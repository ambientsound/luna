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

class Luna_Admin_Controller_Action extends Zend_Controller_Action
{
	protected $_layout = 'index';

	protected $_t = null;

	protected $user = null;

	protected $acl = null;

	protected $_menu = null;

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

		/* Access control lists */
		$this->acl = new Luna_Acl($this->user->role);
		Zend_Registry::set('acl', $this->acl);
		$this->view->acl = $this->acl;

		/* Model setup */
		if (!empty($this->_modelName))
		{
			$this->_model = new $this->_modelName;
		}

		/* Menu */
		$this->_menu = new Luna_Menu;
		$this->setupMenu();
	}

	public function preDispatch()
	{
		parent::preDispatch();

		$this->view->setTemplate($this->_getParam('controller') . '/' . $this->_getParam('action'));

		if (!$this->user->isValid())
		{
			$path = trim($_SERVER['REQUEST_URI'], '/');
			$this->_redirect('/auth/login' . (empty($path) ? null : '?path=' . urlencode($path)));
			return false;
		}

		if (!$this->acl->can($this->_getParam('controller') . '.' . $this->_getParam('action')))
		{
			$this->addError('privilege_shortage');
			$this->_redirect($_SERVER['HTTP_REFERER']);
			return false;
		}
	}

	public function postDispatch()
	{
		parent::postDispatch();

		$this->view->menu = $this->_menu->getMenu();

		if ($this->_ajaxMessage)
		{
			$session = new Zend_Session_Namespace('template');
			$this->view->errors = $session->errors;
			$this->view->messages = $session->messages;
			unset($session->errors);
			unset($session->messages);
			echo $this->view->render('message.tpl');
			$this->_helper->viewRenderer->setNoRender(true);
		}
	}

	public function exitAjax()
	{
		if ($this->getRequest()->isXmlHttpRequest())
			$this->_ajaxMessage = true;
	}

	public function hasPrivilege($priv)
	{
		return $this->acl->can($this->_getParam('controller') . '.priv' . $priv);
	}

	protected function addMenu($action, $params = null, $title = null, $uri = null)
	{
		$this->_menu->add($this->_getParam('controller'), $action, $params, $title, $uri);
	}

	protected function setupMenu()
	{
	}

	public function getForm()
	{
		if (empty($this->_formName))
			return new Luna_Form;

		return new $this->_formName(array(
			'method'	=> 'post',
			'action'	=> '/' . $this->_getParam('controller') . '/save'
		));
	}

	public function indexAction()
	{
		$this->_forward('list');
	}

	public function listAction()
	{
		$table = Luna_Table::factory($this->_model->selectComplete(), $this->_model, $this->_tableConfig);
		$table->getPaginator()->setCurrentPageNumber($this->_getParam('page', $table->getPaginator()->getCurrentPageNumber()));

		$this->view->table = $table;
	}

	public function createAction()
	{
		$this->view->form = $this->getForm();
	}

	public function readAction()
	{
		$form = $this->getForm();
		$object = $this->_model->find($this->_getParam('id'))->current();
		if (!empty($object))
		{
			$form->populate($object->toArray());
			$this->addTitle($object->id);
		}
		$this->view->form = $form;
	}

	public function saveAction()
	{
		$form = $this->getForm();

		if (!$form->isValid($this->getRequest()->getPost()))
		{
			$this->view->form = $form;
			$this->addTitle($form->getValue('id'));
			$this->view->setTemplate($this->_getParam('controller') . '/' . ($form->getValue('id') == 0 ? 'create' : 'read'));
			return;
		}

		$values = $form->getValues();
		$insertId = $this->_model->inject($values);

		if (!empty($insertId))
		{
			$this->addMessage('changes_saved');
			$this->_redirect('/' . $this->_getParam('controller') . '/read/id/' . $insertId);
		}
		else
		{
			$this->addError('changes_not_saved_db');
			$this->view->form = $form;
			$this->view->setTemplate($this->_getParam('controller') . '/' . ($form->getValue('id') == 0 ? 'create' : 'read'));
		}
	}

	public function deleteAction()
	{
		$id = intval($this->_getParam('id'));
		if (!empty($id))
		{
			if ($this->_model->deleteId($id))
				$this->addMessage('deleted');
			else
				$this->addError('delete_failed');
		}

		$this->_redirect('/' . $this->_getParam('controller') . '/list');
	}

	public function translate($key, $params = null)
	{
		return $this->_t->_($key, $params);
	}

	public function clearTitle()
	{
		$session = new Zend_Session_Namespace('template');
		unset($session->title);
	}

	public function addTitle($title, $params = null)
	{
		$session = new Zend_Session_Namespace('template');
		$session->title[] = $this->_t->_($title, $params);
	}

	public function delTitle()
	{
		$session = new Zend_Session_Namespace('template');
		unset($session->title[count($session->title) - 1]);
		if (!empty($session->title))
		{
			$session->title = array_values($session->title);
		}
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
}
