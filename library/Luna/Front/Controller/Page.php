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

class Luna_Front_Controller_Page extends Luna_Front_Controller_Action
{
	protected $page = null;

	/*
	 * Fetches articles from database based on URL information.
	 * This controller is always the route destination if no other static routes match.
	 */
	public function indexAction()
	{
		$model = new Model_Pages;
		$uri = $this->_getParam(1);
		$this->page = $model->getFromUrl($uri);

		if (empty($this->page))
			throw new Zend_Exception('Path /' . $uri . ' does not exist in the database.', 404);

		if (empty($this->page->nodetype))
			$this->page->nodetype = 'pages';

		$template = Luna_Template::getFrontTemplatePath($this->page->nodetype, $this->page->template);
		if (!file_exists($template))
			throw new Zend_Exception("Page {$this->article['id']} points to template '{$template}' which does not exist on file system.", 503);

		$this->view->setTemplate($this->page->nodetype . '/' . $this->page->template);

		$baseurl = null;

		if (!empty($this->page->path))
		foreach ($this->page->path as $sub)
		{
			$baseurl .= '/' . $sub['slug'];
			$this->path->add($baseurl, $sub['title']);
		}

		if (!empty($this->page->metadesc))
			$this->setMeta('description', $this->article['metadesc']);

		$robots = $this->page->spider_index ? 'index' : 'noindex';
		$robots .= ', ' . ($this->page->spider_follow ? 'follow' : 'nofollow');
		$this->setMeta('robots', $robots);

		$this->view->page = $this->page;
	}
}
