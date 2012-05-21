<?php

require_once __DIR__.'/bootstrap.php';

use TFramework\DB\PDOManager;
use TFramework\ORM\EntityManager;
use Model\Person;
use Form\PersonForm;
use TFramework\Widget\InputText;
use TFramework\Widget\Select;

$conn = PDOManager::getCurrentConnection();

/*$operator_id = rand(1, 1000);
$conn->exec("SET @operator_id = $operator_id");

foreach($conn->query('SELECT * FROM person ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC) as $row){
	foreach($row as $column => $value){
		echo "$column: $value, ";
	}
	echo "<br>";
}
echo "<br>";*/

$person1 = new Person();
$person1->name = 'Lolek';
$person1->save();

$person2 = Person::find($person1->id);

FB::log($person1, 'Person1');
FB::log($person2, 'Person2');
FB::log($person1 === $person2 ? 'true' : 'false', 'Same person?');

foreach(Person::findAll() as $person) {
	if($person->id == $person1->id) {
		FB::log($person1 === $person ? 'true' : 'false', 'Same person?');
	}
}

Person::find(null);

$form = new PersonForm($person);

FB::log($form->getObject());
FB::log($form->getValues());

$form->bind(array('name' => 'Bolek "!@#$%^&*()'));

$form->bindValuesToObject();

$form->getObject()->save();

FB::log($form->getObject());
FB::log($form->getValues());

$form['surname'] = new InputText(array('label' => 'Nazwisko'));
$form['select'] = new Select(array('label' => 'Choice', 'choices' => array()));

foreach($form as $field) {
	$field->renderLabel();
	$field->render();
	if($field->isVisible()) echo '<br/>';
}

$form = new PersonForm(new Person());
$form->bind(array('name' => 'Ala'));
$form->bindValuesToObject();
$person = $form->getObject();
$person->save();

FB::log($person, 'New person from form.');
