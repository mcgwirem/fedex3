<?php

include('credentials.php');
include('functions_fedex.php');

//connect to database and execute query to get tracking numbers
try 
{
	$conn = new PDO(db_pdohost, db_user, db_pswd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
//throw back error if something goes wrong 
catch (PDOException $e) 
{
	die(print_r($e->getMessage()));
}

//loop through the database and store in array
foreach ($conn->query("SELECT TOP 5 SHP_BOL FROM SHIP_FedEx WHERE SHP_IS_COMPLETE != 1 AND SHP_BOL LIKE '7%'") as $row){
	$data[]=$row;
}

//define the tracking number column name
$colName = 'SHP_BOL';

//main function to do xml and update work
//reference functions_fedex.php to see actual code
foo($data, $colName);

var_dump($failures).'<br>';
var_dump($success).'<br>';
//close the connection
$conn = null;
//var_dump(arrayStoreage());
?>