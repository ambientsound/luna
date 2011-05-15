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

class Luna_View_Helper_Title extends Luna_Iterator
{
	protected $_data = array();

	protected $_translator = null;

	protected $_front = null;

	public function __construct()
	{
		$this->_translator = Zend_Registry::get('Zend_Translate');
		$this->_session = new Zend_Session_Namespace('view');
		$this->_front = Zend_Controller_Front::getInstance();
	}

	public function init(Zend_Controller_Request_Abstract $request)
	{
		$this->clear();

		$baseurl = '/';
		$basetitle = 'title';
		$this->add($baseurl, $basetitle);

		$controller = $request->getControllerName();
		$baseurl .= $controller;
		$basetitle .= '_' . $controller;

		if ($controller != $this->_front->getDefaultControllerName())
			$this->add($baseurl, $basetitle);

		$action = $request->getActionName();
		$baseurl .= '/' . $action;
		$basetitle .= '_' . $action;

		if ($action != $this->_front->getDefaultAction())
			$this->add($baseurl, $basetitle);
	}

	public function add($uri, $title, $params = null)
	{
		$this->_data[] = array(
			'url'		=> $uri,
			'title'		=> $this->_translator->_($title, $params)
		);
	}

	public function clear()
	{
		$this->_data = array();
	}

	public function getTitle($mode = 'condensed', $separator = ' | ', $direction = 'ltr')
	{
		if (empty($this->_data))
			return null;

		$titles = array();
		foreach ($this->_data as $t)
		{
			if (!empty($t['title']))
				$titles[] = $t['title'];
		}

		if (empty($titles))
			return null;

		if ($direction == 'rtl')
			$titles = array_reverse($titles);

		$last = count($titles) - 1;

		switch($mode)
		{
			case 'condensed':
				if ($last == 0)
					return $titles[0];

				return $titles[0] . $separator . $titles[$last];

			case 'top':
				return $titles[$last];

			case 'base':
				return $titles[0];

			case 'full':
			default:
				return join($separator, $titles);
		}

		return null;
	}
}
