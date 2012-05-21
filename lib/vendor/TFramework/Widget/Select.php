<?php

namespace TFramework\Widget;

use Exception;

class Select extends Widget
{
	protected $required_options = array('choices');
	
	protected $empty = true;
	protected $empty_label = '';
	
	protected function validate()
	{
		parent::validate();
		if(!is_array($this->options['choices'])) {
			throw new Exception("Opcja 'options' musi być typu tablicowego");
		}
	}
	
	protected function setup()
	{
		if(isset($this->options['empty'])) {
			$this->empty = $this->options['empty'] ? true : false;
		}
		if(isset($this->options['empty_label'])) {
			$this->empty_label = $this->options['empty_label'];
		}
		
		if($this->empty) {
			$this->choices[''] = sprintf('<option value="">%s</option>', htmlentities($this->empty_label, null, 'UTF-8'));
		}
		
		foreach($this->options['choices'] as $label => $value) {
			$this->choices[$value] = sprintf('<option value="%s">%s</option>', str_replace('"', '&quot;', $value), htmlentities($label, null, 'UTF-8'));
		}
	}
	
	public function render()
	{
		printf('<select id="%s" name="%s">', $this->getId(), $this->getFieldName());
		
		if($this->empty) {
			printf('<option value="">%s</option>', htmlentities($this->empty_label, null, 'UTF-8'));
		}
		
		$binded_value = empty($this->value) ? '' : $this->value;
		
		foreach($this->options['choices'] as $label => $value) {
			if($binded_value == $value) {
				printf('<option selected="selected" value="%s">%s</option>', str_replace('"', '&quot;', $value), htmlentities($label, null, 'UTF-8'));
			} else {
				printf('<option value="%s">%s</option>', str_replace('"', '&quot;', $value), htmlentities($label, null, 'UTF-8'));
			}
		}
		
		echo '</select>';
	}
}