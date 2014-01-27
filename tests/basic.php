<?php

include __DIR__."/../vendor/autoload.php";
include __DIR__."/../NotORM.php";
include __DIR__."/../model/Application.php";

use Tracy\Debugger;
Debugger::enable();

$pdo = new PDO('mysql:host=localhost;dbname=example;charset=utf8', "root", "root");
$db = new Wigex\Database\NotORM($pdo);

$db->setTableMap(array(
   'application' => 'Model\\Application',
));

$application = $db->application()->limit(1)->fetch();
$application['name'] = "Emacs";
$application->save();

Debugger::dump($application);

