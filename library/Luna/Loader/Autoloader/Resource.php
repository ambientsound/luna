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


/*
 * IMPORTANT: This class forks Zend Framework 1.11.6. The addResourceType and getClassPath functions
 * had to be modified to allow multiple directories. This implementation might break if ZF changes
 * these functions in the future.
 */
class Luna_Loader_Autoloader_Resource extends Zend_Loader_Autoloader_Resource
{
	public function setBasePath($path)
	{
		if (!empty($path) && !is_array($path))
			$path = array($path);

		$this->_basePath = (array) $path;
		return $this;
	}

	public function addResourceType($type, $path, $namespace = null)
	{
		$type = strtolower($type);

		if (!isset($this->_resourceTypes[$type]))
		{
			if (null === $namespace)
			{
				require_once 'Zend/Loader/Exception.php';
				throw new Zend_Loader_Exception('Initial definition of a resource type must include a namespace');
			}
			$namespaceTopLevel = $this->getNamespace();
			$namespace = ucfirst(trim($namespace, '_'));
			$this->_resourceTypes[$type] = array(
				'namespace' => empty($namespaceTopLevel) ? $namespace : $namespaceTopLevel . '_' . $namespace,
			);
		}
		if (!is_string($path))
		{
			require_once 'Zend/Loader/Exception.php';
			throw new Zend_Loader_Exception('Invalid path specification provided; must be string');
		}

		$paths = $this->getBasePath();
		foreach ($paths as $p)
		{
			$this->_resourceTypes[$type]['path'][] = $p . '/' . rtrim($path, '\/');
		}

		$component = $this->_resourceTypes[$type]['namespace'];
		$this->_components[$component] = $this->_resourceTypes[$type]['path'];
		return $this;
	}

	public function getClassPath($class)
	{
		$segments = explode('_', $class);
		$namespaceTopLevel = $this->getNamespace();
		$namespace = '';

		if (!empty($namespaceTopLevel))
		{
			$namespace = array();
			$topLevelSegments = count(explode('_', $namespaceTopLevel));
			for ($i = 0; $i < $topLevelSegments; $i++)
			{
				$namespace[] = array_shift($segments);
			}
			$namespace = implode('_', $namespace);
			if ($namespace != $namespaceTopLevel)
			{
				// wrong prefix? we're done
				return false;
			}
		}

		if (count($segments) < 2)
		{
			// assumes all resources have a component and class name, minimum
			return false;
		}

		$final	 = array_pop($segments);
		$component = $namespace;
		$lastMatch = false;
		do
		{
			$segment = array_shift($segments);
			$component .= empty($component) ? $segment : '_' . $segment;
			if (isset($this->_components[$component]))
			{
				$lastMatch = $component;
			}
		} while (count($segments));

		if (!$lastMatch)
		{
			return false;
		}

		$final = substr($class, strlen($lastMatch) + 1);
		$paths = $this->_components[$lastMatch];

		foreach ($paths as $path)
		{
			$classPath = $path . '/' . str_replace('_', '/', $final) . '.php';

			if (Zend_Loader::isReadable($classPath))
			{
				return $classPath;
			}
		}

		return false;
	}
}
