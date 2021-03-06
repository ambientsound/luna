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

class Luna_User extends Luna_Stdclass implements Zend_Acl_Role_Interface
{
	protected $_role;

	protected $_data = null;

	protected $_model = null;

	public function __construct($handle)
	{
		if (empty($handle))
			return;

		$this->_model = new Model_Users();

		if (is_numeric($handle))
			$this->_data = $this->_model->get($handle);
		elseif (is_array($handle))
			$this->_data = $handle;
		else
			$this->_data = $this->_model->getByUsername($handle);

		unset($this->_data['password']);
	}

	public function registerActivity()
	{
		if (!$this->isValid())
			return false;

		$cfg = Luna_Config::get('site');
		$model = new Zend_Db_Table('users');

		$updates = array(
			'activity' => new Zend_Db_Expr('NOW()')
		);
		return $model->update($updates, Zend_Registry::get('db')->quoteInto('id = ?', $this->_data['id']));
	}

	public function isValid()
	{
		return (!empty($this->_data));
	}

	public function getRoleId()
	{
		return 'user-' . $this->_data['id'];
	}

	public function getRoles()
	{
		if (empty($this->_data))
			return array('group-guest');

		$roles = array($this->getRoleId());

		if (!isset($this->_data['roles']))
		{
			$this->_data['roles'] = $this->_model->getRoles($this->_data['id']);
			if (!empty($this->_data['roles']))
			{
				foreach ($this->_data['roles'] as &$role)
					$roles[] = 'group-' . $role;
			}
		}

		return $roles;
	}

	public function toArray()
	{
		return $this->_data;
	}
}
