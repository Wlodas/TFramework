<?php

namespace TFramework\ClassLoader;

use RuntimeException;

/**
 * Klasa ładująca klasy w określonej przestrzeni nazw.
 *
 * @package TFramework
 * @subpackage ClassLoader
 * @author Paweł Włodarczyk
 */
class NamespaceClassLoader
{
	private static $_instances = array();
	private static $_registered_loaders = array();
	private $_loaded_classes = array();
	private $_registered = false;
	private $_prefix;
	private $_path;
	private $_extension = 'php';
	
	public function __construct($prefix, $path)
	{
		if(!is_dir($path))
			throw new RuntimeException("Podany katalog '$path' nie istnieje.");
		
		$this->_prefix = $prefix;
		$this->_path = $path;
		self::$_instances[] = $this;
	}
	
	public function setExtension($extension)
	{
		$this->_extension = $extension;
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
		if(!class_exists($class_name) && substr($class_name, 0, strlen($this->_prefix)) == $this->_prefix) {
			if (false !== $pos = strrpos($class_name, '\\')) {
				$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class_name, 0, $pos)) . DIRECTORY_SEPARATOR;
				$className = substr($class_name, $pos + 1);
			} else {
				$classPath = null;
				$className = $class_name;
			}
			
			$path = $this->_path . DIRECTORY_SEPARATOR . $classPath . $className . '.' . $this->_extension;
			
			if(is_file($path)) {
				require_once $path;
				if(class_exists($class_name)) {
					$this->_loaded_classes[] = $class_name;
				}
			}
		}
	}
}