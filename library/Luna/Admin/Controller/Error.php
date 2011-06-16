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

class Luna_Admin_Controller_Error extends Luna_Admin_Controller_Action
{
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');

		switch ($errors->type)
		{
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setHttpResponseCode(404);
				break;

			default:
		}

		$this->path->add(null, 'error');
	}

	public function postDispatch()
	{
		parent::postDispatch();

		$params = $this->getRequest()->getParams();

		if ($this->getInvokeArg('displayExceptions') == true)
		{
			$this->view->exception = $params['error_handler']->exception;
			$this->view->stacktrace = explode("\n", $params['error_handler']->exception->getTraceAsString());
		}

		if ($params['error_handler']->exception instanceof Luna_Acl_Exception)
		{
			$this->view->setTemplate('error/accessdenied');
			$this->path->add(null, 'error_access_denied');
		}
		else
		{
			$this->view->setTemplate('error/error');
		}

		unset($params['error_handler']);
		$this->view->params = $params;

		if ($this->getRequest()->isXmlHttpRequest())
			$this->_helper->viewRenderer->setNoRender(true);

	}
}
