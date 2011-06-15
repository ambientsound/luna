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

class Luna_Admin_Model_Files extends Luna_Model_File
{
	private $_folderFilter = null;

	public function selectFiles()
	{
		$select = $this->select();
		if (empty($this->_folderFilter))
			return $select;

		foreach ($this->_folderFilter as &$f)
			$f = $this->db->quote($f);

		return $select->where('folder_id IN (' . join(',', $this->_folderFilter) . ')');
	}

	public function selectImages()
	{
		return $this->selectFiles()->where('size IS NOT NULL');
	}

	public function getIdByFilename($filename)
	{
		$select = $this->select()
			->from($this->_name, 'id')
			->where($this->db->quoteInto('filename = ?', $filename))
			->limit(1);

		return $this->db->fetchOne($select);
	}

	public function createThumbnailSize($size)
	{
		$table = new Model_Thumbnails;
		if (!$table->inject($size))
			return false;

		$filenames = $this->db->fetchCol($this->select()
			->from($this->_name, 'filename')
			->where('size IS NOT NULL'));

		if (empty($filenames))
			return true;

		if (empty($size['slug']))
			$size = array($size['size'] => $size['size']);
		else
			$size = array($size['slug'] => $size['size']);

		set_time_limit(0);

		foreach ($filenames as $file)
			$this->createThumbs($file, $size);

		return true;
	}

	public function deleteThumbnailSize($size)
	{
		$table = $this->getThumbnailTable();
		if (empty($table) || empty($table[$size]) || !empty($table[$size]['permanent']))
			return false;

		$model = new Model_Thumbnails;
		if (!$model->deleteId($size))
			return false;

		$config = Luna_Config::get('site')->media;
		$basedir = realpath(PUBLIC_PATH . $config->path) . '/';
		if (empty($table[$size]['slug']))
			$basedir .= $size;
		else
			$basedir .= $table[$size]['slug'];

		$this->delTree($basedir);

		return true;
	}

	public function delTree($path)
	{
		if (!is_dir($path))
		{
			chmod($path, 0666);
			return unlink($path);
		}
		else
		{
			$files = glob($path . '/*');
			foreach ($files as $f)
				$this->delTree($f);
			chmod($path, 0777);
			return rmdir($path);
		}
	}

	public function getThumbnailTable()
	{
		$config = Luna_Config::get('site')->media;
		$ret = array();

		foreach ($config->thumbnail as $dir => $size)
		{
			$ret[$size] = array(
				'size'		=> $size,
				'slug'		=> $dir,
				'permanent'	=> true,
				'description'	=> null
			);
		}

		$dbdirs = $this->db->fetchAssoc($this->select()
			->setIntegrityCheck(false)
			->from('thumbnails', array('size', 'slug', 'description')));

		if (empty($dbdirs))
			return $ret;

		$ret = array_merge($dbdirs, $ret);
		uksort($ret, 'strnatcmp');

		return $ret;

	}

	public function getMostRecentPictureId()
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from($this->_name, 'MAX(id)')
			->where('size IS NOT NULL');

		return $this->db->fetchOne($select);
	}

	public function setFolderFilter($folder_id, $recurse = false)
	{
		if (!$recurse)
		{
			$this->_folderFilter = array(intval($folder_id));
		}
		else
		{
			$this->_folderFilter = null;
			$folder = new Luna_Object_Folder(new Model_Folders, $folder_id);
			$children = $folder->getDescendants();
			if (empty($children))
				return;

			foreach ($children as $child)
				$this->_folderFilter[] = intval($child['id']);
		}
	}
}
