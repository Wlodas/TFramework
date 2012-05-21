<?php

namespace TFramework\ORM;

use TFramework\DB\PDOManager;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionAnnotatedClass;
use ReflectionAnnotatedMethod;
	
/**
 * Klasa zarządzająca encją.
 *
 * @package TFramework
 * @subpackage ORM
 * @author Paweł Włodarczyk
 */
class Repository
{
	protected $entity_instances = array();
	protected $entity_instances_primary_keys = array();
	
	protected $entityName;
	protected $class_metadata;
	protected $properties_metadata;
	protected $methods_metadata;
	
	/** @var PDO */
	protected $connection;
	protected $pdo_driver;
	
	protected $table;
	protected $primary_keys = array();
	protected $columns = array();
	
	protected $preinsert_methods = array();
	protected $postinsert_methods = array();
	protected $preupdate_methods = array();
	protected $postupdate_methods = array();
	protected $predelete_methods = array();
	protected $postdelete_methods = array();
	
	protected function __construct($entityName, PDO $connection, array $class_metadata, array $properties_metadata, array $methods_metadata)
	{
		$this->entityName = $entityName;
		$this->class_metadata = $class_metadata;
		$this->properties_metadata = $properties_metadata;
		$this->methods_metadata = $methods_metadata;
		$this->connection = $connection;
		$this->pdo_driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
		
		if(!in_array($this->pdo_driver, array('mysql', 'pgsql'))) {
			throw new TORMException("Driver '{$this->pdo_driver}' jest obecnie nie wspierany przez system ORM.");
		}
		
		$this->parseMetadata();
	}
	
	protected function parseMetadata()
	{
		$this->table = $this->class_metadata['Table']->value;
		
		foreach($this->properties_metadata as $property => $annotations) {
			if(array_key_exists('Column', $annotations)) {
				if(empty($annotations['Column']->type)) {
					throw new TORMException("Kolumna '$property' nie posiada zdefiniowanego typu danych.");
				}
				
				$this->columns[$property] = array(
					'type' => $annotations['Column']->type,
					'notnull' => $annotations['Column']->notnull
				);
				
				if(array_key_exists('PrimaryKey', $annotations)) {
					if(count($this->primary_keys) == 1) {
						$primary_key = current($this->primary_keys);
						
						if($primary_key['autoincrement'] && $annotations['PrimaryKey']->autoincrement) {
							throw new TORMException("Może istnieć jedynie 1 kolumna z autoinkrementacją.");
						}
					}
					
					$this->primary_keys[$property] = array(
						'autoincrement' => $annotations['PrimaryKey']->autoincrement
					);
					
					$this->entity_instances_primary_keys[$property] = array();
					
					if($this->pdo_driver == 'pgsql' && $annotations['PrimaryKey']->autoincrement && empty($annotations['PrimaryKey']->sequence)) {
						throw new TORMException("Driver 'pgsql' wymaga podania nazwy sekwencji autoinkrementacji klucza głównego '$property'.");
					} else {
						$this->primary_keys[$property]['sequence'] = $annotations['PrimaryKey']->sequence;
					}
				}
			}
		}
		
		foreach($this->methods_metadata as $method => $annotations) {
			if(array_key_exists('PreInsert', $annotations)) $this->preinsert_methods[] = $method;
			if(array_key_exists('PostInsert', $annotations)) $this->postinsert_methods[] = $method;
			if(array_key_exists('PreUpdate', $annotations)) $this->preupdate_methods[] = $method;
			if(array_key_exists('PostUpdate', $annotations)) $this->postupdate_methods[] = $method;
			if(array_key_exists('PreDelete', $annotations)) $this->predelete_methods[] = $method;
			if(array_key_exists('PostDelete', $annotations)) $this->postdelete_methods[] = $method;
		}
	}
	
