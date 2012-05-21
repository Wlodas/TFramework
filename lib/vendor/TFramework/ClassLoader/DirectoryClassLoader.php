<?php

namespace TFramework\ClassLoader;

use RuntimeException;

/**
 * Klasa ładująca wszystkie klasy znajdujące się w danym katalogu.
 *
 * @package TFramework
 * @subpackage ClassLoader
 * @author Paweł Włodarczyk
 */
class DirectoryClassLoader
{
	private static $_instances = array();
	private $_loaded_classes = array();
	private $_loaded = false;
	private $_path;
	private $_recursive;
	private $_extension = 'php';
	
	public function __construct($path, $recursive = false)
	{
		if(!is_dir($path))
			throw new RuntimeException("Podany katalog '$path' nie istnieje.");
		
		$this->_path = $path;
		$this->_recursive = $recursive;
		self::$_instances[] = $this;
	}
	
	public static function getInstances()
	{
		return self::$_instances;
	}
	
	public function setExtension($extension)
	{
		$this->_extension = $extension;
	}
	
	public function getLoadedClasses()
	{
		return $this->_loaded_classes;
	}
	
	public function load()
	{
		if(!$this->_loaded) {
			$before = get_declared_classes();
			
			$this->_loadDirectory($this->_path);
			
			$this->_loaded_classes = array_diff(get_declared_classes(), $before);
			$this->_loaded = true;
		}
	}
	
	private function _loadDirectory($dir_path)
	{
		if ($handle = opendir($dir_path)) {
			while (false !== ($entry = readdir($handle))) {
				$path = $dir_path.DIRECTORY_SEPARATOR.$entry;
				if($this->_recursive && $entry != '.' && $entry != '..' && is_dir($path)) {
					$this->_loadDirectory($path);
				} elseif(is_file($path) && substr($entry, -strlen($this->_extension) - 1) == '.'.$this->_extension) {
					require_once $path;
				}
			}
		    closedir($handle);
		}
	}
}