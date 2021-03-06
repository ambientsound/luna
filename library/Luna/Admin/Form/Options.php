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

class Luna_Admin_Form_Options extends Luna_Form
{
	public function setup(array $options)
	{
		if (empty($options))
			return false;

		foreach ($options as $key => $opt)
		{
			switch($opt['type'])
			{
				case 'text':
				default:
					$type = 'Text';
					break;
				case 'textarea':
					$type = 'Textarea';
					break;
				case 'page':
					$type = 'Select';
					break;
				case 'bool':
				case 'checkbox':
					$type = 'Checkbox';
					break;
			}
			$key = str_replace('.', '_', $key);
			$this->addElement($type, $key);
			if (empty($opt['null']))
			{
				$this->$key->setAttrib('required', true);
				$this->$key->setRequired(true);
			}
			if ($opt['type'] == 'page')
			{
				$pagemodel = new Model_Pages;
				$pages = $pagemodel->getFormTreeList();
				unset($pages[0]);
				$this->$key->setMultiOptions($pages);
			}
			if (isset($opt['value']))
			{
				$this->$key->setValue($opt['value']);
			}
		}

		$this->setPrefix('option');
		$this->addElement('Submit', 'submit');

		$this->resetDecorators();
	}

	public function getValues()
	{
		$values = parent::getValues();
		$newvals = array();

		foreach ($values as $key => $val)
			$newvals[str_replace('_', '.', $key)] = $val;

		return $newvals;
	}
}
