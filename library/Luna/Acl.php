<?php

class Luna_Acl extends Zend_Acl
{
	protected $roles = null;

	protected $_table = null;

	public function __construct()
	{
		$this->_table = new Model_Roles;
		$roles = $this->_table->getAllRoles();

		foreach($roles as $role)
		{
			$this->addRole(new Zend_Acl_Role($role['role']), $role['inherits']);
		}

		$map = Luna_Config::get('acl')->toArray();

		foreach ($map as $category => $resources)
		{
			$r = new Zend_Acl_Resource($category);
			$this->addResource($r);

			foreach ($resources as $resource => $role)
			{
				$this->addResource(new Zend_Acl_Resource($resource), $r);
				$this->allow($role, $r);
			}
		}

		$this->allow('superuser');
	}

	public function can($resource, $action)
	{
		if (empty($this->roles))
			return false;

		try
		{
			foreach ($this->roles as $role)
			{
				if ($this->isAllowed($role, $resource))
					return true;
			}
		}
		catch (Zend_Exception $e)
		{
		}

		return false;
	}

	public function setUserId($userId)
	{
		$this->roles = $this->_table->getUserRoles($userId);
	}
}
