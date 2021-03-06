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

class Luna_Admin_Controller_User extends Luna_Admin_Controller_Action
{
	protected $_modelName = 'Model_Users';

	protected $_formName = 'Form_Users';

	public function setupMenu()
	{
		$this->_menu->addsub('index');
		$this->_menu->addsub('create');
	}

	public function deleteAction()
	{
		if ($this->object->load() && array_search('superuser', $this->object->roles) !== false)
			$this->acl->assert($object, 'superuser');

		if ($this->object->id == $this->user->id)
			throw new Zend_Exception($this->translate('error_cant_delete_yourself'));

		return parent::deleteAction();
	}

	public function saveToDb($source)
	{
		if ($source instanceof Luna_Object)
			$values = $source->toArray();
		elseif ($source instanceof Zend_Form)
			$values = $source->getValues();
		elseif (is_array($source))
			$values = $source;
		else
			return false;

		if (!($source instanceof Luna_Object))
		{
			$source = new Luna_Object($this->model, $values['id']);
			$source->load();
		}

		if ($source->id == $this->user->id && array_search('superuser', $this->user->roles) !== false && array_search('superuser', $source->roles) === false)
			$values['roles'][] = 'superuser';

		if (is_array($source->roles) && array_search('superuser', $source->roles) !== false)
		{
			$this->acl->assert($source, 'superuser');
			return parent::saveToDb($values);
		}

		if (is_array($source->roles) && array_search('superuser', $source->roles) !== false)
			$this->acl->assert($source, 'superuser');

		return parent::saveToDb($values);
	}

	public function getForm()
	{
		parent::getForm();
		$roles = $this->model->getRoleList();
		$roles = array_combine($roles, $roles);
		foreach ($roles as &$role)
			$role = 'role_' . $role;
		$this->_form->roles->setMultiOptions($roles);
	}
}
