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

class Luna_View_Smarty extends Zend_View_Abstract
{
	/**
	* Smarty object
	* @var Smarty
	*/
	protected $_smarty;

	/*
	 * Master template
	 */
	protected $_masterTemplate;
	protected $_template;

	/*
	 * Any error messages
	 */
	protected $_errors;

	/*
	 * Return a new smarty instance
	 */
	public function factory()
	{
		$paths = array(
			LOCAL_PATH . '/templates',
			APPLICATION_PATH . '/templates'
		);

		$params = array(
			'compile_dir'   => realpath(LOCAL_BASE_PATH . '/data/smarty/compile')
		);

		$view = new Luna_View_Smarty($paths, $params);
		$view->addPluginPath(LOCAL_PATH . '/smarty/');
		$view->addPluginPath(APPLICATION_PATH . '/smarty/');
		$view->addPluginPath(LUNA_PATH . '/library/smarty/');
		$view->doctype('HTML5');

		return $view;
	}

	/**
	* Constructor
	*
	* @param string $tmplPath
	* @param array $extraParams
	* @return void
	*/
	public function __construct($tmplPath = null, $extraParams = array())
	{
		require_once('Smarty/Smarty.class.php');

		$this->_smarty = new Smarty;
	
		if (null !== $tmplPath)
		{
			if (is_array($tmplPath))
			{
				while (($t = array_shift($tmplPath)) !== NULL)
				{
					$this->addScriptPath($t);
				}
			}
			else
			{
				$this->setScriptPath($tmplPath);
			}
		}
	
		foreach ($extraParams as $key => $value) {
			$this->_smarty->$key = $value;
		}

		$this->setLfiProtection(false);
	}

	/**
	* Return the template engine object
	*
	* @return Smarty
	*/
	public function getEngine()
	{
		return $this->_smarty;
	}
	
	/*
	 * Set the path to the templates
	 *
	 * @param string $path The directory to set as the path.
	 * @return void
	 */
	public function setScriptPath($path)
	{
		$this->_smarty->setTemplateDir($path);
	}
	
	/*
	 * Retrieve the current template directory
	 *
	 * @return string
	 */
	public function getScriptPaths()
	{
		if (is_array($this->_smarty->template_dir))
			return $this->_smarty->template_dir;

		return array($this->_smarty->template_dir);
	}
	
	/*
	 * Alias for setScriptPath
	 *
	 * @param string $path
	 * @param string $prefix Unused
	 * @return void
	 */
	public function setBasePath($path, $prefix = 'Zend_View')
	{
		return $this->setScriptPath($path);
	}
	
	/*
	 * Alias for setScriptPath
	 *
	 * @param string $path
	 * @param string $prefix Unused
	 * @return void
	 */
	public function addBasePath($path, $prefix = 'Zend_View')
	{
		return $this->setScriptPath($path);
	}

	public function addPluginPath($path)
	{
		$this->_smarty->addPluginsDir($path);
	}

	public function addScriptPath($path)
	{
		$this->_smarty->addTemplateDir($path);
	}
	
	/*
	 * Assign a variable to the template
	 *
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->_smarty->assign($key, $val);
	}

	public function __get($key)
	{
		return $this->_smarty->getTemplateVars($key);
	}
	
	/**
	* Allows testing with empty() and isset() to work
	*
	* @param string $key
	* @return boolean
	*/
	public function __isset($key)
	{
		return (null !== $this->_smarty->getTemplateVars($key));
	}
	
	/**
	* Allows unset() on object properties to work
	*
	* @param string $key
	* @return void
	*/
	public function __unset($key)
	{
		$this->_smarty->clear_assign($key);
	}
	
	/*
	 * Assign variables to the template
	 *
	 * Allows setting a specific key to the specified value, OR passing
	 * an array of key => value pairs to set en masse.
	 *
	 * @see __set()
	 * @param string|array $spec The assignment strategy to use (key or
	 * array of key => value pairs)
	 * @param mixed $value (Optional) If assigning a named variable,
	 * use this as the value.
	 * @return void
	 */
	public function assign($spec, $value = null)
	{
		if (is_array($spec)) {
			$this->_smarty->assign($spec);
			return;
		}
	
		$this->_smarty->assign($spec, $value);
	}
	
	/**
	 * Clear all assigned variables DUMMY function.
	 * We don't really want variables to be cleared after render()
	 *
	 * @return void
	 */
	public function clearVars()
	{
		return;
	}

	/**
	  * Set the main template which includes the page template.
	  *
	  * @param string $name The template to process.
	  * @return null
	  */
	public function setMaster($name)
	{
		if (empty($name))
		{
			$this->_masterTemplate = null;
			return;
		}
		$this->_masterTemplate = $name . '.tpl';
	}

	/*
	 * Override the standard template requested by render()
	 */
	public function setTemplate($name)
	{
		$this->_template = $name . '.tpl';
	}

	/**
	  * Processes a template and returns the output.
	  *
	  * @param string $name The template to process.
	  * @return string The output.
	  */
	public function render($name)
	{
		if (isset($this->_template))
		{
			$this->template = $this->_template;
		}

		if (empty($this->_masterTemplate))
		{
			return $this->_smarty->fetch($name);
		}
		else
		{
			return $this->_smarty->fetch($this->_masterTemplate);
		}
	}

	public function _run()
	{
		return true;
	}
}
