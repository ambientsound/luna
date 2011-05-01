<?php


class Luna_Form_Decorator_CommentList extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$list = $this->getOption('list');
		$smarty = Bootstrap::newSmarty();
		$smarty->commentList = $list;
		return $content . $smarty->render('/_commentlist.tpl');
	}
}
