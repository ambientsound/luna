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
			'user'	=> $userid,
			'role'	=> $role
		));
	}

	public function inject($data)
	{
		if (empty($data['password']))
		{
			unset($data['password']);
		}
		else
		{
			$hash = new Luna_Phpass(null, true);
			$data['password'] = $hash->HashPassword($data['password']);
		}

		return parent::inject($data);
	}
}
