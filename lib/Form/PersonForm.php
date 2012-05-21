<?php

namespace Form;

use TFramework\Form\Form;
use TFramework\Widget\InputHidden;
use TFramework\Widget\InputText;
use TFramework\Widget\Select;

class PersonForm extends Form
{
	protected function setupNameTemplate()
	{
		return 'person[%s]';
	}
	
	protected function setup()
	{
		$this['id'] = new InputHidden();
		$this['name'] = new InputText(array('label' => 'Imie'));
		$this['operator_id'] = new Select(array(
			'choices' => array(1 => 1, 2 => 2, 3 => 3),
			'label' => 'Operator'
		));
	}
}