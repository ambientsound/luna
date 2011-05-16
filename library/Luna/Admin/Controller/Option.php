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

class Luna_Admin_Controller_Option extends Luna_Admin_Controller_Action
{
	protected $_modelName = 'Model_Options';

	protected $_formName = 'Form_Options';

	public function indexAction()
	{
		$opts = $this->model->getModuleOptions($this->_getParam('section', 'main'));
		if (empty($opts))
			return $this->_redirect('/option');

		$modules = $this->model->getModules();
		foreach ($modules as $module)
			$this->_menu->addsub('index', array('section' => $module), $this->translate('menu_option_' . $module));

		$this->getForm();
		$this->_form->setup($opts);

		if ($this->getRequest()->isPost() && $this->_form->isValid($_POST))
		{
			$this->acl->assert($this->model, 'set');

			$values = $this->_form->getValues();

			if ($this->model->setOptions($values))
			{
				$this->addMessage('options_saved');
				return $this->_redirect('/option/index/section/' . $this->_getParam('section', 'main'));
			}
			$this->addError('options_not_saved');
		}
	}
}
