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

class Luna_Query
{
	protected static $_front = null;

	protected static $_request = null;

	protected static $_params = array();

	public function __construct()
	{
		throw new Zend_Exception('This is a static class and can not be instantiated');
	}

	public static function init()
	{
		if (!empty(self::$_front))
			return;

		self::$_front = Zend_Controller_Front::getInstance();
		self::$_request = self::$_front->getRequest();

		$keys = array(
			self::$_request->getControllerKey(),
			self::$_request->getActionKey(),
			self::$_request->getModuleKey()
		);

		$params = self::$_request->getParams();
		foreach ($params as $key => $param)
			if (array_search($key, $keys) === false)
				self::$_params[$key] = $param;
	}

	public static function appendQuery($params)
	{
		self::init();
		$query = array_merge(self::$_params, $params);

		$url = self::$_front->getBaseUrl();
		if (self::$_request->getControllerName() != self::$_front->getDefaultControllerName() || self::$_request->getActionName() != self::$_front->getDefaultAction())
			$url .= '/' . self::$_request->getControllerName();
		if (self::$_request->getActionName() != self::$_front->getDefaultAction())
			$url .= '/' . self::$_request->getActionName();

		$query = http_build_query($query);
		if (!empty($query))
			$url .= '?' . $query;

		return $url;
	}
}
