<?php

/*
 * Smarty translator
 */
function smarty_block_t($params, $text, &$smarty)
{
	if (empty($text))
		return;

	echo Zend_Registry::get('Zend_Translate')->t($text, $params);
}
