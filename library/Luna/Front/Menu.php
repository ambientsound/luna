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

class Luna_Front_Menu extends Luna_Object
{
	public function __construct()
	{
		parent::__construct(new Model_Menus, null);
	}

	public function set($row)
	{
		if (!parent::set($row))
			return false;

		$this->loadChildren();
	}

	public function loadChildren()
	{
		if (!$this->load())
			return false;

		/* Dynamic mode fetches all children of the referenced page. */
		if ($this->_data['mode'] == 'dynamic')
		{
			if (empty($this->_data['page_id']))
				return false;

			
			$parent = new Luna_Object_Page(new Model_Pages, $this->_data['page_id']);
			$ancestors = $parent->getAncestors();
			$descendants = $parent->getDescendants();

			$base = '/';
			array_pop($ancestors);
			if (!empty($ancestors))
			{
				foreach ($ancestors as $a)
					$base .= $a['slug'] . '/';
			}
			$this->_data = $this->buildMenu($base, 0, null, $this->_data['structure'], $descendants);
		}
		/* Static mode works with menu items. */
		elseif ($this->_data['mode'] == 'static')
		{
			throw new Zend_Exception('Not implemented.');
		}
	}

	public function buildMenu($base, $level, $rgt, $mode, &$descendants)
	{
		++$level;
		$ret = array();

		while (!empty($descendants) && ($descendants[0]['lft'] < $rgt || $rgt == null) && ($page = array_shift($descendants)) != null)
		{
			$page['url'] = $base . $page['slug'];
			$page['level'] = $level;

			/* First/root node */
			if ($level == 1 && empty($ret) && $rgt == null)
			{
				$ret = $this->buildMenu($page['url'] . '/', --$level, $page['rgt'], $mode, $descendants);
				return $ret;
			}

			$ret[] = $page;

			/* Leaf node */
			if ($page['lft'] + 1 == $page['rgt'])
				continue;

			$children = $this->buildMenu($page['url'] . '/', $level, $page['rgt'], $mode, $descendants);
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
