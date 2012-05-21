<?php

namespace TFramework\Core;
	
/**
 * Klasa sterująca działaniem aplikacji.
 *
 * @package TFramework
 * @subpackage Core
 * @author Paweł Włodarczyk
 */
class Context
{
	private static $_instance;
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(self::$_instance == null){
			self::$_instance = new self();
		}
	
		return self::$_instance;
	}
	
	/**
	 * @return User
	 */
	public function getUser()
	{
		return new User();
	}
}