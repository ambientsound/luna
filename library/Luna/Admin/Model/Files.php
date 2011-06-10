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
