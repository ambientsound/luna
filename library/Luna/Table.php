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

	public function __construct($config, Zend_Db_Table $source, Zend_Controller_Request_Abstract $request)
	{
		if ($config instanceof Zend_Config)
			$this->_config = $config->toArray();
		elseif (is_array($config))
			$this->_config = $config;
		else
			$this->_config = Luna_Config::get('table')->$config->toArray();

		if (empty($this->_config))
			throw new Zend_Exception('Luna_Table needs a table configuration index, configuration array or Zend_Config object');

		$this->_config = array_merge(Luna_Config::get('table')->_defaultparams->toArray(), $this->_config);
		$this->_model = $source;
		$this->_info = $this->_model->info();

		/* Determine source */
		$fn = strtoupper($this->_info['name'][0]) . strtolower(substr($this->_info['name'], 1));
		if (method_exists($this->_model, $fn))
			$this->_select = $this->_model->$fn;
		else
			$this->_select = $this->_model->select();

		/* Set some reasonable config defaults */
		if (!empty($this->_config['fields']))
			$this->_config['fields'] = explode(' ', $this->_config['fields']);
		else
			$this->_config['fields'] = $this->_info['cols'];

		if (empty($this->_config['prefix']))
			$this->_config['prefix'] = $this->_info['name'] . '_t_';

		/* Sanitize user input into reasonable pagination defaults */
		$sort = strtolower($request->getParam('sort', current($this->_info['primary'])));
		if (array_search($sort, $this->_info['cols']) === false)
			$sort = current($this->_info['primary']);
		$order = strtoupper($request->getParam('order', 'ASC'));
		if ($order != 'ASC' && $order != 'DESC')
			$order = 'ASC';
		$limit = intval($request->getParam('limit', $this->_config['limit']));
		if ($limit < 1)
			$limit = $this->_config['limit'];
		elseif ($limit > $this->_config['maxlimit'])
			$limit = $this->_config['maxlimit'];
		$page = intval($request->getParam('page', $this->_config['page']));
		if ($page < 1)
			$page = 1;

		/* Ordering */
		$this->_select->order("{$sort} {$order}");

		/* Create our paginator object */
		$this->_paginator = Zend_Paginator::factory($this->_select);

		/* Pagination */
		$this->_paginator->setCurrentPageNumber($page);
		$this->_paginator->setItemCountPerPage($limit);
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
