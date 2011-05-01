<?php

class Luna_Acl extends Zend_Acl
{
	private $_config = null;

	protected $_role = null;

	public function __construct($r = null)
	{
		$this->_role = $r;
		$this->_config = Luna_Config::get('acl');

		foreach($this->_config->roles as $role => $extends)
		{
			$this->addRole($role, empty($extends) ? null : $extends);
		}

		foreach ($this->_config->toArray() as $ctrl => $resources)
		{
			if ($ctrl == 'roles')
				continue;

			$this->addResource($ctrl);
			foreach ($resources as $resource => $role)
			{
				$this->addResource($ctrl . '.' . $resource, $ctrl);
				$this->allow($role, $ctrl . '.' . $resource);
			}
		}
	}

	public function can($resource)
	{
		try
		{
			return $this->isAllowed($this->_role, $resource);
		}
		catch (Zend_Exception $e)
		{
			return false;
		}
	}
}
