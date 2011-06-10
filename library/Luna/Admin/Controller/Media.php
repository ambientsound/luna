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

class Luna_Admin_Controller_Media extends Luna_Admin_Controller_Action
{
	protected $_modelName = 'Model_Files';

	protected $_formName = 'Form_File';

	public function setupMenu()
	{
		$this->_menu->addsub('index');
		$this->_menu->addsub('create');
	}

	public function browseAction()
	{
		$this->view->setMaster('media/browse');

		$model = new Model_Folders;
		$folders = $model->getFlatAssocList('name');

		$foldform = new Form_Folder;
		$foldform->removeElement('newfolder');
		$foldform->removeElement('submit');
		$foldform->folder->setMultiOptions(array('/'));
		$foldform->folder->addMultiOptions($folders);
		$foldform->folder->setValue($this->_getParam('folder', 0));

		$this->model->setFolderFilter($foldform->getValue('folder'), $foldform->getValue('recurse'));
		$pictures = new Zend_Paginator(new Luna_Paginator_Adapter_Images($this->model->selectImages()));

		$form = new Form_File;
		$form->folder_id->setMultiOptions(array('/'));
		$form->folder_id->addMultiOptions($folders);

		if ($this->getRequest()->isPost() && $form->isValid($_POST))
		{
			$this->acl->assert($this->model, 'create');
			if (($id = $this->saveToDb($form)) !== false)
				return $this->_redirect('/media/browse?id='. $id);
			else
				$form->addError('form_incomplete');
		}

		$file = new Luna_Object(new Model_Files, $this->_getParam('id'));
		if (($src = $this->_getParam('src')) != null)
		{
			/* Determine image id, alignment and size from URL */
			if (($pos = strrpos($src, '/')) !== false)
			{
				$filename = substr($src, $pos + 1);
				if (($size = strrpos($src, '/', -strlen($filename) - 2)) !== false)
				{
					$size = substr($src, $size + 1, $pos - $size - 1);
				}
				$file->load($this->model->getIdByFilename($filename));
			}
		}

		$file->load();

		$inserter = new Form_Mediabrowser;
		$inserter->setImage($file);
		if (!empty($size))
			$inserter->size->setValue($size);
		if (($class = $this->_getParam('class')) != null)
			$inserter->align->setValue($class);

		$this->view->insertform = $inserter;
		$this->view->picture = $file;
		$this->view->upform = $form;
		$this->view->folders = $foldform;
		$this->view->pictures = $pictures;
	}

	public function indexAction()
	{
		$model = new Model_Folders;
		$folders = $model->getFlatAssocList('name');
		$foldform = new Form_Folder;
		$foldform->folder->setMultiOptions(array('/'));
		$foldform->folder->addMultiOptions($folders);
		$foldform->folder->setValue($this->_getParam('folder', 0));

		if ($this->getRequest()->isPost() && $foldform->isValid($_POST))
		{
			if (($name = $foldform->getValue('newfolder')) != null)
			{
				try
				{
					$id = $model->inject(array(
						'parent'	=> $foldform->getValue('folder'),
						'name'		=> $name
					));
				}
				catch (PDOException $e)
				{
					$this->addError('object_failed_save_db', $e->getMessage());
				}
				if (!empty($id))
					return $this->_redirect('/media?folder=' . $id);
			}
		}

		$this->model->setFolderFilter($foldform->getValue('folder'), $foldform->getValue('recurse'));

		parent::indexAction();

		$this->view->folders = $foldform;
	}

	public function saveToDb($source)
	{
		if ($source instanceof Zend_Form)
		{
			$old = new Luna_Object($this->model, $source->getValues());
			$old->load();

			if (!empty($old->id))
				$this->acl->assert($old, 'update');

			$insertId = $this->model->upload($source->upload, $old->id);
			if (empty($insertId) && empty($old->id))
				return false;
		}

		$values = $source->getValues();
		unset($values['upload']);
		$values['id'] = (empty($insertId) ? $values['id'] : $insertId);

		return parent::saveToDb($values);
	}

	public function getForm()
	{
		if (!parent::getForm())
			return false;

		$model = new Model_Folders;
		$folders = $model->getFlatAssocList('name');
		$this->_form->folder_id->setMultiOptions(array('/'));
		$this->_form->folder_id->addMultiOptions($folders);
		$this->_form->folder_id->setValue($this->_getParam('folder', 0));
	}
}
