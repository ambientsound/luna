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

class Luna_Model_Page extends Luna_Model_Preorder
{
	protected $_objectName = 'Page';

	public function getFromUrl($url)
	{
		$url = explode('/', trim($url, '/'));
		if (empty($url))
			return null;

		$quoteurl = array();
		foreach ($url as $u)
			$quoteurl[] = $this->db->quote($u);

		$select = $this->select()
			->from('pages', array('id', 'lft', 'rgt', 'slug', 'title'))
			->where('slug IN (' . join(',', $quoteurl) . ')')
			->order('lft ASC');

		$nodes = $this->db->fetchAll($select);

		if (empty($nodes))
			return null;

		$lft = 0;
		$rgt = 9999999;
		$build = array();
		foreach ($nodes as $a)
		{
			if (($a['lft'] > $lft && $a['rgt'] < $rgt) && ($a['slug'] == $url[count($build)]))
			{
				$build[] = $a;
				$lft = $a['lft'];
				$rgt = $a['rgt'];
			}
			else
			{
				if ($a['slug'] == $url[0])
				{
					$build = array($a);
					$lft = $a['lft'];
					$rgt = $a['rgt'];
				}
				else
				{
					$build = array();
					$lft = 0;
					$rgt = 9999999;
				}
			}

			if (count($build) == count($url))
				break;
		}

		if (count($build) != count($url))
			return null;
		
		$node = $this->_get($a['id']);
		$node['path'] = $build;
		$url = '/' . join('/', $url);

		$modelname = 'Model_' . strtoupper($node['nodetype'][0]) . strtolower(substr($node['nodetype'], 1));
		if (@class_exists($modelname))
			$node = Luna_Object::factory(new $modelname, $node);
		else
			$node = new Luna_Object_Page($this, $node);

		/* Part of the path, but not all of it, was corrent. We check with the canonical url here. */
		if ($node->url != $url)
			return null;

		$node->loadRelation();

		return $node;
	}

	public function getFromId($id)
	{
		$node = $this->_get($id);

		$modelname = 'Model_' . strtoupper($node['nodetype'][0]) . strtolower(substr($node['nodetype'], 1));
		if (@class_exists($modelname))
			$node = Luna_Object::factory(new $modelname, $node);
		else
			$node = new Luna_Object_Page($this, $node);

		$node->loadRelation();

		return $node;
	}

	public function getXmlList($fields = null)
	{
		$f = array('id', 'lft', 'rgt', 'slug', 'title');
		if (!empty($fields))
			$f = array_merge($f, $fields);

		$select = $this->select()
			->setIntegrityCheck(false)
			->from('pages', $f)
			->joinCross(array('parent' => 'pages'), array('depth' => 'COUNT(parent.title) - 1'))
			->where('pages.lft BETWEEN parent.lft AND parent.rgt')
			->order('pages.lft ASC');

		foreach ($f as $field)
			$select->group('pages.' . $field);

		$allPages = $this->db->fetchAll($select);
		$urls = array();

		foreach ($allPages as &$node)
		{
			$urls[$node['depth']] = $node['slug'];
			$urls = array_slice($urls, 0, $node['depth'] + 1);
			$node['url'] = '/' . join('/', $urls);
		}
		reset($allPages);

		return $allPages;
	}

	public function getAll()
	{
		$select = $this->select()
			->from('pages', array('id', 'lft', 'rgt', 'published', 'slug', 'title'))
			->order('lft ASC');

		return $this->db->fetchAll($select);
	}
}
