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

class Luna_Object_Menu extends Luna_Object
{
	private $_options = null;

	public function set($row)
	{
		if (!parent::set($row))
			return false;

		$this->loadChildren();
	}

	/*
	 * FIXME: this function does a lot of database lookups and might be a performance hit.
	 * Dynamic menus will get slow if the site has a lot of pages.
	 * Static menus slow down if they have a lot of menu items.
	 * DO SOME CACHING!
	 */
	public function loadChildren()
	{
		if (!$this->load())
			return false;

		$this->_options = new Model_Options;

		/* Dynamic mode fetches all children of the referenced page. */
		if ($this->_data['mode'] == 'dynamic')
		{
			$base = '/';

			if (!empty($this->_data['page_id']))
			{
				$parent = new Luna_Object_Page(new Model_Pages, $this->_data['page_id']);
				$ancestors = $parent->getAncestors();
				$descendants = $parent->getDescendants();

				array_pop($ancestors);
				if (!empty($ancestors))
				{
					foreach ($ancestors as $a)
						$base .= $a['slug'] . '/';
				}

				$root = array_shift($descendants);
				$base .= $root['slug'] . '/';
			}
			else
			{
				$model = new Model_Pages;
				$descendants = $model->getAll();
				$root = array(
					'rgt'	=> 999999
				);
			}

			$this->_data['children'] = $this->buildDynamicMenu($base, 0, $root['rgt'], $this->_data['structure'], $descendants);
		}
		/* Static mode works with menu items. */
		elseif ($this->_data['mode'] == 'static')
		{
			$page = new Luna_Object_Page(new Model_Pages, null);
			$this->_data['children'] = $this->_model->getStaticMenuItems($this->_data['id']);

			foreach ($this->_data['children'] as $key => &$item)
			{
				if (!empty($item['page_id']))
				{
					if (!$page->load($item['page_id']) && empty($item['url']))
					{
						unset($this->_data['children'][$key]);
						continue;
					}

					if ($item['page_id'] != $this->_options->main->frontpage)
						$item['url'] = $page->getCanonicalUrl();
					else
						$item['url'] = '/';
				}
			}
			reset($this->_data['children']);
		}

		return true;
	}

	public function buildDynamicMenu($base, $level, $rgt, $mode, &$descendants)
	{
		++$level;
		$ret = array();

		while (!empty($descendants) && ($descendants[0]['lft'] < $rgt || $rgt == null) && ($page = array_shift($descendants)) != null)
		{
			$url = $base . $page['slug'];
			if ($page['id'] != $this->_options->main->frontpage)
				$page['url'] = $url;
			else
				$page['url'] = '/';

			$page['level'] = $level;
			$ret[] = $page;

			/* Leaf node */
			if ($page['lft'] + 1 == $page['rgt'])
				continue;

			$children = $this->buildDynamicMenu($url . '/', $level, $page['rgt'], $mode, $descendants);
			if (empty($children))
				continue;

			if ($mode == 'tree')
				$ret[count($ret)-1]['children'] = $children;
			else
				$ret = array_merge($ret, $children);
		}

		return $ret;
	}
}
