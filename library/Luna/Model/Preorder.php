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

abstract class Luna_Model_Preorder extends Luna_Db_Table
{
	public function deleteId($id)
	{
		$node = new Luna_Object_Preorder($this, $id);
		if (!$node->load())
			return false;
		
		$diff = $node->rgt - $node->lft + 1;
		$tablename = $this->db->quoteIdentifier($this->_name);
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

	/*
	 * Injects a row and updates modified preorder tree.
	 *
	 * @returns mixed row id on success, false on failure
	 *
	 */
	public function inject($data)
	{
		try
		{
			/* Workaround since Zend is missing something like hasTransaction() */
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

			$node = new Luna_Object_Preorder($this, $data['id']);
			$parent = new Luna_Object_Preorder($this, $data['parent']);

			/* Disable moving entire node trees */
			if ($node->load() && $node->lft + 1 != $node->rgt)
				$parent->load($node->getParentId());

			unset($data['parent']);

			/* Some SQL strings */
			$tablename = $this->db->quoteIdentifier($this->_name);
			$lft = $this->db->quoteIdentifier('lft');
			$rgt = $this->db->quoteIdentifier('rgt');

			$parent->load();

			do
			{
				if (!empty($parent->id))
				{
					if (!empty($node->id))
					{
						if ($parent->id == $node->getParentId())
						{
							/* Parent stays the same and no change to rgt/lft needed, just save. */
							$data['lft'] = $node->lft;
							$data['rgt'] = $node->rgt;
							break;
						}

						/* Old article is not empty, but moved to a different parent. It will be appended to the end, so let's adjust the rest accordingly. */
						$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} - 2 WHERE {$lft} >= {$node->lft}");
						$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} - 2 WHERE {$rgt} >= {$node->lft}");
					}

					$parent->reload();
					$data['lft'] = $parent->rgt;
					$data['rgt'] = $data['lft'] + 1;

					$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} + 2 WHERE {$lft} >= {$data['lft']}");
					$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} + 2 WHERE {$rgt} >= {$data['lft']}");

					break;
				}

				if (!empty($node->id))
				{
					$path = $node->getAncestors();
					if (count($path) == 1)
					{
						/* This is already a bottom node, no need to change lft/rgt. */
						$data['lft'] = $node->lft;
						$data['rgt'] = $node->rgt;

						break;
					}

					/* Old article is not empty, but moved to bottom. It will be appended to the end, so let's adjust the rest accordingly. */
					$this->db->query("UPDATE {$tablename} SET {$lft} = {$lft} - 2 WHERE {$lft} >= {$node->lft}");
					$this->db->query("UPDATE {$tablename} SET {$rgt} = {$rgt} - 2 WHERE {$rgt} >= {$node->lft}");
				}

				/* This node doesn't have a valid lft/rgt, OR it is a new node. Insert it at the very end. */

				$data['lft'] = $this->db->fetchOne($this->select()->setIntegrityCheck(false)->from($this->_name, "MAX({$rgt}) + 1"));
				if (empty($data['lft']))
					$data['lft'] = 1;
				$data['rgt'] = $data['lft'] + 1;
			}
			while (false);

			$id = parent::inject($data);

			if (empty($nocommit))
			{
				if (empty($id))
					$this->db->rollBack();
				else
					$this->db->commit();
			}

			return $id;
		}
		catch (Exception $e)
		{
			if (empty($nocommit))
				$this->db->rollBack();

			throw $e;
		}
	}

	public function getOrderedList(array $fields = array())
	{
		if (array_search('lft', $fields) === false)
			$fields[] = 'lft';
		if (array_search('rgt', $fields) === false)
			$fields[] = 'rgt';

		$select = $this->select()
			->setIntegrityCheck(false)
			->from($this->_name, $fields)
			->joinCross(array('parent' => $this->_name), array('depth' => 'COUNT(parent.lft) - 1'))
			->where($this->_name . '.lft BETWEEN parent.lft AND parent.rgt')
			->order($this->_name . '.lft ASC');

		foreach ($fields as $field)
		{
			$select->group($this->_name . '.' . $field);
		}

		return $this->db->fetchAll($select);
	}

	public function getPathOrderedList($field, $delimiter = '/')
	{
		$rows = $this->getOrderedList(array('id', 'lft', 'rgt', $field));
		$nodes = array();

		foreach ($rows as &$node)
		{
			$nodes[$node['depth']] = $node[$field];
			$nodes = array_slice($nodes, 0, $node['depth'] + 1);
			$node['path'] = $delimiter . join($delimiter, $nodes);
		}
		reset($rows);

		return $rows;
	}

	public function getFlatAssocList($field, $delimiter = '/')
	{
		$list = $this->getPathOrderedList($field, $delimiter);

		$rows = array();
		foreach ($list as $row)
			$rows[$row['id']] = $row['path'];

		return $rows;
	}
}
