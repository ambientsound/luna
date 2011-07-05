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

class Luna_Admin_Form_Pages extends Luna_Form
{
	public function init()
	{
		parent::init();

		$this->addElement('Hidden', 'id');
		$this->addElement('Text', 'title', array('required' => true));
		$this->addElement('Tinymce', 'body');
		$this->addElement('Text', 'slug', array('required' => true));
		$this->addElement('Select', 'parent');
		$this->addElement('Picture', 'picture');
		$this->addElement('Select', 'nodetype');
		$this->addElement('Select', 'template');
		$this->addElement('Checkbox', 'spider_sitemap');
		$this->addElement('Checkbox', 'spider_index');
		$this->addElement('Checkbox', 'spider_follow');
		$this->addElement('Checkbox', 'published');
		$this->addElement('Text', 'publish_from');
		$this->addElement('Text', 'publish_to');
		$this->addElement('Text', 'modified', array('ignore' => true, 'readonly' => true));
		$this->addElement('Textarea', 'metadesc');

		$this->addElement('Submit', 'submit');

		$this->id->addValidator('Digits');
		$this->slug->addFilter(new Luna_Filter_Slug);
		$this->modified->addFilter(new Luna_Filter_Humantime);
		$this->nodetype->setValue('pages');
		$this->published->setValue(true);
		$this->spider_sitemap->setValue(true);
		$this->spider_index->setValue(true);
		$this->spider_follow->setValue(true);
		$this->modified->setDescription('form_pages_submit_modified');

		$this->resetDecorators();
	}
}
