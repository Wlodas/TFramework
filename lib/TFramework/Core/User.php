<?php

namespace TFramework\Core;

/**
 * Klasa zarządzająca zmiennymi aplikacji oraz autoryzacją użytkownika.
 *
 * @package TFramework
 * @subpackage ORM
 * @author Paweł Włodarczyk
 */
class User
{
	protected $id = 100;
	
	public function getId() {
		return $this->id;
	}
}