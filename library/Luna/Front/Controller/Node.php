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

class Luna_Front_Controller_Node extends Luna_Front_Controller_Action
{
	protected $node = null;

	/*
	 * Fetches articles from database based on URL information.
	 * This controller is always the route destination if no other static routes match.
	 */
	public function indexAction()
	{
		$model = new Model_Node;
		$uri = $this->_getParam(1);
		$this->node = $model->getNodeFromUrl($uri);

		if (empty($this->node))
			throw new Zend_Exception('Path /' . $uri . ' does not exist in the database.', 404);

		$template = Luna_Template::getFrontTemplatePath($this->node->nodetype, $this->node->template);
		if (!file_exists($template))
			throw new Zend_Exception("Article {$this->article['id']} points to template '{$template}' which does not exist on file system.", 503);

		$this->view->setTemplate($this->node->nodetype . '/' . $this->node->template);

		$baseurl = null;

		if (!empty($this->node->path))
		foreach ($this->node->path as $sub)
		{
			$baseurl .= '/' . $sub['slug'];
			$this->path->add($baseurl, $sub['title']);
		}

		if (!empty($this->node->metadesc))
			$this->setMeta('description', $this->article['metadesc']);

/*
		$slugger = new Luna_Filter_Slug;
		$shortlink = $slugger->filter($uri);
		if (method_exists($this, $shortlink))
		{
			$this->$shortlink();
		}
*/
		$this->view->node = $this->node;
	}





	/*
	 * Custom page actions for URLs that require a bit more than basic functionality.
	 */

	const VINTERKONKURRANSE_ID = 697303;

	public function guidevinterkonkurranse()
	{
		$pos = strrpos($_SERVER['HTTP_REFERER'], '-');
		$referringId = substr($_SERVER['HTTP_REFERER'], $pos + 1);

		$form = new Form_Vinter_Konkurranse(array(
			'method'	=> 'post',
			'action'	=> '/guide/vinter/konkurranse'
		));

		$this->view->status = 'found';

		if ($this->_getParam('komplett') == 1)
		{
			$this->view->status = 'complete';
		}
		elseif ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$model = new Model_Compo;
				$mailer = new Zend_Mail('UTF-8');
				$filter = new Olga_Filter_Html2text;
				$config = Olga_Config::get('site')->mailer;

				$values = array(
					'compo_id'	=> 1,
					'ip'		=> new Zend_Db_Expr($model->getDb()->quoteInto('INET_ATON(?)', $_SERVER['REMOTE_ADDR'])),
					'name'		=> $form->getValue('name'),
					'email'		=> $form->getValue('email')
				);
				$model->inject($values);

				$smarty =& $this->view->getEngine();
				$tpl = $smarty->createTemplate('page/guide/vinter/_konkurransemail.tpl', $smarty);
				$tpl->assign('form', $form->getValues());
				$htmlbody = $tpl->fetch();

				$mailer->setFrom($config->frommail, $config->fromname);
				$mailer->addTo($form->getValue('email'), $form->getValue('name'));
				$mailer->addBcc($config->bccmail);
				$mailer->setSubject($this->translate('mail_vinter_konkurranse_subject'));
				$mailer->setBodyHtml($htmlbody);
				$mailer->setBodyText($filter->filter($htmlbody));

				$mailer->send();

				$this->_redirect('/guide/vinter/konkurranse?komplett=1');
			}
		}
		elseif ($referringId != self::VINTERKONKURRANSE_ID && strpos($_SERVER['HTTP_REFERER'], 'onlineguiden.no/sok') === false)
		{
			$this->view->status = null;
		}

		$this->view->form = $form;
	}

	public function guidevintersjekkliste()
	{
		$slugger = new Olga_Filter_Slug;

		if (($fb = $this->_getParam('fbshare')) == 1)
		{
			$checklist = $this->_getParam('clist');
			if (!empty($checklist))
			{
				$urlsegment = base64_encode(json_encode($checklist));
				return $this->_redirect('http://www.facebook.com/sharer.php?u=' .
					urlencode('http://www.onlineguiden.no/guide/vinter/sjekkliste?list=' . $urlsegment) . '&t=' .
					urlencode($this->translate('guide_vinter_checklist_fbtitle')));
			}
		}
		if ($this->getRequest()->isPost())
		{
			$emailValidator = new Zend_Validate_EmailAddress;

			$email = $this->_getParam('email');
			$optin = $this->_getParam('optin');
			$checklist = $this->_getParam('clist');

			$urlsegment = base64_encode(json_encode($checklist));

			if ($emailValidator->isValid($email))
			{
				$mailer = new Zend_Mail('UTF-8');
				$plaintext = new Olga_Filter_Html2text;
				$config = Olga_Config::get('site')->mailer;

				$hits = array();
				$index = new Olga_Search('main');
				$search = new Olga_Search_Frontend;

				/* Make an item array and perform a search for each entry */
				foreach ($checklist as $item)
				{
					$hits[] = array();
					$h =& $hits[count($hits)-1];
					$h['title'] = $item;
					$h['searchurl'] = 'http://www.onlineguiden.no/sok?q=' . urlencode($item);

					if ($optin)
					{
						$search->clear();
						$search->setDismaxQuery($item);
						$search->addFilterQuery('active', 'true');
						$search->setSort(array('pri asc', 'score desc'));
						$search->setRange(0, 3);
						$index->prep($search->getParams());
						$items = $index->getItems();
						$h['hits'] = $items;
					}
				}

				/* Compose the HTML text for the checklist */
				$smarty =& $this->view->getEngine();
				$tpl = $smarty->createTemplate('layouts/checklist.tpl', $smarty);
				$tpl->assign('checklist', $hits);
				$tpl->assign('urlsegment', $urlsegment);
				$htmlbody = $tpl->fetch();
				unset($tpl);

				$mailer->setFrom($config->frommail, $config->fromname);
				$mailer->setSubject($this->translate('guide_vinter_checklist_subject'));
				$mailer->addTo($email);
				$mailer->setBodyHtml($htmlbody);
				$mailer->setBodyText($plaintext->filter(utf8_decode($htmlbody)));
				$mailer->send();
			}

			return $this->_redirect('/guide/vinter/sjekkliste?list=' . $urlsegment);
		}
		
		$urlsegment = $this->_getParam('list');

		if (!empty($urlsegment))
		{
			$checklist = json_decode(base64_decode($urlsegment));
		}

		if (empty($checklist))
		{
			$checklist = array(
				'Ryggsekk',
				'Sitteunderlag',
				'Ullgenser',
				'Solbriller',
				'Kakao',
				'Appelsin',
				'Turmat',
				'Kvikklunsj',
				'Ski',
			);
		}

		$this->view->checklist = $checklist;
	}
}
