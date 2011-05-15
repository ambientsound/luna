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

	public function init()
	{
		/* Master template */
		$this->view->setMaster('layouts/' . $this->_layout);

		/* Translation setup */
		$this->_t = Zend_Registry::get('Zend_Translate');

		/* Breadpath/title setup */
		$this->path = new Luna_View_Helper_Title;
	}

	public function setMeta($name, $content, $params = null)
	{
		$this->_meta[$name] = $this->_t->_($content, $params);
	}

	public function preDispatch()
	{
		parent::preDispatch();

		$this->view->setTemplate($this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName());
		$this->path->add('/', 'title');
	}

	public function postDispatch()
	{
		parent::postDispatch();

		$this->view->request = $this->getRequest();
		$this->view->params = $this->getRequest()->getParams();
		$this->view->path = $this->path;
		$this->view->meta = $this->_meta;

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
}
