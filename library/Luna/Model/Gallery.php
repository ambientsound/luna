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

class Luna_Model_Gallery extends Luna_Model_Page_Abstract
{
	protected $_objectName = 'Gallery';

	public function getGalleryImages($gallery_id)
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('files')
			->join('galleries_files', 'galleries_files.file_id = files.id', null)
			->where('galleries_files.gallery_id = ' . intval($gallery_id))
			->where('files.size IS NOT NULL')
			->order('galleries_files.position ASC');

		return new Zend_Paginator(new Luna_Paginator_Adapter_Images($select));
	}

	public function getFolderImages($folder_id)
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('files')
			->where('size IS NOT NULL')
			->order('id DESC');

		if (empty($folder_id))
		{
			$select->where('folder_id IS NULL');
		}
		elseif (is_array($folder_id))
		{
			foreach ($folder_id as &$id)
				$id = intval($id);
			$select->where('folder_id IN (' . join(',', $folder_id) . ')');
		}
		else
		{
			$select->where('folder_id = ' . intval($folder_id));
		}

		return new Zend_Paginator(new Luna_Paginator_Adapter_Images($select));
	}
}
