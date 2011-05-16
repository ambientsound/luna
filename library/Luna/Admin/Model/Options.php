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

class Luna_Admin_Model_Options extends Luna_Model_Option
{
	protected static $_tree = null;

	protected static $_defaults = array(
		'type'		=> 'text',
		'null'		=> true
	);

	protected function buildTree()
	{
		$config = Luna_Config::get('options');
		self::$_tree = array();

		foreach ($config as $module => $opts)
		{
			foreach ($opts as $option => $params)
			{
				if (empty($params))
					$params = self::$_defaults;
				else
					$params = array_merge(self::$_defaults, $params->toArray());

				self::$_tree[$module . '.' . $option] = $params;
			}
		}
	}

	protected function collapse(array $src, $base = null)
	{
		$re = array();

		foreach ($src as $key => $a)
		{
			if (is_array($a))
				$re = array_merge($re, $this->collapse($a, (!strlen($base) ? $key : $base . '.' .$key)));
			else
				$re[$base][$key] = $a;
		}

		return $re;
	}

	public function getModuleOptions($module)
	{
		$config = Luna_Config::get('options');
		if (empty($config->$module))
			return null;

		$options = $this->collapse($config->$module->toArray(), $module);
		$dbopts = $this->getAll();

		foreach ($dbopts as $key => $value)
		{
			if (isset($options[$key]))
				$options[$key]['value'] = $value;
		}

		return $options;
	}

	public function getModules()
	{
		$config = Luna_Config::get('options');
		return array_keys($config->toArray());
	}

	public function setOptions($values)
	{
		$this->db->beginTransaction();

		foreach ($values as $key => $value)
		{
			if (!$this->inject(array(
				'key'	=> $key,
				'value'	=> $value
			)))
			{
				$this->db->rollBack();
				return false;
			}
		}

		if ($this->db->commit())
			return true;

		$this->db->rollBack();
		return false;
	}
}
