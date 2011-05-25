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

abstract class Luna_Admin_Model_Pages extends Luna_Model_Page
{
	public function deleteId($id)
	{
		$node = new Luna_Object($this, $id);
		if (!$node->load())
			return false;
		
		$diff = $node->rgt - $node->lft + 1;
		$tablename = $this->db->quoteIdentifier('pages');
		$lft = $this->db->quoteIdentifier('lft');
		$rgt = $this->db->quoteIdentifier('rgt');

		$this->db->beginTransaction();

		try
		{
			/* FIXME: we should really do an ACL check to see if we are allowed to delete all the objects. */
			$this->db->query("DELETE FROM {$tablename} WHERE {$lft} >= {$node->lft} AND {$rgt} <= {$node->rgt}");
			$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} - {$diff} WHERE {$lft} >= {$node->lft}");
			$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} - {$diff} WHERE {$rgt} >= {$node->lft}");
		}
		catch (Zend_Db_Exception $e)
		{
			$this->db->rollBack();
			return false;
		}

		if ($this->db->commit())
			return true;

		return false;
	}

	public function getTemplates()
	{
		return Luna_Template::scanFront($this->_name);
	}

	public function getXmlList()
	{
		$select = $this->select()
			->setIntegrityCheck(false)
			->from('pages', array('id', 'lft', 'rgt', 'slug', 'title'))
			->joinCross(array('parent' => 'pages'), array('depth' => 'COUNT(parent.title) - 1'))
			->where('pages.lft BETWEEN parent.lft AND parent.rgt')
			->group('pages.id')
			->group('pages.lft')
			->group('pages.rgt')
			->group('pages.slug')
			->group('pages.title')
			->order('pages.lft ASC');

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

	public function getFormTreeList()
	{
		$pages = $this->getXmlList();

		$ret = array(0 => '/');
		foreach ($pages as $node)
		{
			$ret[$node['id']] = $node['url'];
		}

		return $ret;
	}

	/*
	 * Inserts a page, updates modified tree and optionally injects it into the other table as well.
	 *
	 * @param $data full set of data to be injected across two tables.
	 * @returns mixed row id on success, false on failure
	 *
	 */
	public function inject($data)
	{
		try
		{
			$this->db->beginTransaction();
		}
		catch (Exception $e)
		{
			$nocommit = true;
		}

		$cols = $this->info();
		$cols = $cols['cols'];

		$node = new Luna_Object_Page($this, $data['id']);
		$parent = new Luna_Object_Page($this, $data['parent']);
		$deptable = null;

		/* Disable moving entire node trees */
		if ($node->load() && $node->lft + 1 != $node->rgt)
			$parent->load($node->getParentId());

		unset($data['parent']);

		$nodedata = array();
		$local = array();

		if (!empty($data['nodetype']))
		{
			$nodedata['nodetype'] = $data['nodetype'];
			$deptable = new Luna_Db_Table($data['nodetype']);
		}
		unset($data['nodetype']);

		foreach ($data as $key => $value)
		{
			if (array_search($key, $cols) === false)
				$local[$key] = $value;
			else
				$nodedata[$key] = $value;
		}

		$parent->load();

		/* Some SQL strings */
		$tablename = $this->db->quoteIdentifier('pages');
		$lft = $this->db->quoteIdentifier('lft');
		$rgt = $this->db->quoteIdentifier('rgt');

		do
		{
			if (!empty($parent->id))
			{
				if (!empty($node->id))
				{
					if ($parent->id == $node->getParentId())
					{
						/* Parent stays the same and no change to rgt/lft needed, just save. */
						$nodedata['lft'] = $node->lft;
						$nodedata['rgt'] = $node->rgt;
						break;
					}

					/* Old article is not empty, but moved to a different parent. It will be appended to the end, so let's adjust the rest accordingly. */
					$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} - 2 WHERE {$lft} >= {$node->lft}");
					$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} - 2 WHERE {$rgt} >= {$node->lft}");
				}

				$parent->reload();
				$nodedata['lft'] = $parent->rgt;
				$nodedata['rgt'] = $nodedata['lft'] + 1;

				$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} + 2 WHERE {$lft} >= {$nodedata['lft']}");
				$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} + 2 WHERE {$rgt} >= {$nodedata['lft']}");

				break;
			}

			if (!empty($node->id))
			{
				$path = $node->getAncestors();
				if (count($path) == 1)
				{
					/* This is already a bottom node, no need to change lft/rgt. */
					$nodedata['lft'] = $node->lft;
					$nodedata['rgt'] = $node->rgt;

					break;
				}

				/* Old article is not empty, but moved to bottom. It will be appended to the end, so let's adjust the rest accordingly. */
				$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} - 2 WHERE {$lft} >= {$node->lft}");
				$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} - 2 WHERE {$rgt} >= {$node->lft}");
			}

			/* This node doesn't have a valid lft/rgt, OR it is a new node. Insert it at the very end. */

			$nodedata['lft'] = $this->db->fetchOne($this->select()->setIntegrityCheck(false)->from('pages', "MAX({$rgt}) + 1"));
			if (empty($nodedata['lft']))
				$nodedata['lft'] = 1;
			$nodedata['rgt'] = $nodedata['lft'] + 1;
		}
		while (false);

		$local['id'] = parent::inject($nodedata);
		if ($local['id'] == false)
		{
			if (empty($nocommit))
				$this->db->rollBack();

			return null;
		}

		if ($deptable instanceof Luna_Db_Table)
			$local['id'] = $deptable->inject($local);

		if (empty($nocommit))
		{
			if (empty($local['id']))
				$this->db->rollBack();
			else
				$this->db->commit();
		}

		return $local['id'];
	}
}
