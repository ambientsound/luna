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

class Luna_Front_Controller_Action extends Zend_Controller_Action
{
	protected $_layout = 'index';

	protected $_t = null;

	protected $_meta = array();

	protected $path = null;

	protected $options = null;

	public function init()
	{
		/* Master template */
		$this->view->setMaster('layouts/' . $this->_layout);

		/* Translation setup */
		$this->_t = Zend_Registry::get('Zend_Translate');

		/* Option manager */
		$this->options = new Model_Options;

		/* Breadpath/title setup */
		$this->path = new Luna_View_Helper_Title;

		/* Search engine indexing */
		$this->setMeta('robots', 'index, follow');
	}

	public function setMeta($name, $content, $params = null)
	{
		$this->_meta[$name] = $this->_t->_($content, $params);
	}

	public function preDispatch()
	{
		parent::preDispatch();

		$this->view->setTemplate($this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName());
		$this->path->add('/', $this->options->main->title);
	}

	public function postDispatch()
	{
		parent::postDispatch();

		if (!$this->options->main->searchable)
			$this->setMeta('robots', 'noindex, nofollow');

		$this->view->request = $this->getRequest();
		$this->view->params = $this->getRequest()->getParams();
		$this->view->path = $this->path;
		$this->view->meta = $this->_meta;
		$this->view->menu = $this->menu;
		$this->view->options = $this->options;

		$session = new Zend_Session_Namespace('template');
		$this->view->errors = $session->errors;
		$this->view->messages = $session->messages;
		unset($session->errors);
		unset($session->messages);

		if ($this->_ajaxMessage)
		{
			echo $this->view->render('message.tpl');
			$this->_helper->viewRenderer->setNoRender(true);
		}
	}
	public function translate($key, $params = null)
	{
		return $this->_t->_($key, $params);
	}

	protected function gotoPage($page)
	{
		$model = new Model_Pages;
		if (is_numeric($page))
		{
			$this->page = $model->getFromId($page);
			if (empty($this->page))
				throw new Zend_Exception('Page number #' . $page . ' is invalid, it does not exist in the database.', 404);
		}
		else
		{
			$this->page = $model->getFromUrl($page);
			if (empty($this->page))
				throw new Zend_Exception('Path /' . $page . ' does not exist in the database.', 404);

			/* Don't allow viewing this page at it's original path, avoiding duplicate content. */
			if ($this->options->main->frontpage == $this->page->id && !empty($page))
				return $this->_redirect('/');
		}

		if (!$this->page->published)
			throw new Zend_Exception('Page #' . $page->id . ' is not published.', 404);

		if (empty($this->page->nodetype))
			$this->page->nodetype = 'pages';

		$template = Luna_Template::getFrontTemplatePath($this->page->nodetype, $this->page->template);
		if (!file_exists($template))
			throw new Zend_Exception("Page {$this->page['id']} points to template '{$template}' which does not exist on file system.", 503);

		$this->view->setTemplate($this->page->nodetype . '/' . $this->page->template);

		/* Build a breadcrumb path, unless this is the frontpage article. */
		if ($this->options->main->frontpage != $this->page->id)
		{
			$baseurl = null;
			foreach ($this->page->path as $sub)
			{
				$baseurl .= '/' . $sub['slug'];
				$this->path->add($baseurl, $sub['title']);
			}
		}
		elseif ($this->options->main->frontpagetitle)
		{
			$this->path->add(null, $this->page->title);
			$this->view->frontpage = true;
		}

		if (!empty($this->page->metadesc))
			$this->setMeta('description', $this->page['metadesc']);

		$robots = $this->page->spider_index ? 'index' : 'noindex';
		$robots .= ', ' . ($this->page->spider_follow ? 'follow' : 'nofollow');
		$this->setMeta('robots', $robots);

		$this->view->page = $this->page;
	}
}
