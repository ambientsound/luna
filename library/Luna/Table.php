<?php

/*
 * Standardized data representation table for use in templates.
 */
class Luna_Table implements Iterator
{
	protected $_paginator = null;

	protected $_config = array();

	protected $_model = null;

	protected $_info = null;

	protected $_headers = array();

	protected $_rowCache = null;

	private $_iter = 0;

	public function __construct($config, $source = null)
	{
		if ($config instanceof Zend_Config)
			$this->_config = $config->toArray();
		elseif (is_array($config))
			$this->_config = $config;
		else
			$this->_config = Luna_Config::get('table')->$config->toArray();

		if (empty($this->_config))
			throw new Zend_Exception('Luna_Table needs a table configuration index, configuration array or Zend_Config object');

		$this->_config['params'] = array_merge(Luna_Config::get('table')->_defaultparams->toArray(), $this->_config['params']);

		if (!empty($this->_config['params']['fields']))
			$this->_config['params']['fields'] = explode(' ', $this->_config['params']['fields']);

		if ($source instanceof Zend_Db_Table)
		{
			$this->_model = $source;
			$this->_info = $this->_model->info();
			$fn = strtoupper($this->_info['name'][0]) . strtolower(substr($this->_info['name'], 1));

			if (method_exists($this->_model, $fn))
				$this->_select = $this->_model->$fn;
			else
				$this->_select = $this->_model->select();

			$this->_paginator = Zend_Paginator::factory($this->_select);

			if (empty($this->_config['params']['fields']))
				$this->_config['params']['fields'] = $this->_info['cols'];
		}
		else
		{
			throw new Zend_Exception('Luna_Table must be constructed with a Zend_Db_Table.');
		}

		$this->_paginator->setCurrentPageNumber($this->_config['page']);
		$this->_paginator->setItemCountPerPage($this->_config['limit']);
	}

	public function __toString()
	{
		$view = Luna_View_Smarty::factory();
		$view->assign('table', $this);
		$view->assign('config', $this->_config);
		return $view->render('table.tpl');
	}

	public function getPaginator()
	{
		return $this->_paginator;
	}

	public function count()
	{
		return $this->_paginator->getTotalItemCount();
	}

	private function cacheRows()
	{
		$this->_rowCache = array();
		$items = $this->_paginator->getCurrentItems();
		foreach ($items as $i)
		{
			$this->_rowCache[] = new Luna_Table_Row($this->_config, $i->toArray());
		}
	}

	/*
	 * Iterator functions
	 */
	public function rewind()
	{
		$this->_iter = 0;
	}

	public function current()
	{
		if (empty($this->_rowCache))
			$this->cacheRows();

		return $this->_rowCache[$this->_iter];
	}

	public function key()
	{
		if (empty($this->_rowCache))
			$this->cacheRows();

		$row =& $this->_rowCache[$this->_iter];
		return (string)$row->id;
	}

	public function next()
	{
		++$this->_iter;
	}

	public function valid()
	{
		return ($this->_iter >= 0 && $this->_iter < $this->_paginator->getCurrentItemCount());
	}
}
