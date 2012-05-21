<?php

require_once __DIR__.'/lib/vendor/TFramework/required.php';

use TFramework\ClassLoader\NamespaceClassLoader;
use TFramework\DB\PDOManager;
use TFramework\ORM\EntityManager;

$loader = new NamespaceClassLoader('Model', __DIR__.'/lib');
$loader->register();

$loader = new NamespaceClassLoader('Form', __DIR__.'/lib');
$loader->register();

$databases = Spyc::YAMLLoad(__DIR__.'/config/databases.yml');
foreach($databases as $database => $conn_args){
	PDOManager::createConnection($database, $conn_args);
}

EntityManager::registerEntity('Model\Person');