	public static function createRepository($entityName)
	{
		if(!EntityManager::isRegistered($entityName)) {
			$entity = new ReflectionAnnotatedClass($entityName);
			
			$class_metadata = array();
			foreach($entity->getAnnotations() as $annotation) {
				$class_metadata[get_class($annotation)] = $annotation;
			}
			
			if(!array_key_exists('Entity', $class_metadata)) {
				throw new TORMException("Klasa '".$entity->name."' nie jest klasą encji.");
			}
			
			if(!array_key_exists('Table', $class_metadata) && empty($class_metadata['Table']->value)) {
				throw new TORMException("Klasa '".$entity->name."' nie posiada zdefiniowanej nazwy tabeli.");
			}
			
			if(!array_key_exists('Connection', $class_metadata)) {
				if(!empty($class_metadata['Connection']->value)) {
					$connection = PDOManager::getConnection($class_metadata['Connection']->value);
				} else {
					$connection = PDOManager::getCurrentConnection();
				}
			}
			
			$properties_metadata = array();
			foreach($entity->getProperties() as $property) {
				$annotations = $property->getAnnotations();
				if($property->isPublic() && count($annotations) > 0) {
					$properties_metadata[$property->name] = array();
					foreach($annotations as $annotation) {
						$properties_metadata[$property->name][get_class($annotation)] = $annotation;
					}
				}
			}
			
			$methods_metadata = array();
			foreach($entity->getMethods() as $method) {
				$annotations = $method->getAnnotations();
				if($method->isPublic() && count($annotations) > 0) {
					$methods_metadata[$method->name] = array();
					foreach($annotations as $annotation) {
						$methods_metadata[$method->name][get_class($annotation)] = $annotation;
					}
				}
			}
			
			$repository_class = isset($class_metadata['RepositoryClass']) ? $class_metadata['RepositoryClass']->value : 'TFramework\ORM\Repository';
			
			$repo = new ReflectionClass($repository_class);
			if($repo->isSubclassOf('TFramework\ORM\Repository') || $repo->name == 'TFramework\ORM\Repository'){
				return new $repository_class($entityName, $connection, $class_metadata, $properties_metadata, $methods_metadata);
			} else {
				throw new TORMException("Klasa '$repository_class' musi dziedziczyć po klasie 'TFramework\ORM\Repository'.");
			}
		}
	}
	
	public static function getRepository($entityName)
	{
		EntityManager::getRepository($entityName);
	}
	
	public function getSelectSQL()
	{
		$columns = implode(', ', array_keys($this->columns));
		
		return "SELECT $columns FROM {$this->table}";
	}
	
	public function findAll()
	{
		$stmt = $this->connection->prepare($this->getSelectSQL());
		$stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityName);
		$stmt->execute();
		
		$entities = $stmt->fetchAll();
		
		foreach($entities as $key => $entity) {
			$entities[$key] = $this->storeFoundEntity($entity);
		}
		
