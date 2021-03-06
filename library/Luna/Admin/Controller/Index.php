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

class Luna_Admin_Controller_Index extends Luna_Admin_Controller_Action
{
	public function indexAction()
	{
		$pagemodel = new Model_Pages;
		$filemodel = new Model_Files;

		$this->getRequest()->setParam('sort', 'modified');
		$this->getRequest()->setParam('order', 'desc');

		if ($this->acl->can($pagemodel, 'list'))
		{
			Luna_Table_Cell_Slug::setMap($pagemodel->getFormTreeList());
			$pagetable = new Luna_Table($pagemodel->getTableName(), $pagemodel, $this->getRequest());
			$pagetable->getPaginator()->setItemCountPerPage(5);
			$this->view->pagetable = $pagetable;
		}

		if ($this->acl->can($filemodel, 'list'))
		{
			$filetable = new Luna_Table($filemodel->getTableName(), $filemodel, $this->getRequest());
			$filetable->getPaginator()->setItemCountPerPage(5);
			$this->view->filetable = $filetable;
		}
	}
}
