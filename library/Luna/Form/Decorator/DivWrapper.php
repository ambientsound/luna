<?php


class Luna_Form_Decorator_DivWrapper extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$elementName = $this->getElement()->getName();
		$type = $this->getElement()->getType();
		$pos = strrpos($type, '_');
		$type = strtolower(substr($type, $pos + 1));
		
		return '<div id="' . $elementName . '-div" class="full-' . $type . '-element">' . $content . '</div>';
	}
}
