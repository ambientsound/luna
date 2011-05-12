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

class Luna_Db_Table extends Zend_Db_Table implements Zend_Acl_Resource_Interface
{
	public $db;

	protected $_primary = 'id';

	public function init()
	{
		parent::init();

		$this->db =& Zend_Registry::get('db');

		if (!empty($this->_name) && ($pos = strrpos(get_class($this), 'Model_')) !== false)
		{
			$this->_name = strtolower(substr(get_class($this), $pos + 6));
		}
	}

	/*
	 * Fetch an object and convert it to an array
	 */
	public final function get($id)
	{
		return new Luna_Object($this, $this->_get($id));
	}

	public function _get($id)
	{
		$o = $this->find($id);
		return (empty($o) ? null : current($o->toArray()));
	}

	public function getFull($id)
	{
		return $this->get($id);
	}

	public function getTableName()
	{
		return $this->_name;
	}

	public function getPrimaryKey()
	{
		$this->_setupPrimaryKey();
		return current($this->_primary);
	}

	public final function getAcl($id)
	{
		if (empty($id))
			return null;

		$key = $this->db->quoteIdentifier($this->_name) . '.' . $this->db->quoteIdentifier($this->getPrimaryKey());
		$select = $this->select()
			->setIntegrityCheck(false)
			->from($this->_name, array('createdby'))
			->joinFull('privileges', $this->db->quoteIdentifier('privileges') . '.' . $this->db->quoteIdentifier('resource_id') . ' = ' . $key . ' AND ' .
				$this->db->quoteInto('privileges.resource_type = ?', $this->_name),
				array('user_id', 'role', 'privilege'))
			->where($this->db->quoteInto($key . ' = ?', $id));

		return $this->db->fetchAll($select);
	}

	public function deleteId($id)
	{
		return $this->delete($data, $this->db->quoteIdentifier($this->_primary) . ' = ' . $this->db->quote($id));
	}

	public function updateId($data, $id)
	{
		return $this->update($data, $this->db->quoteIdentifier($this->_primary) . ' = ' . $this->db->quote($id));
	}

	public function count()
	{
		return $this->db->fetchOne($this->select()->from($this->_name, 'COUNT(*)'));
	}

	/*
	 * Inserts or updates an object
	 */
	public function inject($data)
	{
		if (!empty($data[current($this->_primary)]))
		{
			if ($this->updateId($data, $data[current($this->_primary)]) !== FALSE)
			{
				return $data[current($this->_primary)];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $this->insert($data);
		}
	}

	/*
	 * Ensure that created, modified, createdby and modifiedby are always updated on common objects.
	 */
	public function update($data, $where)
	{
		$info = $this->info();

		if (array_search('modified', $info['cols']) !== FALSE && empty($data['modified']))
			$data['modified'] = new Zend_Db_Expr('NOW()');
		if (array_search('modifiedby', $info['cols']) !== FALSE && empty($data['modifiedby']))
			$data['modifiedby'] = Zend_Registry::get('user')->id;

		return parent::update($data, $where);
	}

	public function insert($data)
	{
		$info = $this->info();

		if (array_search('created', $info['cols']) !== FALSE && empty($data['created']))
			$data['created'] = new Zend_Db_Expr('NOW()');
		if (array_search('createdby', $info['cols']) !== FALSE && empty($data['createdby']))
			$data['createdby'] = Zend_Registry::get('user')->id;
		if (array_search('modified', $info['cols']) !== FALSE && empty($data['modified']))
			$data['modified'] = new Zend_Db_Expr('NOW()');
		if (array_search('modifiedby', $info['cols']) !== FALSE && empty($data['modifiedby']))
			$data['modifiedby'] = Zend_Registry::get('user')->id;

		if (empty($data[current($this->_primary)]))
			unset($data[current($this->_primary)]);

		return parent::insert($data);
	}

	public function getResourceId()
	{
		return 'model-' . $this->_name;
	}
}
