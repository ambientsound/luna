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

		if (!empty($data['nodetype']) && $data['nodetype'] != $this->_name)
		{
			$nodedata['nodetype'] = $data['nodetype'];
			$deptable = 'Model_Page_' . strtoupper($data['nodetype'][0]) . strtolower(substr($data['nodetype'], 1));
			if (class_exists($deptable))
				$deptable = new $deptable;
			else
				$deptable = new Luna_Db_Table($data['nodetype']);
		}
		else
		{
			$nodedata['nodetype'] = $this->_name;
		}
		$nodedata['parent'] = $data['parent'];
		unset($data['parent']);
		unset($data['nodetype']);

		foreach ($data as $key => $value)
		{
			if (array_search($key, $cols) !== false)
				$nodedata[$key] = $value;
			else
				$local[$key] = $value;
		}

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
