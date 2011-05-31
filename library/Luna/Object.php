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

class Luna_Object extends Luna_Stdclass implements Zend_Acl_Resource_Interface
{
	protected $_resId = null;

	protected $_model = null;

	protected $_loaded = false;

	protected $_pk = null;

	protected $_acl = null;

	public function __construct(Luna_Db_Table &$model, $row)
	{
		$this->_model =& $model;
		$this->_pk = $this->_model->getPrimaryKey();
		$this->set($row);
	}

	public function __get($name)
	{
		if ($name == 'id')
			$name = $this->_pk;

		return parent::__get($name);
	}

	/*
	 * Load up-to-date data from database
	 */
	public function load($rowid = null)
	{
		if ($this->_loaded && ($rowid == null || $rowid == $this->_data[$this->_pk]))
			return $this->_data;

		if (empty($rowid))
			$rowid = $this->_data[$this->_pk];

		if (empty($rowid) || is_array($rowid) || is_object($rowid))
			return false;

		$data = $this->_model->_get($rowid);
		if (empty($data))
			return false;

		$this->set($data);

		return $this->_data;
	}

	/*
	 * Reload the active object
	 */
	public function reload()
	{
		$this->_loaded = false;
		return $this->load();
	}

	/*
	 * Load ACL data from DB
	 */
	public function getAcl()
	{
		if (empty($this->_data[$this->_pk]) || !empty($this->_acl))
			return $this->_acl;

		$this->_acl = $this->_model->getAcl($this->_data[$this->_pk]);

		return $this->_acl;
	}

	/*
	 * Reset internal data with data provided
	 */
	public function set($row)
	{
		if (empty($row))
			return false;

		$this->clear();

		if (!empty($row) && !is_array($row))
		{
			$this->_data = array($this->_pk => $row);
			$this->_loaded = false;
		}
		else
		{
			$this->_data = $row;
			$this->_loaded = true;
		}

		if (empty($this->_data[$this->_pk]))
			$this->_resId = $this->_model->getResourceId();
		else
			$this->_resId = $this->_model->getTableName() . '-' . $this->_data[$this->_pk];

		return true;
	}

	/*
	 * Clear internal data
	 */
	public function clear()
	{
		$this->_data = null;
		$this->_resId = null;
		$this->_acl = null;
		$this->_iter = 0;
		$this->_loaded = false;
	}

	public function getModel()
	{
		return $this->_model;
	}

	public function isLoaded()
	{
		return $this->_loaded;
	}

	public function getResourceId()
	{
		return $this->_resId;
	}
}
