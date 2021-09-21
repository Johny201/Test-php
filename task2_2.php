<?php
echo("PHP script\n");

$user = "phpUser";
$password = "\$phpUserPassword123";
$databaseName = "orders";
$tableName = "ordersTable11";

function connectToTable($user, $password, $databaseName, $tableName) {
	$mysqlConnect = new mysqli('localhost', $user, $password);
	if($mysqlConnect->connect_error) {
		echo("Error:".$mysqlConnect->connect_error."\n");
	}

	$query = "show databases;";
	$isExistsDatabase = false;
	if($result = $mysqlConnect->query($query)) {
		foreach($result as $row)
			if($row["Database"] == $databaseName) {
				$isExistDatabase = true;
				break;
			}
	}

	if($isExistsDatabase == false) {
		$result = $mysqlConnect->query("create database ".$databaseName);
		if($result == false)
			echo($mysqlConnect->error."\n");
	}

	$mysqlConnect = new mysqli('localhost', $user, $password, $databaseName);

	$query = "show tables from ".$databaseName.";";
	$isExistsTable = false;
	if($result = $mysqlConnect->query($query)) {
		foreach($result as $row)
			if($row["Tables_in_orders"] == $tableName) {
				$isExistDatabase = true;
				break;
			}
	}

	if($isExistsTable == false) {
		$result = $mysqlConnect->query("create table ".$tableName." (id INT(10) AUTO_INCREMENT PRIMARY KEY, event_id INT(11), ticket_type VARCHAR(20), ticket_price INT(11), event_date DATETIME, barcode VARCHAR(120), age VARCHAR(5), created DATETIME);");
		if($result == false)
			echo($mysqlConnect->error."\n");
	}

	return $mysqlConnect;
}

$mysqlConnect = connectToTable($user, $password, $databaseName, $tableName);

function insertDataIntoTable($mysqlConnect, $tableName, $event_id, $ticket_type, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
	$age = "adult";
	$ticket_price = $ticket_adult_price;
	for($i = 0; $i < $ticket_adult_quantity; ++$i)
		insertDataIntoTableStep2($mysqlConnect, $tableName, $event_id, $ticket_type, $event_date, $ticket_price, $age, $i);
	$age = "kid";
	$ticket_price = $ticket_kid_price;
	for($i = 0; $i < $ticket_kid_quantity; ++$i)
		insertDataIntoTableStep2($mysqlConnect, $tableName, $event_id, $ticket_type, $event_date, $ticket_price, $age, $i + $ticket_adult_quantity);
}

function insertDataIntoTableStep2($mysqlConnect, $tableName, $event_id, $ticket_type, $event_date, $ticket_price, $age, $n) {
	$ticket_adult_price = 0;
	$ticket_adult_quantity = 0;
	$ticket_kid_price = 0;
	$ticket_kid_quantity = 0;
	if($age == "adult") {
		$ticket_adult_price = $ticket_price;
		$ticket_adult_quantity = 1;
	}
	else {
		$ticket_kid_price = $ticket_price;
		$ticket_kid_quantity = 0;
	}
	
	do {
		$datetime = new DateTime();
		$created = $datetime->format("Y-m-d H:i:s");
		$barcode = hash("md5", $event_id.$event_date.$ticket_price.$ticket_type.$n.$created, $binary = false);
		$result = apiQuery("", $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);
	
		if($result) {
		
			$query = "insert into ".$tableName." (event_id, event_date, ticket_type, ticket_price, age, barcode, created) values (".$event_id.", \"".$event_date."\", \"".$ticket_type."\", ".$ticket_price.", \"".$age."\", \"".$barcode."\", \"".$created."\");";
			echo($query);
			$mysqlConnect->query($query);
			echo($mysqlConnect->error);
		}
	} while(!$result);
}

function apiQuery($url, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode) {
	//$apiQueryData = array('event_id' => $event_id, 'event_date' => $event_date, 'ticket_adult_price' => $ticket_adult_price, 'ticket_adult_quantity' => $ticket_adult_quantity, 'ticket_kid_price' => $ticket_kid_price, 'barcode' => $barcode);
	
	//$context = stream_context_create($apiQueryData);

	//$result = file_get_contents($url, false, $context);
	
	$randomIndex = rand(0, 3);
	
	switch($randomIndex) {
		case 0:
			$result = array('message' => "order succesfully aproved");
			break;
		case 1:
			$result = array('error' => "event cancelled");
			break;
		case 2:
			$result = array('error' => "no tickets");
			break;
		case 3:
			$result = array('error' => "fan removed");
			break;
	}
	if(array_key_exists("message", $result))
		if($result["message"] == "order succesfully aproved")
			return true;
	return false;
}

$event_id = "012";
$datetime = new DateTime();
$event_date = $datetime->format("Y-m-d H:i:s");
$ticket_adult_price = 250;
$ticket_adult_quantity = 2;
$ticket_kid_price = 125;
$ticket_kid_quantity = 2;
$ticket_type = "individual";

insertDataIntoTable($mysqlConnect, $tableName, $event_id, $ticket_type, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);
?>
