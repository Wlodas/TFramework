<?php

namespace TFramework\ORM;

use ReflectionClass;
	
/**
 * Klasa zarządzająca repozytoriami encji w systemie.
 *
 * @package TFramework
 * @subpackage ORM
 * @author Paweł Włodarczyk
 */
class EntityManager
{
	private static $_repositories = array();
	
	public static function registerEntity($entityName)
	{
		if(!self::isRegistered($entityName)){
			self::$_repositories[$entityName] = Repository::createRepository($entityName);
		} else {
			throw new TORMException("Klasa '$entityName' jest już zarejestrowana.");
		}
	}
	
	public static function isRegistered($entityName)
	{
		return isset(self::$_repositories[$entityName]);
	}
	
	public static function getRepository($entityName)
	{
		if(!self::isRegistered($entityName)) {
			self::registerEntity($entityName);
		}
		
		return self::$_repositories[$entityName];
	}
	
	public static function persist($entity)
	{
		if(self::isRegistered($entityName = get_class($entity))){
			self::$_repositories[$entityName]->persist($entity);
		} else {
			throw new TORMException("Klasa '$entityName' nie jest zarejestrowana w klasie EntityManager.");
		}
	}
	
	public static function delete($entity)
	{
		if(self::isRegistered($entityName = get_class($entity))){
			self::$_repositories[$entityName]->delete($entity);
		} else {
			throw new TORMException("Klasa '$entityName' nie jest zarejestrowana w klasie EntityManager.");
		}
	}
	
	public static function isPersisted($entity)
	{
		if(self::isRegistered($entityName = get_class($entity))){
			self::$_repositories[$entityName]->isPersisted($entity);
		} else {
			throw new TORMException("Klasa '$entityName' nie jest zarejestrowana w klasie EntityManager.");
		}
	}
}