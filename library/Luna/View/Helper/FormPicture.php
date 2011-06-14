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

class Luna_View_Helper_FormPicture extends Zend_View_Helper_FormElement
{
	public function formPicture($name, $value = null, array $attribs = null)
	{
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable

		if (isset($id))
		{
			if (isset($attribs) && is_array($attribs))
				$attribs['id'] = $id;
			else
				$attribs = array('id' => $id);
		}

		$t =& Zend_Registry::get('Zend_Translate');

		$html = $this->_hidden($name, $value, $attribs);
		$html .= '<div class="picture-placeholder" id="' . $name . '-picture' . '">';
		if (!empty($value))
		{
			$pic = new Luna_Object(new Model_Files, $value);
			if ($pic->load())
			{
				$html .= '<img src="' . $pic->thumbnail['medium']['pub'] . '" />';
			}
		}
		$html .= '</div>';
		$html .= $this->formButton($attribs);

		return $html;
	}

	public function formButton($attribs)
	{
		$info = $this->_getInfo($attribs);
		if (empty($attribs['button']))
			$attribs['button'] = 'choose_' . $info['id'];

		$t =& Zend_Registry::get('Zend_Translate');

		return '<button type="button"' . $this->_htmlAttribs($info['attribs']) . '>' . $t->_($attribs['button']) . '</button>';
	}
}
