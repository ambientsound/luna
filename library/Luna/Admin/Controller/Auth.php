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
	protected $_modelName = 'Model_Users';

	public function indexAction()
	{
		$this->_redirect('login');
	}

	public function loginAction()
	{
		$cfg = Luna_Config::get('site');
		$this->_form = $this->getForm();
		$request = $this->getRequest();

		$this->_form->populate($request->getParams());
		if (!$request->isPost() || !$this->_form->isValid($request->getPost()))
		{
			$usercount = $this->model->count();
			if ($usercount == 0)
			{
				return $this->_redirect('/auth/setup');
			}
			$this->view->form = $this->_form;
			return;
		}

		$user = $this->model->checkAuth($this->_form->getValue('username'), $this->_form->getValue('password'));

		if (!empty($user))
		{
			$this->user = new Luna_User($user);
			$this->acl->setUser($this->user);
		}

		if (!$this->user->isValid() || !$this->acl->can('global', 'login'))
		{
			$this->addError('login_failed');
			$this->view->form = $this->_form;
			return;
		}

		$updates = array(
			'lastlogin'	=> new Zend_Db_Expr('NOW()'),
			'logincount'	=> new Zend_Db_Expr($this->model->db->quoteIdentifier('logincount') . ' + 1')
		);

		$model = new Zend_Db_Table('users');
		$model->update($updates, $this->model->db->quoteInto('id = ?', $user['id']));

		Zend_Auth::getInstance()->getStorage()->write($user);

		$path = '/' . $this->_getParam('path');
		$this->_redirect($path);
	}

	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_helper->redirector('index');
	}

	public function setupAction()
	{
		if ($this->model->count() != 0)
		{
			return $this->_redirect('/auth/login');
		}

		$this->_form = $this->getInitForm();

		if ($this->getRequest()->isPost() && $this->_form->isValid($_POST))
		{
			$hash = new Luna_Phpass(null, true);
			$values = $this->_form->getValues();
			$values['username'] = 'admin';
			$values['enabled'] = true;

			try
			{
				$this->model->db->beginTransaction();

				if ($userid = $this->model->inject($values))
				{
					if ($this->model->addUserRole($userid, 'superuser'))
					{
						if ($this->model->db->commit())
						{
							$this->addMessage('luna_initial_setup_succeeded');
							return $this->_redirect('/auth/login');
						}
					}
				}
			}
			catch (Zend_Exception $e)
			{
			}

			$this->model->db->rollBack();
			$this->addError('luna_initial_setup_failed');
		}
	}

	public function getInitForm()
	{
		return new Form_Login_Init(array(
			'method'	=> 'post',
			'action'	=> '/admin/auth/setup'
		));
	}

	public function getForm()
	{
		$this->_form = new Form_Login;
		$this->_form->setRequest($this->getRequest());
		return $this->_form;
	}
}
