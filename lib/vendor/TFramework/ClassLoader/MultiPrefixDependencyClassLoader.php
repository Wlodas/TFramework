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
class MultiPrefixDependencyClassLoader
{
	static private $_instance;
	private $_registered = false;
	private $_prefixes = array();
	private $_loaded_classes = array();
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(self::$_instance == null){
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function getAddedPrefixes()
	{
		return $this->_prefixes;
	}
	
	public function addPrefix($prefix, $fullPath)
	{
		if(!is_file($fullPath))
			throw new RuntimeException("Podany plik '$fullPath' nie istnieje.");
		
		$this->_prefixes[$prefix] = $fullPath;
		
		return $this;
	}
	
	public function addPrefixes(array $prefixes)
	{
		foreach($prefixes as $prefix => $fullPath){
			if(!is_file($fullPath))
				throw new RuntimeException("Podany plik '$fullPath' nie istnieje.");
			
			$this->_prefixes[$prefix] = $fullPath;
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
		if(!class_exists($class_name)) {
			foreach($this->_prefixes as $prefix => $path) {
				if(substr($class_name, 0, strlen($prefix)) == $prefix) {
					$before = get_declared_classes();
					
					require_once $path;
					
					$this->_loaded_classes = array_merge($this->_loaded_classes, array_diff(get_declared_classes(), $before));
				}
			}
        }
	}
}