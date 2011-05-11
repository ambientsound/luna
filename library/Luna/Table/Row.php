<?php

class Luna_Table_Row extends Luna_Stdclass
{
	protected $_row = null;
	
	protected $_config = null;

	public function __construct($config, $row)
	{
		$this->_config = $config;
		$this->_row = $row;

		foreach ($this->_config['fields'] as $field)
		{
			$this->_data[] = new Luna_Table_Cell($this->_config, $row[$field]);
		}
	}

	public function __get($key)
	{
		if (($pos = array_search($key, $this->_config['fields'])) !== false)
			return $this->_data[$pos];

		return null;
	}

	public function key()
	{
		if (!$this->valid())
			return null;

		return $this->_config['fields'][$this->_iter];
	}
}
