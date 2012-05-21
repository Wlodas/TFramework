<?php

namespace TFramework\DB;

use ReflectionClass;
use PDO;
	
/**
 * Klasa zarządzająca połączeniami do bazy danych poprzez PDO.
 *
 * @package TFramework
 * @subpackage DB
 * @author Paweł Włodarczyk
 */
class PDOManager
{
	/** @var PDO[] */
	private static $_connections = array();
	
	/** @var PDO */
	private static $_current_connection;
	
	/**
	 * Zwraca aktualne połączenie do bazy danych
	 *
	 * @throws TPDOException
	 * @return PDO
	 */
	public static function getCurrentConnection()
	{
		if(empty(self::$_current_connection)){
			throw new TPDOException('Nie zainicjonowano żadnego połączenia z bazą danych.');
		} else {
			return self::$_current_connection;
		}
	}
	
	/**
	 * Zwraca aktualne połączenie do bazy danych
	 *
	 * @throws TPDOException
	 * @return PDO
	 */
	public static function getCurrentConnectionName()
	{
		if(empty(self::$_current_connection)){
			throw new TPDOException('Nie zainicjonowano żadnego połączenia z bazą danych.');
		} else {
			return array_search(self::$_current_connection, self::$_connections);
		}
	}
	
	/**
	 * Zwraca wybrane połączenie do bazy danych
	 *
	 * @param string $conn_name Nazwa połączenia
	 * @throws TPDOException
	 * @return PDO
	 */
	public static function getConnection($conn_name)
	{
		if(!isset(self::$_connections[$conn_name])){
			throw new TPDOException('Połączenie do bazy danych o podanej nazwie nie istnieje.');
		} else {
			return self::$_connections[$conn_name];
		}
	}
	
	/**
	 * Ustawia aktualne połączenie do bazy danych.
	 *
	 * @param string $conn_name
	 * @throws TPDOException
	 * @return void
	 */
	public static function setCurrentConnection($conn_name)
	{
		if(!isset(self::$_connections[$conn_name])){
			throw new TPDOException('Połączenie do bazy danych o podanej nazwie nie istnieje.');
		} else {
			self::$_current_connection = self::$_connections[$conn_name];
		}
	}
	
	/**
	 * Tworzy nowe połączenie do bazy danych.
	 *
	 * @param string $conn_name
	 * @param array $conn_args
	 * @throws TPDOException
	 * @return void
	 */
	public static function createConnection($conn_name, array $conn_args)
	{
		if(isset(self::$_connections[$conn_name])){
			throw new TPDOException('Połączenie do bazy danych o podanej nazwie już istnieje.');
		}
		$pdo_args = array();
		
		if(isset($conn_args['dns']))
			$pdo_args[] = $conn_args['dns'];
		else
			throw new TPDOException('Nie podano adresu DNS dla połączenia z bazą danych.');
		if(isset($pdo_args[0]) && isset($conn_args['username']))
			$pdo_args[] = $conn_args['username'];
		if(isset($pdo_args[1]) && isset($conn_args['passwd']))
			$pdo_args[] = $conn_args['passwd'];
		if(isset($pdo_args[2]) && isset($conn_args['options']))
			$pdo_args[] = $conn_args['options'];
		
		$ref = new ReflectionClass('PDO');
		$conn = $ref->newInstanceArgs($pdo_args);
		
		self::$_connections[$conn_name] = $conn;
		if(empty(self::$_current_connection)) self::$_current_connection = $conn;
	}
	
	/**
	 * Sprawdza czy połączenie o podanej nazwie istnieje.
	 *
	 * @param string $conn_name
	 * @return bool
	 */
	public static function hasConnection($conn_name)
	{
		return isset(self::$_connections[$conn_name]);
	}
}