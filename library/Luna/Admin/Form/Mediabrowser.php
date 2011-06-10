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

class Luna_Admin_Form_Mediabrowser extends Luna_Form
{
	public function init()
	{
		parent::init();

		$this->addElement('Hidden', 'id');
		$this->addElement('Select', 'template');
		$this->addElement('Select', 'size');
		$this->addElement('Text', 'customsize');
		$this->addElement('Select', 'align');
		$this->addElement('Select', 'link');
		$this->addElement('Text', 'customlink');
		$this->addElement('Text', 'title');
		$this->addElement('Text', 'alt');
		$this->addElement('Submit', 'submit');

		$this->link->setMultiOptions(array(
			''		=> 'form_mediabrowser_link_no',
			'big'		=> 'form_mediabrowser_link_big',
			'custom'	=> 'form_mediabrowser_link_custom',
		));

		$this->align->setMultiOptions(array(
			''		=> 'form_mediabrowser_align_none',
			'alignleft'	=> 'form_mediabrowser_align_left',
			'center'	=> 'form_mediabrowser_align_center',
			'alignright'	=> 'form_mediabrowser_align_right'
		));

		$this->template->setMultiOptions(Luna_Template::scanAdmin('media/templates'));

		$this->setAction('/admin/util/getimage');

		$this->resetDecorators();
	}

	public function setImage(Luna_Object $image)
	{
		if (!$image->load())
			return false;

		$this->populate($image->toArray());

		$this->size->setMultiOptions(array('' => $image->size . ' (' . Zend_Registry::get('Zend_Translate')->_('file_original_size') . ')'));
		foreach ($image->thumbnail as $key => $size)
			$this->size->addMultiOptions(array($key => $size['size']));
		$this->size->addMultiOptions(array('custom' => Zend_Registry::get('Zend_Translate')->_('form_mediabrowser_size_custom')));

		if ($this->getValue('alt') == null)
			$this->alt->setValue($image->title);

		return true;
	}
}
