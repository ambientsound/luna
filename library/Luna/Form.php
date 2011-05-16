<?php


/*
 * Extension of Zend_Form, making 'label' field optional to set. Defaults to lowercased class name + field name.
 */
class Luna_Form extends Zend_Form
{
	private $_prefixStr = null;

	public function init()
	{
		$this->setPrefix(strtolower(get_class($this)));
		$this->addPrefixPath('Luna_Form_Decorator', APPLICATION_PATH . '/../library/Luna/Form/Decorator', 'decorator');
		$this->addPrefixPath('Luna_Form_Element', APPLICATION_PATH . '/../library/Luna/Form/Element', 'element');

		$this->setDisableLoadDefaultDecorators(true);
		$this->setAttrib('id', $this->_prefixStr);
		$this->addDecorator('FormElements')->addDecorator('Form');

		return parent::init();
	}

	public function setRequest(Zend_Controller_Request_Abstract $request)
	{
		$base = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setAction($base . '/' . $request->getControllerName() . '/' . $request->getActionName());
	}

	public function removeElements($elements)
	{
		if (empty($elements) || !is_array($elements))
			return;

		foreach ($elements as $e)
		{
			$this->removeElement($e);
		}
	}

	public function disableAll()
	{
		foreach ($this->_elements as &$e)
		{
			if ($e->getName() != 'submit' && $e->getName() != 'id')
				$e->setIgnore(true)->setAttrib('readonly', true)->setAttrib('disabled', true);
		}
	}

	/*
	 * Automagically create form from Zend_Db_Table
	 */
	public function autoGen($model)
	{
		if (!is_a($model, 'Zend_Db_Table_Abstract'))
		{
			throw new Zend_Exception('Luna_Form::autoGen requires parameter 1 to be inherited from Zend_Db_Table_Abstract.');
		}
		$this->clearElements();

		$meta = $model->info();
		$meta = $meta['metadata'];

		foreach ($meta as $field)
		{
			$e = $this->getElementFromMeta($field);
			if (!empty($e))
			{
				$this->addElement($e);
			}
		}

		$this->addElement('Submit', 'submit');
		$this->resetDecorators();
	}

	private function getElementFromMeta($field)
	{
		$info = array();
		$type = null;

		switch($field['COLUMN_NAME'])
		{
			case 'created':
			case 'createdby':
			case 'modified':
			case 'modifiedby':
				return null;
			case 'id':
				$type = 'Hidden';
				break;
			case 'password':
				$type = 'Password';
				break;
			default:
				break;
		}

		if ($type == null)
		{
			if (!empty($this->_autoTypes[$field['COLUMN_NAME']]))
			{
				$type = $this->_autoTypes[$field['COLUMN_NAME']];
			}
			else
			switch($field['DATA_TYPE'])
			{
				default:
					if (substr($field['DATA_TYPE'], 0, 4) == 'enum')
					{
						$type = 'Select';
						break;
					}
				case 'int':
				case 'varchar':
				case 'datetime':
					$type = 'Text';
					break;
			}
		}

		if (file_exists(APPLICATION_PATH . '/../library/Luna/Form/Element/' . $type . '.php'))
			$type = "Luna_Form_Element_{$type}";
		else
			$type = "Zend_Form_Element_{$type}";
		$e = new $type($field['COLUMN_NAME']);

		if (substr($field['DATA_TYPE'], 0, 4) == 'enum')
		{
			$opts = explode(',', trim(substr($field['DATA_TYPE'], 4), '()'));
			foreach ($opts as &$o)
			{
				$o = trim($o, "'");
				$newopts[$o] = $this->_prefixStr . '_' . $e->getName() . '_' . $o;
			}
			$e->addMultiOptions($newopts);
		}

		$e->addPrefixPath('Luna', APPLICATION_PATH . '/../library/Luna');

		return $e;
	}

	public function resetDecorators()
	{
		foreach ($this->_elements as &$e)
		{
			switch($e->getType())
			{
				case 'Zend_Form_Element_Hidden':
					$e->setDecorators(array('ViewHelper'));
					break;
				case 'Zend_Form_Element_File':
					$e->setDecorators(array('File', 'ElementWrapper', 'Errors', 'Description', 'Label', 'DivWrapper'));
					break;
				case 'Zend_Form_Element_Submit':
					$e->setDecorators(array('ViewHelper', 'ElementWrapper', 'Errors', 'Description', 'DivWrapper'));
					break;
				default:
					$e->setDecorators(array('ViewHelper', 'ElementWrapper', 'Errors', 'Description', 'Label', 'DivWrapper'));
			}
		}

		$this->setDisplayGroupDecorators(array(
			'FormElements',
			'Fieldset',
		));
	}

	public function setPrefix($str)
	{
		$this->_prefixStr = $str;
	}

	public function render()
	{
		if (!empty($this->_prefixStr))
		{
			foreach ($this->_elements as &$e)
			{
				$label = $e->getLabel();
				if (!empty($label) && $label != $e->getName())
					continue;
				$e->setLabel($this->_prefixStr . '_' . $e->getName());
			}

			foreach ($this->_displayGroups as &$e)
			{
				$legend = $e->getLegend();
				if (!empty($legend) && $legend != $e->getName())
					continue;
				$e->setLegend($this->_prefixStr . '_' . $e->getName());
			}
		}

		return parent::render();
	}
}
