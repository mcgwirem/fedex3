<?php

$trck = ' 638879972890';
$complete = 1;
$dd = date('Y-m-d', 0);
$pd = date('Y-m-d', 0);
$sts = 'Delivered';
$eta = date('Y-m-d', 0);


updatedDB($trck, $dd, $pd, $sts, $eta, $complete);
echo $trck.'<br>';
echo $complete.'<br>';

echo "HELLO<br>";

function updatedDB ($var1, $var2, $var3, $var4, $var5, $var6)
{
	echo "in update<br>";
	try 
	{
		$conn = new PDO(db_pdohost, db_user, db_pswd);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		echo 'in try<br>';
	} 
	catch (PDOException $e) 
	{
		die(print_r($e->getMessage()));
	}

	$stmtU = $conn->prepare('UPDATE SHIP_FedEx SET SHP_DEL_DT = ?, SHP_PICKUP_DT = ?, SHP_STS = ?, SHP_ETA_DT = ?, SHP_IS_COMPLETE = ? WHERE SHP_BOL = ?');
	$stmtU->execute(array($var2, $var3, $var4, $var5, $var6, $var1));
	if ($stmtU->rowCount())
	{
		echo 'success';
	}
	else
	{
		echo 'failure';
	}
	echo 'after execute<br>';
	$stmtU = null;
	$conn = null;	
}


/************************UPDATE FUNCTION**************************/
/*
function updatedDB ($var1, $var6)
{
	echo "in update<br>";
	try 
	{
		$conn = new PDO(db_pdohost, db_user, db_pswd);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		echo 'in try<br>';
	} 
	catch (PDOException $e) 
	{
		die(print_r($e->getMessage()));
	}

	$stmtU = $conn->prepare('UPDATE SHIP_FedEx SET SHP_IS_COMPLETE = ? WHERE SHP_BOL = ?');
	//$stmtU->bindParam(1, $var6, PDO::PARAM_INT);
	//$stmtU->bindParam(2, $var1, PDO::PARAM_STR, 30);
	echo "after bind<br>";
	print_r($stmtU).'<br>';
	$stmtU->execute(array($var6, $var1));
	if ($stmtU->rowCount())
	{
		echo 'success';
	}
	else
	{
		echo 'failure';
	}
	echo 'after execute<br>';
	print($var6).'<br>';
	$stmtU = null;
	$conn = null;	
}
*/


?>
