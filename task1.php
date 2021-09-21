<?php
echo("PHP script\n");

$user = "phpUser";
$password = "\$phpUserPassword123";
$databaseName = "orders";
$tableName = "ordersTable4";

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
		$result = $mysqlConnect->query("create table ".$tableName." (id INT(10) AUTO_INCREMENT PRIMARY KEY, event_id INT(11), event_date DATETIME, ticket_adult_price INT(11), ticket_adult_quantity INT(11), ticket_kid_price INT(11), ticket_kid_quantity INT(11), barcode VARCHAR(120), equal_price INT(11), created DATETIME);");
		if($result == false)
			echo($mysqlConnect->error."\n");
	}

	return $mysqlConnect;
}

$mysqlConnect = connectToTable($user, $password, $databaseName, $tableName);

function insertDataIntoTable($mysqlConnect, $tableName, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
	$equal_price = $ticket_adult_price * $ticket_adult_quantity + $ticket_kid_price * $ticket_kid_quantity;
	$barcode = hash("md5", $event_id.$event_date.$equal_price, $binary = false);
	
	$result = apiQuery("", $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);
	
	if($result) {
		$datetime = new DateTime();
		$created = $datetime->format("Y-m-d H:i:s");
	
		$query = "insert into ".$tableName." (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created) values (".$event_id.", \"".$event_date."\", ".$ticket_adult_price.", ".$ticket_adult_quantity.", ".$ticket_kid_price.", ".$ticket_kid_quantity.", \"".$barcode."\", ".$equal_price.", \"".$created."\");";
		$mysqlConnect->query($query);
		echo($mysqlConnect->error);
	}
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

insertDataIntoTable($mysqlConnect, $tableName, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);
?>
