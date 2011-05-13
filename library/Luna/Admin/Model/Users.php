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

class Luna_Admin_Model_Users extends Luna_Db_Table
{
	public function _get($id)
	{
		$select = $this->select()
			->from($this->_name)
			->setIntegrityCheck(false)
			->joinLeft('users_roles', 'users_roles.user_id = users.id', 'role')
			->where($this->db->quoteInto('users.id = ?', $id));

		$user = $this->db->fetchAll($select);
		if (empty($user))
			return $user;

		$u = $user[0];
		unset($u['role']);
		foreach ($user as $us)
		{
			$u['roles'][] = $us['role'];
		}

		return $u;
	}

	public function getByUsername($username)
	{
		$select = $this->select()
			->from($this->_name)
			->where($this->db->quoteInto('username = ?', $username))
			->limit(1);

		return $this->db->fetchRow($select);
	}

	public function getByEmail($mail)
	{
		$select = $this->select()
			->from($this->_name)
			->where($this->db->quoteInto('email = ?', $mail))
			->limit(1);

		return $this->db->fetchRow($select);
	}

	public function getRoles($userId)
	{
		if (empty($userId))
			return null;

		$select = $this->select()
			->setIntegrityCheck(false)
			->from('users_roles', 'role')
			->where($this->db->quoteIdentifier('user_id') . ' = ' . $this->db->quote($userId));

		return $this->db->fetchCol($select);
	}

	public function getRoleList()
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('roles', 'role');

		return $this->db->fetchCol($select);
	}

	/*
	 * Checks if a username/password combination is valid and the user can log in.
	 * Returns the user row on success, false on failure.
	 */
	public function checkAuth($username, $password)
	{
		$select = $this->select()
			->from($this->_name)
			->where($this->db->quoteInto('username = ?', $username))
			->where('enabled = true')
			->limit(1);

		$user = $this->db->fetchRow($select);
		if (empty($user))
			return false;

		$hash = new Luna_Phpass(null, true);
		if (!$hash->CheckPassword($password, $user['password']))
			return false;

		unset($user['password']);

		return $user;
	}

	/*
	 * Adds a role to a user.
	 */
	public function addUserRole($userid, $role)
	{
		$table = new Luna_Db_Table('users_roles');
		return $table->insert(array(
			'user_id'	=> $userid,
			'role'		=> $role
		));
	}

	/*
	 * Adds a role to a user.
	 */
	public function setUserRoles($userid, $roles)
	{
		$table = new Luna_Db_Table('users_roles');

		if (empty($roles))
		{
			$table->delete($this->db->quoteInto('user_id = ?', $userid));
			return true;
		}

		$existingroles = $this->db->fetchCol($table->select()->from('users_roles', 'role')->where($this->db->quoteInto('user_id = ?', $userid)));

		$deleteroles = $roles;
		foreach ($deleteroles as &$role)
			$role = $this->db->quote($role);
		$table->delete($this->db->quoteInto('user_id = ? AND role NOT IN ', $userid) . '(' . join(',', $deleteroles) . ')');

		$newroles = array();
		foreach ($roles as $role)
			if (array_search($role, $existingroles) === false)
				$newroles[] = $role;

		if (empty($newroles))
			return true;

		foreach ($newroles as $role)
		{
			if (!$table->insert(array(
				'user_id'	=> $userid,
				'role'		=> $role
			))) return false;
		}

		return true;
	}

	public function inject($data)
	{
		$this->db->beginTransaction();

		if (empty($data['password']))
		{
			unset($data['password']);
		}
		else
		{
			$hash = new Luna_Phpass(null, true);
			$data['password'] = $hash->HashPassword($data['password']);
		}

		$roles = $data['roles'];
		unset($data['roles']);

		$id = parent::inject($data);
		if ($id != false && $this->setUserRoles($id, $roles) && $this->db->commit())
			return $id;

		$this->db->rollBack();
		return false;
	}
}
