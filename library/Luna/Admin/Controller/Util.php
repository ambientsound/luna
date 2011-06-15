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

class Luna_Admin_Controller_Util extends Luna_Admin_Controller_Action
{
	public function preDispatch()
	{
		$this->_helper->viewRenderer->setNoRender(true);
	}

	public function slugAction()
	{
		$filter = new Luna_Filter_Slug();
		echo $filter->filter($this->_getParam('source'));
	}

	public function templatesAction()
	{
		echo json_encode(Luna_Template::scanFront($this->_getParam('type', 'pages')));
	}

	public function filesAction()
	{
		$model = new Luna_Admin_Model_Folders;
		$folders = $model->getFiles($this->_getParam('folder', 0));
		echo json_encode($folders);
	}

	public function mediabrowserAction()
	{
		$this->_helper->viewRenderer->setNoRender(false);
		$file = new Luna_Object(new Model_Files, $this->_getParam('id'));
		$file->load();
		$inserter = new Form_Mediabrowser;
		$inserter->setImage($file);
		$this->view->setMaster('media/browse/select');
		$this->view->insertform = $inserter;
		$this->view->picture = $file;
	}

	public function getfolderAction()
	{
		$this->_helper->viewRenderer->setNoRender(false);
		$model = new Model_Files;
		$model->setFolderFilter($this->_getParam('folder'), $this->_getParam('recurse'));
		$pictures = new Zend_Paginator(new Luna_Paginator_Adapter_Images($model->selectImages()));
		$pictures->setItemCountPerPage(1000);
		$this->view->setMaster('media/browse/list');
		$this->view->pictures = $pictures;
	}

	public function getimageAction()
	{
		$file = new Luna_Object(new Model_Files, $this->_getParam('id'));
		if ($file->load())
		{
			$inserter = new Form_Mediabrowser;
			$inserter->setImage($file);

			if ($inserter->isValid($_POST))
			{
				if ($inserter->getValue('size') == 'custom')
				{
					$filemodel = new Model_Files;
					$size = $inserter->getValue('customsize');
					if ($filemodel->createThumbs($file->filename, array($size => $size)))
					{
						$file->reload();
						$inserter->setImage($file);
						$inserter->size->setValue($size);
					}
					else
					{
						header('HTTP/1.1 400 Thumbnail creation failed');
						header('Status: 400 Thumbnail creation failed');
						return;
					}
				}
				$this->_helper->viewRenderer->setNoRender(false);
				$this->view->opts = $inserter->getValues();
				$this->view->picture = $file;
				$this->view->setMaster('media/templates/' . $inserter->getValue('template'));
				return;
			}
		}

		header('HTTP/1.1 400 Missing picture id');
		header('Status: 400 Missing picture id');
	}
}
