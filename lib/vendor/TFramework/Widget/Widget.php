<?php

namespace TFramework\Widget;

use Exception;

abstract class Widget
{
	protected $name;
	protected $label;
	protected $options;
	protected $required_options = array();
	protected $attributes;
	protected $value;
	protected $is_visible = true;
	
	public function __construct(array $options = array(), array $attributes = array())
	{
		$this->options = $options;
		$this->attributes = $attributes;
		
		$this->init();
		$this->validate();
		$this->setup();
	}
	
	protected function init()
	{
		if(isset($this->options['label'])) {
			$this->label = $this->options['label'];
		}
	}
	
	protected function validate()
	{
		foreach($this->required_options as $option) {
			if(!array_key_exists($option, $this->options)) {
				throw new Exception("Nie zdefiniowano wymaganego elementu '$option'");
			}
		}
	}
	
	protected function setup(){}
	
	public function isVisible()
	{
		return $this->is_visible;
	}
	
	final public function getName()
	{
		return $this->name;
	}
	
	final public function setName($name)
	{
		$this->name = $name;
	}
	
	final public function getNameTemplate()
	{
		return $this->name_template;
	}
	
	final public function setNameTemplate($name_template)
	{
		if(false == strpos($name_template, '%s')) {
			throw new Exception('Nie znaleziono ciągu znaków "%s" w podanym szablonie.');
		}
		
		$this->name_template = $name_template;
	}
	
	final public function getLabel()
	{
		return $this->label;
	}
	
	final public function setLabel($label)
	{
		$this->label = $label;
	}
	
	public function bind($value)
	{
		$this->value = $value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function getFieldName()
	{
		return str_replace('%s', $this->getName(), $this->getNameTemplate());
	}
	
	public function getId()
	{
		return str_replace(array('[', ']'), array('__', ''), $this->getFieldName());
	}
	
	public function renderLabel()
	{
		printf('<label for="%s">%s</label>', $this->getId(), $this->getLabel());
	}
	
	abstract public function render();
}