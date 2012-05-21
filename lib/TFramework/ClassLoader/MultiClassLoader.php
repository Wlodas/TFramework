<?php

namespace TFramework\ClassLoader;

use RuntimeException;

/**
 * Klasa ładująca klasy z podanych ścieżek.
 *
 * @package TFramework
 * @subpackage ClassLoader
 * @author Paweł Włodarczyk
 */
class MultiClassLoader
{
	static private $_instance;
	private $_registered = false;
	private $_classes = array();
	private $_loaded_classes = array();
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(self::$_instance == null){
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function getAddedClasses()
	{
		return $this->_classes;
	}
	
	public function addClass($className, $fullPath)
	{
		if(!is_file($fullPath))
			throw new RuntimeException("Podany plik '$fullPath' nie istnieje.");
		
		$this->_classes[$className] = $fullPath;
		
		return $this;
	}
	
	public function addClasses(array $classes)
	{
		foreach($classes as $className => $fullPath){
			if(!is_file($fullPath))
				throw new RuntimeException("Podany plik '$fullPath' nie istnieje.");
			
			$this->_classes[$className] = $fullPath;
		}
		
		return $this;
	}
	
	public function isRegistered()
	{
		return $this->_registered;
	}
	
	public function register($prepend = false)
	{
		if(!$this->_registered) {
			spl_autoload_register(array($this, 'autoload'), true, $prepend);
			$this->_registered = true;
		}
	}
	
	public function getLoadedClasses()
	{
		return $this->_loaded_classes;
	}
	
	public function autoload($class_name)
	{
		if(!class_exists($class_name) && isset($this->_classes[$class_name])) {
			require_once $this->_classes[$class_name];
			if(class_exists($class_name)) {
				$this->_loaded_classes[] = $class_name;
			}
        }
	}
}