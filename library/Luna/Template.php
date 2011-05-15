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

class Luna_Template
{
	/*
	 * Scan a relative front directory (global, local) for template files.
	 */
	public static function getFrontTemplatePath($type, $filename)
	{
		$template = glob(LOCAL_FRONT_PATH . '/templates/' . $type . '/' . $filename . '.tpl');
		if (!empty($template))
			return current($template);

		$template = glob(FRONT_PATH . '/templates/' . $type . '/' . $filename . '.tpl');
		if (!empty($template))
			return current($template);

		return null;
	}

	/*
	 * Scan a relative front directory (global, local) for template files.
	 */
	public static function scanFront($relative)
	{
		$global = self::scan_abs(FRONT_PATH . '/templates/' . $relative);
		$local = self::scan_abs(LOCAL_FRONT_PATH . '/templates/' . $relative);

		return array_merge($global, $local);
	}

	/*
	 * Scan a relative admin directory (global, local) for template files.
	 */
	public static function scanAdmin($relative)
	{
		$global = self::scan_abs(ADMIN_PATH . '/templates/' . $relative);
		$local = self::scan_abs(LOCAL_ADMIN_PATH . '/templates/' . $relative);

		return array_merge($global, $local);
	}

	/*
	 * Scan a directory for template files.
	 */
	public static function scan_abs($dir)
	{
		$translate = Zend_Registry::get('Zend_Translate');
		$files = glob(realpath($dir) . '/*.tpl');
		if (empty($files))
			return array();

		$templates = array();
		foreach ($files as $f)
		{
			$h = fopen($f, 'r');
			while (($line = fgets($h)) !== false)
			{
				$matches = array();
				if (preg_match('/^\s*\*\s+Template:\s+(.*)$/', $line, $matches))
				{
					$templates[basename($f, '.tpl')] = $translate->_($matches[1]);
					fclose($h);
					continue 2;
				}
			}
			fclose($h);
		}

		return $templates;
	}
}
