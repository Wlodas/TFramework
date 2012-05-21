<?php

namespace TFramework\Form;

use ArrayAccess;
use Iterator;
use TFramework\Widget\Widget;
use Exception;

abstract class Form implements ArrayAccess, Iterator
{
	protected $name_template = '%s';
	protected $widgets = array();
	protected $object;
	
	public function __construct($object = null)
	{
		if(!is_null($object)) $this->bindObject($object);
		
		$this->setNameTemplate($this->setupNameTemplate());
		$this->setup();
		
		if($this->hasObject()) $this->bindObjectValues($object);
	}
	
	final protected function bindObject($object)
	{
		if(is_object($object)) {
			$this->object = $object;
		} else {
			throw new Exception('Podany argument nie jest typu obiektowego.');
		}
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
	
	public function setWidget($name, Widget $widget)
	{
		$widget->setNameTemplate($this->getNameTemplate());
		$widget->setName($name);
		if($label = $widget->getLabel()) {
			
		}
		$widget->setLabel($widget->getLabel() == '' ? $name : $widget->getLabel());
		$this->widgets[$name] = $widget;
	}
	
	public function bind($element)
	{
		if(is_array($element)) {
			$this->bindArrayValues($element);
		} elseif(is_object($element)) {
			$this->bindObjectValues($element);
		} else {
			throw new Exception('Przypisany element musi być typu tablicowego lub obiektowego.');
		}
	}
	
	protected function bindArrayValues(array $array)
	{
		foreach($this->widgets as $name => $widget) {
			if(isset($array[$name])) {
				$widget->bind($array[$name]);
			}
		}
	}
	
	protected function bindObjectValues($object)
	{
		$class = get_class($object);
		
		foreach($this->widgets as $name => $widget) {
			if(property_exists($class, $name)) {
				$widget->bind($object->$name);
			}
		}
	}
	
	public function getValues()
	{
		$values = array();
		
		foreach($this->widgets as $name => $widget) {
			$values[$name] = $widget->getValue();
		}
		
		return $values;
	}
	
	public function bindValuesToObject()
	{
		if($this->hasObject()) {
			$object = $this->getObject();
			$class = get_class($object);
			
			foreach($this->widgets as $name => $widget) {
				if(property_exists($class, $name)) {
					$object->$name = $widget->getValue();
				} else {
					throw new Exception("Pole '$name' nie istnieje w klasie '$class'");
				}
			}
		} else {
			throw new Exception('Formularz nie posiada przypisanego mu obiektu.');
		}
	}
	
	public function hasObject()
	{
		return $this->object ? true : false;
	}
	
	public function getObject()
	{
		return $this->object;
	}
	
	abstract protected function setupNameTemplate();
	abstract protected function setup();
	
	public function offsetSet($name, $widget)
	{
		if (!is_null($name)) {
			$this->setWidget($name, $widget);
		} else {
			throw new Exception("Podany widżet musi posiadać przypisaną nazwę.");
		}
	}
	
	public function offsetExists($offset)
	{
		return isset($this->widgets[$offset]);
	}
	
	public function offsetUnset($offset)
	{
		unset($this->widgets[$offset]);
	}
	
	public function offsetGet($offset)
	{
		return isset($this->widgets[$offset]) ? $this->widgets[$offset] : null;
	}
	
	public function rewind()
	{
		reset($this->widgets);
	}
	
	public function current()
	{
		return current($this->widgets);
	}
	
	public function key()
	{
		return key($this->widgets);
	}
	
	public function next()
	{
		next($this->widgets);
	}
	
	public function valid()
	{
		return isset($this->widgets[$this->key()]);
	}
}