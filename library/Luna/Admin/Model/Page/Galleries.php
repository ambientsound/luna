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

class Luna_Admin_Model_Page_Galleries extends Luna_Model_Gallery
{
	protected $_name = 'galleries';

	protected $_objectName = 'Gallery';

	public function inject($data)
	{
		$values = array('id' => $data['id']);
		$values['folder_id'] = $data['use_folder'] && !empty($data['folder_id']) ? $data['folder_id'] : new Zend_Db_Expr('NULL');
		$pictures = empty($data['pictures']) ? null : explode(',', $data['pictures']);
		unset($data['pictures']);

		if (($id = parent::inject($values)) != false)
		{
			if ($this->setGalleryImages($values['id'], $pictures))
				return $id;
		}

		return false;
	}

	public function setGalleryImages($gallery_id, $pictures)
	{
		$table = new Luna_Db_Table('galleries_files');

		$table->delete($this->db->quoteInto('gallery_id = ?', $gallery_id));

		if (empty($pictures))
			return true;

		$pictures = array_values($pictures);

		foreach ($pictures as $position => $picture)
		{
			if (!$table->insert(array(
				'gallery_id'	=> $gallery_id,
				'file_id'	=> $picture,
				'position'	=> $position
			))) return false;
		}

		return true;
	}

	public function getThumbSizes($gallery_id)
	{
	}
}
