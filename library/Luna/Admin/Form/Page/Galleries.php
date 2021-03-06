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

class Luna_Admin_Form_Page_Galleries extends Luna_Admin_Form_Pages
{
	public function init()
	{
		parent::init();

		$this->addElement('Select', 'viewmode');
		$this->addElement('Select', 'size_thumbnails');
		$this->addElement('Select', 'size_flow');
		$this->addElement('Text', 'page_limit');

		$this->addElement('Hidden', 'pictures');
		$this->addElement('Checkbox', 'use_folder');
		$this->addElement('Select', 'folder_id');

		$model = new Model_Folders;
		$folders = $model->getFlatAssocList('name');
		$this->folder_id->setMultiOptions(array('/'));
		$this->folder_id->addMultiOptions($folders);
		$this->page_limit->addFilter('Digits');

		$model = new Luna_Admin_Model_Page_Galleries;

		$this->resetDecorators();
	}

	public function populate(array $values)
	{
		parent::populate($values);
		if (!empty($values['folder_id']))
			$this->use_folder->setValue(true);
	}
}
