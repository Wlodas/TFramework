<?php

namespace TFramework\ORM;

use PDO;

abstract class Entity
{
	/** @return Repository */
	public static function getRepository()
	{
		return EntityManager::getRepository(get_called_class());
	}
	
	public static function getPrimaryKeys()
	{
		return self::getRepository()->getPrimaryKeys();
	}
	
	public static function getTableName()
	{
		return self::getRepository()->getTableName();
	}
	
	/** @return PDO */
	public static function getConnection()
	{
		return self::getRepository()->getConnection();
	}
	
	public function save()
	{
		return EntityManager::persist($this);
	}
	
	public function delete()
	{
		return EntityManager::delete($this);
	}
	
	public function isNew()
	{
		return !EntityManager::isPersisted($this);
	}
	
	public static function find($args)
	{
		return self::getRepository()->find($args);
	}
	
	public static function findAll()
	{
		return self::getRepository()->findAll();
	}
}