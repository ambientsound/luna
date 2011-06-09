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

class Luna_Model_Menu extends Luna_Db_Table
{
	protected $_name = 'menus';

	protected $_objectName = 'Menu';

	public function inject($values)
	{
		if (empty($values['page_id']))
			$values['page_id'] = new Zend_Db_Expr('NULL');

		$menuitems = array();
		if (!empty($values['menuitem']))
		{
			foreach ($values['menuitem'] as $item)
				$menuitems[] = (array)json_decode($item);
		}
		unset($values['menuitem']);

		$this->db->beginTransaction();
		
		if (($id = parent::inject($values)) != false)
		{
			if ($this->connectMenuItems($id, $menuitems))
				return $this->db->commit();
		}

		$this->db->rollBack();
		return false;
	}

	public function getStaticMenuItems($menu_id)
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('menuitems', array('id', 'lft', 'rgt', 'page_id', 'title', 'url'))
			->where($this->db->quoteInto('menuitems.menu_id = ?', $menu_id))
			->order('menuitems.lft ASC');

		return $this->db->fetchAll($select);
	}

	protected function connectMenuItems($menu_id, $items)
	{
		if (empty($menu_id))
			return false;

	 	$model = new Luna_Db_Table('menuitems');
		$model->delete($this->db->quoteIdentifier('menu_id') . ' = ' . $this->db->quote($menu_id));

		if (empty($items) || !is_array($items))
			return true;

		$iter = 0;
		$page = new Luna_Object_Page(new Model_Pages, null);

		foreach ($items as $key => $item)
		{
			$insert = array(
				'menu_id'	=> $menu_id,
				'title'		=> $item['title'],
				'page_id'	=> (empty($item['page_id']) ? new Zend_Db_Expr('NULL') : $item['page_id']),
				'url'		=> (!empty($item['page_id']) ? new Zend_Db_Expr('NULL') : $item['url']),
				'lft'		=> ++$iter,
				'rgt'		=> ++$iter
			);

			if (empty($item['page_id']) && empty($item['url']))
				$insert['url'] = '/';

			if (!empty($item['page_id']))
			{
				if ($item['url'] == $item['title'])
				{
					if ($page->load($item['page_id']))
					{
						$insert['title'] = $page->title;
					}
				}
			}

			if (!$model->inject($insert))
				return false;
		}

		return true;
	}
}
