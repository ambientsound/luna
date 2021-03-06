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

class Luna_Acl extends Zend_Acl
{
	protected static $_role = null;

	protected static $_roles = null;

	protected static $_table = null;

	public function __construct()
	{
		if (empty(self::$_table) || empty(self::$_roles))
		{
			self::$_table = new Model_Roles;
			self::$_roles = self::$_table->getAllRoles();
		}

		foreach(self::$_roles as $key => $r)
		{
			extract($r);
			$role = new Zend_Acl_Role('group-' . $role);
			if (!empty($inherit))
			{
				$inherit = 'group-' . $inherit;
				if (!$this->hasRole($inherit))
				{
					unset(self::$_roles[$key]);
					self::$_roles[] = $r;
					continue;
				}
			}
			$this->addRole($role, $inherit);
		}

		if ($this->hasRole('group-superuser'))
			$this->allow('group-superuser');
	}

	public function can($resource, $action)
	{
		if (empty(self::$_role))
			return false;

		if (!($resource instanceof Zend_Acl_Resource_Interface))
			$resource = new Zend_Acl_Resource($resource);

		if (!$this->has($resource))
		{
			/* Lazy loading of resource ACL */
			if ($resource instanceof Luna_Object)
				$this->loadObject($resource);
			else
				$this->addResource($resource);
		}

		try
		{
			foreach (self::$_role as $role)
			{
				if ($this->isAllowed($role, $resource, $action))
					return true;
			}
		}
		catch (Zend_Acl_Exception $e)
		{
			throw new Luna_Acl_Exception($e);
		}

		return false;
	}

	public function assert($resource, $action)
	{
		if (!$this->can($resource, $action))
		{
			if ($resource instanceof Zend_Acl_Resource_Interface)
				$resource = $resource->getResourceId();
			throw new Luna_Acl_Exception('Insufficient privileges for ' . $resource . '->' . $action);
		}

		return true;
	}

	public function loadObject(Luna_Object $object)
	{
		$acl = $object->getAcl();
		if (empty($acl))
			return false;

		if (!$this->has($object))
			$this->addResource($object, $object->getModel());

		if (!empty($acl[0]['createdby']) && !$this->hasRole('user-' . $acl[0]['createdby']))
		{
			$role = new Zend_Acl_Role('user-' . $acl[0]['createdby']);
			$this->addRole($role);
			$this->allow($role, $object);
		}

		foreach ($acl as $a)
		{
			$allow = ($a['allow'] == true || $a['allow'] === null);

			if (!empty($a['user_id']))
			{
				$role = new Zend_Acl_Role('user-' . $a['user_id']);
				if (!$this->hasRole($role))
					$this->addRole($role);

				if ($allow)
					$this->allow($role, $object, $a['privilege']);
				else
					$this->deny($role, $object, $a['privilege']);
			}

			if (!empty($a['role']))
			{
				$role = new Zend_Acl_Role('group-' . $a['role']);
				if (!$this->hasRole($role))
					$this->addRole($role);

				if ($allow)
					$this->allow($role, $object, $a['privilege']);
				else
					$this->deny($role, $object, $a['privilege']);
			}
		}
	}

	public function setUser(Luna_User $user)
	{
		self::$_role = $user->getRoles();
		if ($user->isValid() && !$this->hasRole('user-' . $user->id))
			$this->addRole(new Zend_Acl_Role('user-' . $user->id));
	}
}
