<?php

namespace Model;

use TFramework\ORM\Entity;
use TFramework\Core\Context;

/** 
 * @Entity
 * @Table('person')
 * @RepositoryClass('Model\PersonRepository')
 */
class Person extends Entity
{
	/**
	 * @PrimaryKey(autoincrement = true, sequence = 'person_id_seq')
	 * @Column(type = 'integer')
	 */
	public $id;
	
	/** @Column(type = 'string') */
	public $name;
	
	/** @Column(type = 'integer') */
	public $operator_id;
	
	/** @Column(type = 'datetime') */
	public $created_at;
	
	/** @Column(type = 'datetime') */
	public $updated_at;	
	/** 
	 * @PreInsert
	 * @PreUpdate
	 * @PreDelete
	 * @PostInsert
	 * @PostUpdate
	 * @PostDelete
	 */
	public function logEntity($event)
	{
		\FB::log($this, __CLASS__.' - '.$event);
	}
	
	/** @PreInsert */
	public function setCreatedAt()
	{
		$this->created_at = @date('Y-m-d H:i:s');
	}
	
	/**
	 * @PreInsert
	 * @PreUpdate
	 */
	public function setUpdatedAt()
	{
		$this->updated_at = @date('Y-m-d H:i:s');
	}
}