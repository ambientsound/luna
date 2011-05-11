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

class Luna_Admin_Controller_Action extends Zend_Controller_Action implements Zend_Acl_Resource_Interface
{
	protected $_layout = 'index';

	protected $_t = null;

	protected $user = null;

	protected $acl = null;

	protected $_menu = null;

	protected $path = null;

	protected $_ajaxMessage = false;

	public function init()
	{
		/* Master template setup */
		if ($this->getRequest()->isXmlHttpRequest())
		{
			$this->view->setMaster(null);
			$this->view->ajax = true;
		}
		else
		{
			$this->view->setMaster('layouts/' . $this->_layout);
		}

		/* Translation setup */
		$this->_t = Zend_Registry::get('Zend_Translate');

		/* Current user setup */
		$this->user = new Luna_User(Zend_Auth::getInstance()->getStorage()->read());
		$this->user->registerActivity();
		Zend_Registry::set('user', $this->user);
		$this->view->user = $this->user;

		/* Model setup */
		if (!empty($this->_modelName))
			$this->model = new $this->_modelName;

		/* ACL setup */
		$this->acl = new Luna_Acl_Module('acl');
		$this->acl->setUser($this->user);
		Zend_Registry::set('acl', $this->acl);

		/* Menu */
		$this->_menu = new Luna_Admin_Menu;
		$this->setupMenu();

		/* Breadpath/title setup */
		$this->path = new Luna_View_Helper_Title;
	}

	public function preDispatch()
	{
		parent::preDispatch();

		$this->view->setTemplate($this->_getParam('controller') . '/' . $this->_getParam('action'));
		$this->path->init($this->getRequest());
		/* User check. Skip if we are going to the error or auth controller. */
		$ct = $this->getRequest()->getControllerName();
		if ($ct == 'error' || $ct == 'auth')
			return true;

		if (!$this->user->isValid())
		{
			$path = trim($_SERVER['REQUEST_URI'], '/');
			$this->_redirect('/auth/login' . (empty($path) ? null : '?path=' . urlencode($path)));
			return false;
		}

		try
		{
			if (!$this->acl->can($this, $this->getRequest()->getActionName()))
			{
				$front = Zend_Controller_Front::getInstance();
				if ($this->getRequest()->getControllerName() == $front->getDefaultControllerName() &&
					$this->getRequest()->getActionName() == $front->getDefaultAction())
				{
					$front->setBaseUrl('/');
					return $this->_redirect('/');
				}

				$this->addError('insufficient_privileges');
				$this->_forward('index', 'index');

				return false;
			}
		}
		catch (Luna_Acl_Exception $e)
		{
			trigger_error($this->getResourceId() . '->' . $this->getRequest()->getActionName() . ' is not governed by ACL, allowing access by default.', E_USER_WARNING);
		}
	}

	public function postDispatch()
	{
		parent::postDispatch();

		$this->view->menu = $this->_menu->getMenu();
		$this->view->request = $this->getRequest();
		$this->view->params = $this->getRequest()->getParams();
		$this->view->path = $this->path;

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

	public function exitAjax()
	{
		if ($this->getRequest()->isXmlHttpRequest())
			$this->_ajaxMessage = true;
	}

	protected function addMenu($action, $params = null, $title = null, $uri = null)
	{
		$this->_menu->add($this->_getParam('controller'), $action, $params, $title, $uri);
	}

	protected function setupMenu()
	{
	}

	public function translate($key, $params = null)
	{
		return $this->_t->_($key, $params);
	}


	public function addMessage($message, $params = null)
	{
		$session = new Zend_Session_Namespace('template');
		$session->messages[] = $this->_t->_('msg_' . $message, $params);
	}

	public function addError($error, $params = null)
	{
		$session = new Zend_Session_Namespace('template');
		$session->errors[] = $this->_t->_('error_' . $error, $params);
	}

	protected function _redirect($url, array $options = array())
	{
		if ($this->getRequest()->isXmlHttpRequest())
			return false;

		return parent::_redirect($url, $options);
	}

	public function getResourceId()
	{
		return 'controller-' . $this->getRequest()->getControllerName();
	}
}
