<?php
define('db_user', 'mmcgwire');
define('db_pswd', '5WOPLjwie$6QZI^B04fw19MafyTcd#ozVhW*');
define('db_host', 'travismathew-analytics-database1.database.windows.net');
define('db_name', 'testDB');
define('fe_key', 'LH9RgUjrvEqHHgaq');
define('fe_pswd', '3vo6fcxMhfN3ejYRBZPiESJTM');
define('fe_acct', '304955554');
define('fe_meter', '111359514');
define('db_pdohost', 'sqlsrv:server=travismathew-analytics-database1.database.windows.net; Database=testDB');

/*
try 
{
	$conn = new PDO(db_pdohost, db_user, db_pswd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	foreach ($conn->query("SELECT count(SHP_BOL) as cntSB FROM SHIP_FedEx") as $row) {
		print_r($row);
	}
	$conn = null;
} 
catch (PDOException $e) 
{
	die(print_r($e->getMessage()));
}
*/

try 
{
	$conn = new PDO(db_pdohost, db_user, db_pswd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	foreach ($conn->query("UPDATE SHIP_FedEx SET SHP_IS_COMPLETE = 1 WHERE SHP_BOL = ' 638879972890'") as $row) {
		print_r($row);
	}
	$conn = null;
} 
catch (PDOException $e) 
{
	die(print_r($e->getMessage()));
}

//include(functions.php);

/*
$connectionOptions = array(
	'Database'=>db_name,
	'UID'=>db_user,
	'PWD'=>db_pswd
	);

$conn = sqlsrv_connect(db_host, $connectionOptions);

$var1 = 1;
$var2 = (string)741234208628;

$tsql = "UPDATE SHIP_FedEx SET SHP_IS_COMPLETE = $var1 WHERE SHP_BOL = $var2";

$getResults = sqlsrv_query($conn, $tsql);

if ($getResults == FALSE) 
{
	//echo 'inside if <br>';
	die(print_r(sqlsrv_errors(), true));
}
else
{
	echo "sucess";
}

$colName = 'countTN';

echo 'connect success <br>';

sqlsrv_free_stmt($getResults);

echo 'free statement<br>';

sqlsrv_close($conn);

echo 'close<br>';

*/

?>