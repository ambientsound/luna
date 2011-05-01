<?php


class Luna_Form_Decorator_ElementWrapper extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$type = $this->getElement()->getType();
		$pos = strrpos($type, '_');
		$type = strtolower(substr($type, $pos + 1));
		return '<div id="' . $this->getElement()->getName() . '-element" class="element ' . $type . '-element">' . $content . '</div>';
	}
}
