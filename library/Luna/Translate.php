<?php


class Luna_Translate extends Zend_Translate
{
	public function __construct($locale)
	{
		$locale = preg_replace('/[^\w\d-_]/', null, $locale);

		$global = APPLICATION_PATH . '/i18n/' . $locale . '.ini';
		$local = LOCAL_PATH . '/i18n/' . $locale . '.ini';

		parent::__construct(array(
			'adapter'	=> 'ini',
			'content'	=> APPLICATION_PATH . '/i18n/' . $locale . '.ini',
			'locale'	=> $locale,
		));

		if (file_exists($local))
			$this->addTranslation($local, $locale);
	}

	public function t($text, $params = null)
	{
		return $this->_($text, $params);
	}
 
	/*
	 * Translate a string to the current language
	 */
	public function _($text, $params = null)
	{
		$text = $this->getAdapter()->_($text);
		$text = $this->formatstrarg($text, $params);

		$text = stripslashes($text);

		return $text;
	}

	/*
	 * Formats a string according to
	 * 'string %1 %2 test', 'test1', 'test2' => 'string test1 test2 test'
	 */
	public function formatstrarg($str)
	{
		$tr = array();
		$p = 0;
	
		for ($i = 1; $i < func_num_args(); $i++)
		{
			$arg = func_get_arg($i);
			
			if (is_array($arg))
			{
				foreach ($arg as $aarg)
				{
					$tr['%' . ++$p] = $aarg;
				}
			}
			else
			{
				$tr['%' . ++$p] = $arg;
			}
		}

		return strtr($str, $tr);
	}
}
