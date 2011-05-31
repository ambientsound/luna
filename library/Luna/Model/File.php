<?php
/*
 * LUNA content management system
 * Copyright (c) 2011, Kim Tore Jensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the author nor the names of its contributors may be
 * used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class Luna_Model_File extends Luna_Db_Table
{
	protected $_name = 'files';

	/*
	 * Get full info about an image, including www path
	 */
	public function _get($id)
	{
		$image = parent::_get($id);
		if (empty($image))
			return null;

		$config = Luna_Config::get('site')->media;
			
		$image['pub'] = $config->path . '/' . $image['filename'];
		$image['path'] = realpath(PUBLIC_PATH . $config->path) . '/' . $image['filename'];
		$tsizes = $this->getThumbSizes();

		foreach ($tsizes as $tdest => $size)
		{
			$filename = realpath(PUBLIC_PATH . $config->path) . '/' . $tdest . '/' . $image['filename'];
			if (!file_exists($filename))
				continue;

			$image['thumbnail'][$tdest] = array(
				'pub'	=> $config->path . '/' . $tdest . '/' . $image['filename'],
				'path'	=> realpath(PUBLIC_PATH . $config->path) . '/' . $tdest . '/' . $image['filename'],
				'dir'	=> $tdest,
				'size'	=> $size,
			);
		}

		return $image;
	}

	/*
	 * Retrieve all possible image thumbnail sizes
	 */
	public function getThumbSizes()
	{
		$config = Luna_Config::get('site')->media;
		$dirs = glob(PUBLIC_PATH . $config->path. '/*', GLOB_ONLYDIR);
		$ret = array();

		foreach ($config->thumbnail as $dir => $size)
			$ret[$dir] = $size;

		foreach ($dirs as $dir)
		{
			$size = basename($dir);
			if (isset($ret[$size]))
				continue;

			$ret[$size] = $size;
		}

		return $ret;
	}

	/*
	 * Allocate free filename
	 */
	public function allocate(&$element, $old)
	{
		$config = Luna_Config::get('site')->media;
		$info = current($element->getFileInfo());
		$orig = $element->getFileName();
		$name = preg_replace('/[^\w\d-_\/\. ]/', '', $info['name']);
		$name = preg_replace('/\s+/', '-', $name);
		$dest = realpath(PUBLIC_PATH . $config->path) . '/';

		if (empty($dest))
			throw new Zend_Exception('Upload target path "' . $dest . '" does not exist.');

		$counter = 0;
		$base = substr($name, 0, strrpos($name, '.'));
		$ext = substr($name, strrpos($name, '.'));

		while (file_exists($dest . $name))
		{
			if (!empty($old) && $name == $old['filename'])
				break;

			$name = $base . ++$counter . $ext;
		}

		$dest .= $name;

		if (rename($orig, $dest))
		{
			$size = getimagesize($dest);
			return array(
				'path'	=> $dest,
				'name'	=> $name,
				'size'	=> $size[0] . 'x' . $size[1]
			);
		}
		else
		{
			return false;
		}
	}

	public function upload(&$element, $old = null)
	{
		if (!$element->isUploaded())
			return false;

		if (!$element->receive())
			return false;

		if (!empty($old))
			$old = parent::_get($old);

		if (($file = $this->allocate($element, $old)) === false)
			return false;

		$values = array(
			'id'		=> empty($old) ? null : $old['id'],
			'filename'	=> $file['name'],
			'size'		=> $file['size']
		);

		$values['id'] = $this->inject($values);

		if (empty($values['id']))
		{
			unlink($file['path']);
			return false;
		}

		/* Old file dereferenced, but needs to be deleted from disk. */
		if (!empty($old) && $old['filename'] != $values['filename'])
		{
			$this->unlink($old['filename']);
		}

		$this->createThumbs($values['filename']);

		return $values['id'];
	}

	public function inject($values)
	{
		if (empty($values['title']))
		{
			if (!empty($values['id']))
				unset($values['title']);
			elseif (empty($values['filename']))
				return false;
			elseif (empty($values['id']))
				$values['title'] = $values['filename'];
		}

		if (isset($values['folder_id']) && empty($values['folder_id']))
			$values['folder_id'] = new Zend_Db_Expr('NULL');

		return parent::inject($values);
	}

	/*
	 * Re-create all thumbnails for the given picture.
	 */
	public function createThumbs($filename, array $sizes = array())
	{
		$config = Luna_Config::get('site')->media;
		$basedir = realpath(PUBLIC_PATH . $config->path) . '/';
		$orig = $basedir . $filename;

		if (!file_exists($orig))
			return false;

		$type = null;
		$gd = $this->openGD($orig, $type);
		if (empty($gd))
			return false;

		$width = imagesx($gd);
		$height = imagesy($gd);

		if (empty($sizes))
			$sizes = $config->thumbnail->toArray();

		foreach ($sizes as $tdest => $size)
		{
			$t = $basedir . $tdest . '/';
			if (is_dir($t) || mkdir($t, 0755))
			{
				list($wmax, $hmax) = explode('x', $size);

				$waspect = $width / $wmax;
				$haspect = $height / $hmax;
				$factor = $waspect > $haspect ? $waspect : $haspect;

				$w = $width / $factor;
				$h = $height / $factor;

				$th = imagecreatetruecolor($w, $h);

				imagealphablending($th, false);
				imagesavealpha($th, true);
				imagecopyresampled($th, $gd, 0, 0, 0, 0, $w, $h, $width, $height);

				$this->saveGD($th, $t . $filename, $type);

				imagedestroy($th);
			}
		}

		imagedestroy($gd);

		return true;
	}

	public function saveGD($resource, $filename, $type)
	{
		switch($type)
		{
			case IMAGETYPE_GIF:
				return imagegif($resource, $filename);
			case IMAGETYPE_JPEG:
				return imagejpeg($resource, $filename);
			case IMAGETYPE_PNG:
			case IMAGETYPE_BMP:
			default:
				return imagepng($resource, $filename);
		}

		return false;
	}

	public function openGD($f, &$type)
	{
		$info = getimagesize($f);
		$type = $info[2];

		switch($type)
		{
			case IMAGETYPE_GIF:
				return imagecreatefromgif($f);
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($f);
			case IMAGETYPE_PNG:
				return imagecreatefrompng($f);
			case IMAGETYPE_BMP:
				return $this->imagecreatefrombmp($f);
			case IMAGETYPE_WBMP:
				return imagecreatefromwbmp($f);
			case IMAGETYPE_XBM:
				return imagecreatefromxbm($f);
			case IMAGETYPE_SWF:
			case IMAGETYPE_PSD:
			case IMAGETYPE_TIFF_II:
			case IMAGETYPE_TIFF_MM:
			case IMAGETYPE_JPC:
			case IMAGETYPE_JP2:
			case IMAGETYPE_JPX:
			case IMAGETYPE_JB2:
			case IMAGETYPE_SWC:
			case IMAGETYPE_IFF:
			case IMAGETYPE_ICO:
			default:
				return false;
		}

		return false;
	}

	public function deleteId($id)
	{
		$o = parent::_get($id);
		if (!empty($o))
			$this->unlink($o['filename']);

		return parent::deleteId($id);
	}

	/*
	 * Delete thumbnails and original
	 */
	public function unlink($filename)
	{
		$config = Luna_Config::get('site')->media;
		$basedir = realpath(PUBLIC_PATH . $config->path) . '/';
		$tdirs = $config->thumbnail->toArray();

		file_exists($basedir . $filename) && unlink($basedir . $filename);

		foreach ($tdirs as $dir => $size)
		{
			$file = $basedir . $dir . '/' . $filename;
			file_exists($file) && unlink($file);
		}
	}



	/*********************************************/
	/* Fonction: ImageCreateFromBMP              */
	/* Author:   DHKold                          */
	/* Contact:  admin@dhkold.com                */
	/* Date:     The 15th of June 2005           */
	/* Version:  2.0B                            */
	/*********************************************/

	function imagecreatefrombmp($filename)
	{
		//Ouverture du fichier en mode binaire
		if (! $f1 = fopen($filename,"rb")) return FALSE;

		//1 : Chargement des ent�tes FICHIER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;

		//2 : Chargement des ent�tes BMP
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
				'/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
				'/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		//3 : Chargement des couleurs de la palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216 && $BMP['colors'] != 65536)
		{
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}

		//4 : Cr�ation de l'image
		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);

		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0)
		{
			$X=0;
			while ($X < $BMP['width'])
			{
				if ($BMP['bits_per_pixel'] == 24)
					$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16)
				{ 
					$COLOR = unpack("v",substr($IMG,$P,2));
					$blue  = (($COLOR[1] & 0x001f) << 3) + 7;
					$green = (($COLOR[1] & 0x03e0) >> 2) + 7;
					$red   = (($COLOR[1] & 0xfc00) >> 7) + 7;
					$COLOR[1] = $red * 65536 + $green * 256 + $blue;
				}
				elseif ($BMP['bits_per_pixel'] == 16)
				{ 
					$COLOR = unpack("n",substr($IMG,$P,2));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 8)
				{ 
					$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
					return FALSE;
				imagesetpixel($res,$X,$Y,$COLOR[1]);
				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		}

		//Fermeture du fichier
		fclose($f1);

		return $res;
	}
}
