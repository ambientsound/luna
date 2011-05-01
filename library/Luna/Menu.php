<?php

class Luna_Menu
{
	protected $_menu;

	public function add($controller, $action, $params, $title = null, $uri = null)
	{
		if (empty($action))
		{
			$action = "index";
		}

		if (empty($uri))
		{
			$uri = "/{$controller}";
			if ($action != 'index' || !empty($params))
			{
				$uri .= "/{$action}";
			}
			if (!empty($params))
			{
				foreach ($params as $key => $param)
				{
					$uri .= "/{$key}/{$param}";
				}
			}
		}

		if (empty($title))
		{
			$title = Zend_Registry::get('Zend_Translate')->translate("{$controller}_menu_{$action}");
		}

		$this->_menu[] = array(
			'controller'	=> $controller,
			'action'	=> $action,
			'params'	=> $params,
			'title'		=> $title,
			'uri'		=> $uri
		);
	}

	public function getMenu()
	{
		$acl = Zend_Registry::get('acl');
		$menu = array();

		if (!empty($this->_menu))
		foreach ($this->_menu as $item)
		{
			if ($acl->can($item['controller'] . '.' . $item['action']))
			{
				$menu[] = $item;
			}
		}

		return $menu;
	}
}
