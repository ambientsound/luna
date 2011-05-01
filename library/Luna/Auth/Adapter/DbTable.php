<?php

class Luna_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
	public function authenticate()
	{
		$result = parent::authenticate();
		if (!$result->isValid())
			return $result;

		if ($this->_resultRow['enabled'])
			return $result;

		return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $this->_identity);
	}
}
