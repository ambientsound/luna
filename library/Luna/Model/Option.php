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

class Luna_Model_Option extends Luna_Db_Table
{
	protected $_primary = 'key';

	protected static $_data = null;

	public function __get($key)
	{
		if (empty(self::$_data))
			$this->populate();

		return !isset(self::$_data->$key) ? null : self::$_data->$key;
	}

	protected function getAll()
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('options', array('key', 'value'))
			->order('key ASC');

		return $this->db->fetchPairs($select);
	}

	protected function populate()
	{
		self::$_data = array();
		$this->addpopulation(self::$_data, $this->getAll(), null);
		self::$_data = new Zend_Config(self::$_data);
	}

	/*
	 * Recursive function, builds a nested array of configuration variables.
	 */
	public function addpopulation(&$dest, &$src, $base)
	{
		reset($src);
		$key = key($src);

		while (!empty($src) && (!strlen($base) || substr($key, 0, strlen($base)) == $base))
		{
			$key = (!strlen($base) ? $key : substr($key, strlen($base) + 1));
			$deep = explode('.', $key);

			/* Leaf node? */
			if (count($deep) == 1)
			{
				$dest[$key] = array_shift($src);
				$key = key($src);
				continue;
			}

			/* Recurse into deeper level */
			$key = $deep[0];
			$dest[$key] = array();
			$this->addpopulation($dest[$key], $src, (!strlen($base) ? $key : $base . '.' . $key));

			reset($src);
			$key = key($src);
		}
	}
}
