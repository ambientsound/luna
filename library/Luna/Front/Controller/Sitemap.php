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

class Luna_Front_Controller_Sitemap extends Luna_Front_Controller_Action
{
	public function xmlAction()
	{
		$this->_helper->viewRenderer->setNoRender(true);
		$model = new Model_Pages;
		$xml = new XMLWriter;
		$domain = 'http://' . $_SERVER['SERVER_NAME'];

		$docs = $model->getXmlList(array(
			'published', 'spider_sitemap', 'modified'
		));

		if (!$xml->openMemory())
			throw new Zend_Exception('Cannot allocate memory for sitemap generation');

		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('urlset');
		$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		if (!empty($docs))
		foreach ($docs as $page)
		{
			if (!$page['published'] || !$page['spider_sitemap'])
				continue;

			if ($page['id'] == $this->options->main->frontpage)
				$page['url'] = '/';

			$page['modified'] = date('c', strtotime($page['modified']));

			$xml->startElement('url');
			$xml->writeElement('loc', $domain . $page['url']);
			$xml->writeElement('lastmod', $page['modified']);
			$xml->endElement();
		}

		$xml->fullEndElement();
		$xml->endDocument();
		$document = $xml->flush();

		header('Content-Type: application/xml');

		echo $document;
	}
}
