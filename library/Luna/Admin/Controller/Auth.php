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

class Luna_Admin_Controller_Auth extends Luna_Admin_Controller_Action
{
	public function indexAction()
	{
		$this->_redirect('login');
	}

	public function preDispatch()
	{
		if (Zend_Auth::getInstance()->hasIdentity())
		{
			// If the user is logged in, we don't want to show the login form;
			// however, the logout action should still be available
			if ('logout' != $this->getRequest()->getActionName())
			{
				$this->_helper->redirector('index', 'index');
			}
		}
		else
		{
			// If they aren't, they can't logout, so that action should
			// redirect to the login form
			if ('logout' == $this->getRequest()->getActionName())
			{
				$this->_helper->redirector('index');
			}
		}

		$this->view->setTemplate($this->_getParam('controller') . '/' . $this->_getParam('action'));
	}

	public function loginAction()
	{
		$cfg = Luna_Config::get('site');
		$form = $this->getForm();
		$request = $this->getRequest();
		$this->delTitle();

		$form->populate($request->getParams());
		if (!$request->isPost() || !$form->isValid($request->getPost()))
		{
			$this->view->form = $form;
			return;
		}

		$auth = $this->getAuthAdapter();
		$auth->setIdentity($form->getValue('email'));
		$auth->setCredential($form->getValue('password'));

		$result = $auth->authenticate($auth);

		if (!$result->isValid())
		{
			$form->setDescription('form_login_invalidlogin');
			$this->view->form = $form;
			return;
		}

		$updates = array(
			'lastlogin'	=> new Zend_Db_Expr('NOW()'),
			'logincount'	=> new Zend_Db_Expr(Zend_Registry::get('db')->quoteIdentifier('logincount') . ' + 1')
		);

		$user = $auth->getResultRowObject(null, array('password', 'salt'));
		$model = new Zend_Db_Table('users');
		$model->update($updates, Zend_Registry::get('db')->quoteInto('id = ?', $user->id));

		Zend_Auth::getInstance()->getStorage()->write($user);

		$path = '/' . $this->_getParam('path');
		$this->_redirect($path);
	}

	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_helper->redirector('index');
	}

	public function getAuthAdapter()
	{
		return new Luna_Auth_Adapter_DbTable(
			Zend_Registry::get('db'),
			'users',
			'email',
			'password',
			'SHA1(CONCAT("' . Zend_Registry::get('db_salt') . '", ?, salt))'
		);
	}

	public function getForm()
	{
		return new Form_Login(array(
			'method'	=> 'post',
			'action'	=> '/admin/auth/login'
		));
	}
}