		return $entities;
	}
	
	public function find($ids) {		
		if(count($this->primary_keys) > 1 && !is_array($ids)) {
			throw new TORMException("Nie podano wartości dla wszystkich kluczy głównych encji '{$this->entityName}'.");
		}
		
		$wheres = array();
		$values = array();
		
		if(is_array($ids)) {
			if(in_array(null, $ids)) return null;
			
			foreach(array_keys($this->primary_keys) as $key) {
				if(isset($ids[$key])) {
					$wheres[] = "$key = ?";
					$values[] = $ids[$key];
				} else {
					throw new TORMException("Nie podano wartości dla wszystkich kluczy głównych encji '{$this->entityName}'.");
				}
			}
		} else {
			if($ids == null) return null;
			
			$key = key($this->primary_keys);
			$wheres[] = "$key = ?";
			$values[] = $ids;
			$ids = array($key => $ids);
		}
		
		if($entity = $this->getPersistedByKeys($ids)) {
			return $entity;
		}
		
		$wheres = implode(' AND ', $wheres);
		
		$stmt = $this->connection->prepare($this->getSelectSQL()." WHERE $wheres LIMIT 1");
		$stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityName);
		$stmt->execute($values);
		
		$entity = $stmt->fetch();
		
		return $entity ? $this->storeFoundEntity($entity) : null;
	}
	
	protected function getPersistedByKeys(array $ids)
	{
		$all_keys = count($this->primary_keys);
		$matches = 0;
	
		foreach($this->entity_instances_primary_keys as $primary_key => $keys_values) {
			if($hash = array_search($ids[$primary_key], $keys_values)) $matches++;
		}
	
		if($matches == $all_keys) {
			return $this->entity_instances[$hash];
		}
	}
	
	protected function storeFoundEntity($entity)
	{
		$all_keys = count($this->primary_keys);
		$matches = 0;
		
		foreach($this->entity_instances_primary_keys as $primary_key => $keys_values) {
			if($hash = array_search($entity->$primary_key, $keys_values)) $matches++;
		}
		
		if($matches != $all_keys) {
			$hash = spl_object_hash($entity);
			
			$this->entity_instances[$hash] = $entity;
			
			foreach(array_keys($this->primary_keys) as $primary_key) {
				$this->entity_instances_primary_keys[$primary_key][$hash] = $entity->$primary_key;
			}
		}
		
		return $this->entity_instances[$hash];
	}
	
	public function isPersisted($entity) {
		return isset($this->entity_instances[spl_object_hash($entity)]);
	}
	
	public function persist($entity) {
		if($entity instanceof $this->entityName) {
			if(!$this->isPersisted($entity)) {
				return $this->insert($entity);
			} else {
				return $this->update($entity);
			}
		} else {
			throw new TORMException("Podany parametr nie jest instancją encji '".$this->entityName."'.");
		}
	}
	
	protected function insert($entity) {
		foreach($this->preinsert_methods as $method) {
			$entity->$method('PreInsert');
		}
		
		$primary_column = key($this->primary_keys);
		$primary_key = current($this->primary_keys);
		$is_autoincrement = $primary_key['autoincrement'] ? true : false;
		
		$columns = array();
		$placeholders = array();
		$values = array();
		
		foreach(array_keys($this->columns) as $column) {
			if($is_autoincrement && $column == $primary_column) continue;
			
			$columns[] = $column;
			$placeholders[] = '?';
			$values[] = $entity->$column;
		}
		
		$columns = implode(', ', $columns);
		$placeholders = implode(', ', $placeholders);
		
		$this->connection
			->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)")
			->execute($values)
		;
		
		if($is_autoincrement) {
			if($this->pdo_driver == 'pgsql') {
				$entity->$primary_column = $this->connection->lastInsertId($primary_key['sequence']);
			} else {
				$entity->$primary_column = $this->connection->lastInsertId();
			}
			
			if($entity->$primary_column == null) {
				throw new TORMException("Nie znaleziono identyfikatora dla autoinkrementalej kolumny '$primary_column'");
			}
		}
		
		$hash = spl_object_hash($entity);
		
		$this->entity_instances[$hash] = $entity;
		
		foreach(array_keys($this->primary_keys) as $key) {
			$this->entity_instances_primary_keys[$key][$hash] = $entity->$key;
		}
		
		foreach($this->postinsert_methods as $method) {
			$entity->$method('PostInsert');
		}
	}
	
	protected function update($entity) {
		foreach($this->preupdate_methods as $method) {
			$entity->$method('PreUpdate');
		}
		
		$hash = spl_object_hash($entity);
		$sets = array();
		$wheres = array();
		$values = array();
		
		foreach(array_keys($this->columns) as $column) {
			$sets[] = "SET $column = ?";
			$values[] = $entity->$column;
		}
		
		$sets = implode(', ', $sets);
		
		foreach(array_keys($this->primary_keys) as $key) {
			$wheres[] = "$key = ?";
			$values[] = $this->entity_instances_primary_keys[$key][$hash];
		}
		
		$wheres = implode(' AND ', $wheres);
		
		$this->connection
			->prepare("UPDATE {$this->table} $sets WHERE $wheres")
			->execute($values)
		;
		
		foreach(array_keys($this->primary_keys) as $key) {
			$this->entity_instances_primary_keys[$key][$hash] = $entity->$key;
		}
		
		foreach($this->postupdate_methods as $method) {
			$entity->$method('PostUpdate');
		}
	}
	
	public function delete($entity) {
		if($entity instanceof $this->entityName) {
			if($this->isPersisted($entity)) {
				foreach($this->predelete_methods as $method) {
					$entity->$method('PreDelete');
				}
				
				$wheres = array();
				$values = array();
				
				foreach(array_keys($this->primary_keys) as $key) {
					$wheres[] = "$key = ?";
					$values[] = $entity->$key;
				}
				
				$wheres = implode(' AND ', $wheres);
				
				$q = $this->connection->prepare("DELETE FROM {$this->table} WHERE $wheres");
				$q->execute($values);
				
				if($deleted = $q->rowCount() == 1 ? true : false) {
					$hash = spl_object_hash($entity);
					unset($this->entity_instances[$hash]);
					foreach(array_keys($this->primary_keys) as $key) {
						$entity->$key = null;
						unset($this->entity_instances_primary_keys[$key][$hash]);
					}
				}
				
				if($deleted) {
					foreach($this->postdelete_methods as $method) {
						$entity->$method('PostDelete');
					}
					return true;
				} else {
					return false;
				}
			}
		} else {
			throw new TORMException("Podany parametr nie jest instancją encji '".$this->entityName."'.");
		}
	}
	
	/** @return PDO */
	public function getConnection()
	{
		return $this->connection;
	}
	
	public function getTable()
	{
		return $this->table;
	}
	
	public function getPrimaryKeys()
	{
		return $this->primary_keys;
	}
	
	public function getColumns()
	{
		return $this->columns;
	}
}