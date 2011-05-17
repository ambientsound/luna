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

class Luna_Table_Row extends Luna_Stdclass
{
	protected $_row = null;
	
	protected $_config = null;

	public function __construct($config, $row)
	{
		$this->_config = $config;
		$this->_row = $row;

		foreach ($this->_config['fields'] as $field)
		{
			$celltype = null;
			if (!empty($this->_config['f'][$field]['type']))
			{
				switch($this->_config['f'][$field]['type'])
				{
					case 'slug':
						$celltype = 'Slug';
						break;
					case 'actions':
						$celltype = 'Actions';
						break;
					case 'timestamp':
						$celltype = 'Timestamp';
						break;
					default:
				}
			}
			$celltype = 'Luna_Table_Cell' . (empty($celltype) ? $celltype : '_' . $celltype);
			$this->_data[] = new $celltype($this->_config, $row, $field);
		}
	}

	public function __get($key)
	{
		if (($pos = array_search($key, $this->_config['fields'])) !== false)
			return $this->_data[$pos];

		return null;
	}

	public function key()
	{
		if (!$this->valid())
			return null;

		return $this->_config['fields'][$this->_iter];
	}
}
