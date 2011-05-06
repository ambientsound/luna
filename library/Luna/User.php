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

class Luna_User
{
	protected $_role;

	protected $_data = null;

	public function __set($name, $value)
	{
		$trace = debug_backtrace();
		trigger_error(
			'Property ' . $name . ' is read only' .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_ERROR);
	}

	public function __get($name)
	{
		if (empty($this->_data))
			return null;

		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}

	public function __isset($name)
	{
		return isset($this->_data[$name]);
	}

	public function __unset($name)
	{
		$trace = debug_backtrace();
		trigger_error(
			'Property ' . $name . ' is read only' .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_ERROR);
	}

	public function __construct($handle)
	{
		if (empty($handle))
		{
			return;
		}

		$model = new Model_Users();

		if (is_numeric($handle))
		{
			$this->_data = $model->get($handle);
		}
		elseif (is_array($handle))
		{
			if (is_numeric($handle['id']))
			{
				$this->_data = $model->get($handle['id']);
			}
			else
			{
				return;
			}
		}
		else
		{
			$this->_data = $model->getByUsername($handle);
		}

		unset($this->_data->password);
	}

	public function registerActivity()
	{
		if (!$this->isValid())
			return false;

		$cfg = Luna_Config::get('site');
		$model = new Zend_Db_Table('users');

		$updates = array(
			'activity' => new Zend_Db_Expr('NOW()')
		);
		return $model->update($updates, Zend_Registry::get('db')->quoteInto('id = ?', $this->_data['id']));
	}

	public function isValid()
	{
		return (!empty($this->_data));
	}

	public function toArray()
	{
		return $this->_data;
	}
}
