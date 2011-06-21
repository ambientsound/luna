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

class Luna_Table implements Iterator
{
	protected $_paginator = null;

	protected $_config = array();

	protected $_model = null;

	protected $_info = null;

	protected $_request = null;

	protected $_rowCache = null;

	private $_iter = 0;

	public function __construct($config, Zend_Db_Table $source, Zend_Controller_Request_Abstract $request)
	{
		if ($config instanceof Zend_Config)
			$this->_config = $config->toArray();
		elseif (is_array($config))
			$this->_config = $config;
		else
		{
			$this->_config = Luna_Config::get('table')->$config;
			if (empty($this->_config))
				throw new Zend_Exception('Table "' . $config . '" does not have an entry in table.ini.');

			$this->_config = $this->_config->toArray();
		}

		if (empty($this->_config))
			throw new Zend_Exception('Luna_Table needs a table configuration index, configuration array or Zend_Config object');

		$this->_config = array_merge(Luna_Config::get('table')->_defaultparams->toArray(), $this->_config);
		$this->_model = $source;
		$this->_info = $this->_model->info();
		$this->_config['primary'] = current($this->_info['primary']);

		/* Determine source */
		$fn = 'select' . strtoupper($this->_info['name'][0]) . strtolower(substr($this->_info['name'], 1));
		if (method_exists($this->_model, $fn))
			$this->_select = $this->_model->$fn();
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
		$sort = strtolower($request->getParam('sort', $this->_config['sort']));
		if (array_search($sort, $this->_info['cols']) === false)
			$sort = $this->_config['primary'];
		$order = strtoupper($request->getParam('order', $this->_config['order']));
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

		$this->_request = $request;

		/* Ordering */
		$this->_select->order("{$sort} {$order}");

		/* Create our paginator object */
		$this->_paginator = Zend_Paginator::factory($this->_select);

		/* Pagination */
		$this->_paginator->setCurrentPageNumber($page);
		$this->_paginator->setItemCountPerPage($limit);
		$this->_paginator->setView(Luna_View_Smarty::factory());
	}

	public function __toString()
	{
		try
		{
			$view = Luna_View_Smarty::factory();
			$view->assign('table', $this);
			$view->assign('config', $this->_config);
			$view->assign('request', $this->_request);
			$view->assign('params', $this->_request->getParams());
			return $view->render('table.tpl');
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
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
