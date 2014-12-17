<?php
require_once("CyberDB.php");

$cyberDB = new CyberDB();
$cyberDB->setDB("hostname", "user", "pass", "name");

$columns = array('idUsuario', 'name', 'surname', 'phone', 'age', 'state');
$table = 'Users';
$whereClauses = array('state=\'US\'', 'age>23');

$query = $cyberDB->getQuery();

$query->Select($columns);
$query->From($table);
$query->Where($whereClauses);
$cyberDB->setQuery($query);

$usersArray = $cyberDB->getArray();
//$usersArray = $cyberDB->getObjectsArray();
foreach($usersArray as $user) {
	echo $user["name"]." ".$user["surname"]." ".$user["phone"]."<p></p>";
	//echo $user.->name." ".$user->surname." ".$user->phone."<p></p>";
}

$query = $cyberDB->getQuery();
$query->Select($columns);
$query->From($table);
$query->Where("idUser=2");
$cyberDB->setQuery($query);

$userRow = $cyberDB->getRow();
//$userRow = $cyberDB->getObject();
echo $user["name"]." ".$user["surname"]." ".$user["phone"]."<p></p>";
//echo $user.->name." ".$user->surname." ".$user->phone."<p></p>";

$query = $cyberDB->getQuery();
$query->Select("name");
$query->From("Users");
$query->Where("idUser=2");
$cyberDB->setQuery($query);

$userName = $cyberDB->getResult();
echo $userName;

$values = array(1, "Miguel", "S. Mendoza", "999999999", "30", "US");
$query = $cyberDB->getQuery();
$query->Insert($table);
$query->Columns($columns);
$query->Values($values);
$cyberDB->setQuery($query);

$cyberDB->executeQuery();

$set = array("nombre='Pepe'", "age=32");
$query = $cyberDB->getQuery();
$query->Update($table);
$query->Set($set);
$query->Where($whereClauses);

$cyberDB->executeQuery();

$query = $cyberDB->getQuery();
$query->Delete($table);
$query->Where($whereClauses);

$cyberDB->executeQuery();

	
?>