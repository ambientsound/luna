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

class Luna_Admin_Controller_Page extends Luna_Admin_Controller_Action
{
	protected $_modelName = 'Model_Pages';

	protected $_formName = 'Form_Pages';

	public function setupMenu()
	{
		$available = $this->model->getFormTreeList();
		$this->_menu->addsub('index');

		$nodetypes = Luna_Config::get('site')->formats->pagetypes;
		foreach ($nodetypes as $type)
			$this->_menu->addsub('create', array('nodetype' => $type), $this->translate('menu_page_create_' . $type));
	}

	public function indexAction()
	{
		Luna_Table_Cell_Slug::setMap($this->model->getFormTreeList());
		$this->acl->assert($this->model, 'list');
		$table = new Luna_Table($this->model->getTableName(), $this->model, $this->getRequest());
		$this->view->table = $table;
	}

	public function getForm()
	{
		if (!empty($this->_form))
			return $this->_form;

		$this->object->load();

		$modelname = $this->getRequest()->getParam('nodetype', $this->object->nodetype);
		if (!empty($modelname))
		{
			$modelname = strtoupper($modelname[0]) . strtolower(substr($modelname, 1));
			$formname = 'Form_Page_' . $modelname;
			if (@class_exists($formname))
				$this->_formName = $formname;

			$formname = 'Model_Page_' . $modelname;
			if (@class_exists($formname))
				$this->object = Luna_Object::factory(new $formname, $this->object->toArray());
		}

		$this->object->loadRelation();
		$this->object->loadStickers();

		$this->_form = new $this->_formName;
		$this->_form->setRequest($this->getRequest());
		$this->_form->setPrefix('form_pages');

		$available = $this->model->getFormTreeList();
		$this->_form->parent->setMultiOptions($available);

		$nodetype = $this->_getParam('nodetype', $this->object->nodetype);
		$nodetype = empty($nodetype) ? 'pages' : $nodetype;
		$this->_form->template->setMultiOptions(Luna_Template::scanFront($nodetype));

		$nodetypes = Luna_Config::get('site')->formats->pagetypes;
		foreach ($nodetypes as $type)
			$this->_form->nodetype->addMultiOption($type, 'form_pages_nodetype_' . $type);

		$this->_form->nodetype->setValue($nodetype);

		if (empty($this->object->id))
			return $this->_form;

		$this->_form->parent->removeMultiOption($this->object->id);
		$this->_form->parent->setValue($this->object->getParentId());

		if (!$this->object->isLeaf())
		{
			/* Disable moving entire trees for now. */
			$this->_form->slug->setAttrib('readonly', true);
			$this->_form->parent->setAttrib('disabled', true);
			$this->_form->parent->addError('page_children_locked');
		}

		$this->_form->populate($this->object->toArray());

		return $this->_form;
	}
}
