<?php

define('VENDOR_DIR', realpath(__DIR__.'/../'));
define('TFRAMEWORK_DIR', realpath(VENDOR_DIR.'/TFramework'));

require_once TFRAMEWORK_DIR.'/ClassLoader/NamespaceClassLoader.php';

use TFramework\ClassLoader\DirectoryClassLoader;
use TFramework\ClassLoader\NamespaceClassLoader;
use TFramework\ClassLoader\MultiPrefixDependencyClassLoader;

$loader = new NamespaceClassLoader('TFramework', VENDOR_DIR);
$loader->register();

MultiPrefixDependencyClassLoader::getInstance()
	->addPrefixes(array(
		'Annotation' => TFRAMEWORK_DIR.'/vendor/addendum/annotations.php',
		'Reflection' => TFRAMEWORK_DIR.'/vendor/addendum/annotations.php',
		'Spyc' => TFRAMEWORK_DIR.'/vendor/Spyc/Spyc.php',
		'Smarty' => TFRAMEWORK_DIR.'/vendor/Smarty/Smarty.class.php',
		'Swift' => TFRAMEWORK_DIR.'/vendor/Swift_Mailer/swift_required.php',
		'FB' => TFRAMEWORK_DIR.'/vendor/FirePHPCore/fb.php',
	))
	->register()
;

$loader = new DirectoryClassLoader(TFRAMEWORK_DIR.'/ORM/Annotation');
$loader->load();

unset($loader);
