<?php

namespace TFramework\ClassLoader;

use RuntimeException;

/**
 * Klasa ładująca wybrany plik zależności klas, jeżeli wymagana jest klasa o podanym prefiksie.
 *
 * @package TFramework
 * @subpackage ClassLoader
 * @author Paweł Włodarczyk
 */
class PrefixDependencyClassLoader
{
	private static $_instances = array();
	private static $_registered_loaders = array();
	private $_loaded_classes = array();
	private $_loaded = false;
	private $_registered = false;
	private $_prefix;
	private $_path;
	
	public function __construct($prefix, $path)
	{
		if(!is_file($path))
			throw new RuntimeException("Podany plik '$path' nie istnieje.");
		
		$this->_prefix = $prefix;
		$this->_path = $path;
		self::$_instances[] = $this;
	}
	
	public function register($prepend = false)
	{
		if(!$this->_registered) {
			spl_autoload_register(array($this, 'autoload'), true, $prepend);
			$this->_registered = true;
			self::$_registered_loaders[] = $this;
		}
	}
	
	public static function getInstances()
	{
		return self::$_instances;
	}
	
	public static function getRegisteredLoaders()
	{
		return self::$_registered_loaders;
	}
	
	public function isRegistered()
	{
		return $this->_registered;
	}
	
	public function getLoadedClasses()
	{
		return $this->_loaded_classes;
	}
	
	public function autoload($class_name)
	{
		if(!$this->_loaded && !class_exists($class_name) && substr($class_name, 0, strlen($this->_prefix)) == $this->_prefix) {
			$before = get_declared_classes();
				
			require_once $this->_path;
				
			$this->_loaded_classes = array_diff(get_declared_classes(), $before);
			$this->_loaded = true;
		}
	}
}