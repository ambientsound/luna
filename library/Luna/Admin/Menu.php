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

class Luna_Admin_Menu extends Luna_Menu
{
	private $_curmenu = null;

	public function init()
	{
		$config = Luna_Config::get('site')->menu->toArray();
		$request = Zend_Controller_Front::getInstance()->getRequest();

		$paths = array(
			'global'	=> APPLICATION_PATH . '/controllers/',
			'local'		=> LOCAL_PATH . '/controllers/'
		);

		$modules = array();
		$hide = explode(',', $config['hide']);
		$sort = explode(',', $config['sort']);

		foreach ($paths as $dir)
		{
			if (!is_dir($dir))
				continue;

			$handle = opendir($dir);
			while (($file = readdir($handle)) !== false)
			{
				$matches = array();
				if (is_readable($dir . $file) && preg_match('/^([\w]+)Controller.php$/', $file, $matches))
				{
					$controller = strtolower($matches[1]);
					if (array_search($controller, $hide) === false)
						$modules[] = $controller;
				}
			}
			closedir($handle);
		}

		if (empty($modules))
			return;

		if (!empty($sort))
		{
			foreach ($sort as $module)
			{
				if (($index = array_search($module, $modules)) !== false)
				{
					$mods[] = $module;
					unset($modules[$index]);
				}
			}
			$modules = array_merge($sort, $modules);
		}

		foreach ($modules as $module)
		{
			if ($module == $request->getControllerName())
				$this->_curmenu =& $this->add($module);
			else
				$this->add($module);
		}

		$this->add('auth', 'logout');
	}

	public function addsub($action = null, $params = null, $title = null, $uri = null)
	{
		if (empty($this->_curmenu))
			return false;

		return $this->_curmenu->add($this->_curmenu->controller, $action, $params, $title, $uri);
	}
}
