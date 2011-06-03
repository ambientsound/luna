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

class Luna_Menu extends Luna_Stdclass
{
	public function __construct($params = null)
	{
		$this->_data = (array)$params;
		$this->init();
	}

	public function init() {}

	public function __get($name)
	{
		if ($name == 'active')
		{
			if ($this->_data['active'])
				return true;

			if (!empty($this->_data['children']))
			{
				foreach ($this->_data['children'] as $child)
				{
					if ($child->active)
						return true;
				}
			}

			return false;
		}

		return parent::__get($name);
	}

	public function add($controller, $action = null, $params = null, $title = null, $uri = null)
	{
		$slugfilter = new Luna_Filter_Slug();

		if (empty($title))
			$title = Zend_Registry::get('Zend_Translate')->translate("menu_{$controller}" . (empty($action) ? null : "_{$action}"));

		$m = array(
			'controller'	=> $controller,
			'action'	=> $action,
			'params'	=> $params,
			'title'		=> $title
		);

		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();

		$defaultcontroller = $front->getDefaultControllerName();
		$defaultaction = $front->getDefaultAction();

		if ($uri == null)
		{
			if ($m['controller'] != $defaultcontroller || !empty($m['action']) || !empty($m['params']))
				$uri .= '/' . $m['controller'];

			if ($m['action'] != $defaultaction || !empty($m['params']))
				$uri .= '/' . $m['action'];

			if (!empty($params))
			{
				foreach ($params as $key => $param)
					$uri .= "/{$key}/{$param}";
			}
		}

		if ($request->getControllerName() == $m['controller'])
		{
			if ($request->getActionName() == $m['action'] || (empty($m['action'])))
			{
				$m['active'] = true;
				if (!empty($m['params']))
				{
					foreach ($m['params'] as $key => $val)
					{
						if ($request->getParam($key) != $val)
						{
							$m['active'] = false;
							break;
						}
					}
				}
			}
		}

		$m['url'] = rtrim($front->getBaseUrl() . $uri, '/');
		$m['class'] = 'menu-' . $slugfilter->filter(str_replace('/', ' ', $m['url']));

		return ($this->_data['children'][] = new Luna_Menu($m));
	}
}
