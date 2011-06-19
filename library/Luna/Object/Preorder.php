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

class Luna_Object_Preorder extends Luna_Object
{
	protected $_parentId = null;

	protected $_ancestors = null;

	protected $_descendants = null;

	protected $_preorderFields = array('id', 'lft', 'rgt');

	public function clear()
	{
		parent::clear();
		$this->_parentId = null;
		$this->_ancestors = null;
		$this->_descendants = null;
	}

	public function isLeaf()
	{
		if (!$this->load())
			return true;

		return ($this->lft + 1 == $this->rgt);
	}

	public function getAncestors()
	{
		if (!$this->load())
			return false;

		if (empty($this->_ancestors))
		{
			$select = $this->_model->select()
				->setIntegrityCheck(false)
				->from($this->_tblname, $this->_preorderFields)
				->where($this->_model->db->quoteInto('lft <= ?', $this->lft))
				->where($this->_model->db->quoteInto('rgt >= ?', $this->rgt))
				->order('lft ASC');

			$this->_ancestors = $this->_model->db->fetchAll($select);
		}

		return $this->_ancestors;
	}

	public function getDescendants()
	{
		if (!$this->load())
			return false;

		if (empty($this->_descendants))
		{
			$select = $this->_model->select()
				->setIntegrityCheck(false)
				->from($this->_tblname, $this->_preorderFields)
				->where($this->_model->db->quoteInto('lft >= ?', $this->lft))
				->where($this->_model->db->quoteInto('rgt <= ?', $this->rgt))
				->order('lft ASC');

			$this->_descendants = $this->_model->db->fetchAll($select);
		}

		return $this->_descendants;
	}

	public function getParentId()
	{
		if (!$this->load())
			return null;

		if (empty($this->_parentId))
		{
			$select = $this->_model->select()
				->setIntegrityCheck(false)
				->from($this->_tblname, 'id')
				->where($this->_model->db->quoteInto('lft < ?', $this->lft))
				->where($this->_model->db->quoteInto('rgt > ?', $this->rgt))
				->order('lft DESC')
				->limit(1);

			$this->_parentId = $this->_model->db->fetchOne($select);
		}

		return $this->_parentId;
	}
}
