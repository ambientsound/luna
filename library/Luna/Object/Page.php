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

class Luna_Object_Page extends Luna_Object_Preorder
{
	protected $_tblname = 'pages';

	protected $_preorderFields = array('id', 'lft', 'rgt', 'slug', 'title');

	public function __get($key)
	{
		if ($key == 'url')
		{
			if (!isset($this->_data['url']))
				$this->_data['url'] = $this->getCanonicalUrl();
			return $this->_data['url'];
		}
		elseif ($key == 'path')
		{
			if (!isset($this->_data['path']))
				$this->_data['path'] = $this->getPath();
			return $this->_data['path'];
		}

		return parent::__get($key);
	}

	public function loadRelation()
	{
		if (!$this->load() || empty($this->nodetype))
			return false;

		$select = $this->_model->select()
			->setIntegrityCheck(false)
			->from($this->nodetype)
			->where($this->_model->db->quoteInto('id = ?', $this->id))
			->limit(1);

		$meta = $this->_model->db->fetchRow($select);
		if (empty($meta))
			return false;

		$this->_data = array_merge($this->_data, $meta);

		return true;
	}

	public function loadPicture()
	{
		if (!$this->load() || empty($this->_data['picture']))
			return false;

		if ($this->_data['picture'] instanceof Luna_Object)
			return true;

		$fmodel = new Model_Files;
		$this->_data['picture'] = $fmodel->get($this->_data['picture']);

		return ($this->_data['picture'] instanceof Luna_Object);
	}

	public function getCanonicalUrl()
	{
		if (!$this->getAncestors())
			return false;

		$base = '';
		foreach ($this->_ancestors as $a)
			$base .= '/' . $a['slug'];

		return $base;
	}

	public function getPath()
	{
		if (!$this->getAncestors())
			return false;

		return $this->_ancestors;
	}

	public function loadChildren()
	{
		if (!empty($this->_data['children']))
			return true;

		$descendants = $this->getDescendants();
		array_shift($descendants);
		if (empty($descendants))
			return false;

		$this->_data['children'] = array();

		foreach ($descendants as $descendant)
		{
			$this->_data['children'][] = new $this($this->_model, $descendant['id']);
		}

		return true;
	}

	public function loadStickers()
	{
		if (!$this->load())
			return false;

		$model = new Luna_Model_Sticker;
		$this->_data['stickers'] = $model->getStickers($this->id);

		return true;
	}
}
