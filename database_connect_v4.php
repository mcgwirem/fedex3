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

//include(functions.php);


//connect to database and execute query to get tracking numbers
//store them in array 

try 
{
	$conn = new PDO(db_pdohost, db_user, db_pswd);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) 
{
	die(print_r($e->getMessage()));
}

foreach ($conn->query("SELECT TOP 2 SHP_BOL FROM SHIP_FedEx WHERE SHP_IS_COMPLETE != 1") as $row){
	$data[]=$row;
}

$colName = 'SHP_BOL';

echo 'function';

foo($data, $colName);

$conn = null;

echo 'close<br>';

/*******************************************FUNCTIONS********************************************/

/************************UPDATE FUNCTION**************************/
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

/************************STRING TO DATE FUNCTION**************************/
function stringToDate($var)
{
	$newFormat = date('Y-m-d', strtotime($var));
	return $newFormat;
}

/********************************* MAIN FUNCTION **********************************************/
function foo (&$array, $var1)
{
	for ($i=0; $i < count($array); $i++) 
		{ 
			echo "inside for";
			echo "<pre>";
			print_r($array[$i][$var1]);
			echo "</pre>";

			$trkNum = $array[$i][$var1];
			
			$xml = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v12="http://fedex.com/ws/track/v12">
			  <soapenv:Header></soapenv:Header>
			   <soapenv:Body>
			      <v12:TrackRequest>
			         <v12:WebAuthenticationDetail>
			 <v12:UserCredential>
			               <v12:Key>'.fe_key.'</v12:Key>
			               <v12:Password>'.fe_pswd.'</v12:Password>
			            </v12:UserCredential>
			         </v12:WebAuthenticationDetail>
			         <v12:ClientDetail>
			            <v12:AccountNumber>'.fe_acct.'</v12:AccountNumber>
			            <v12:MeterNumber>'.fe_meter.'</v12:MeterNumber>
			            <v12:Localization>
			               <v12:LanguageCode>EN</v12:LanguageCode>
			               <v12:LocaleCode>US</v12:LocaleCode>
			            </v12:Localization>
			         </v12:ClientDetail>
			         <v12:TransactionDetail>
			            <v12:CustomerTransactionId>Track By Number_v12</v12:CustomerTransactionId>
			</v12:TransactionDetail>
			         <v12:Version>
			            <v12:ServiceId>trck</v12:ServiceId>
			            <v12:Major>12</v12:Major>
			            <v12:Intermediate>0</v12:Intermediate>
			            <v12:Minor>0</v12:Minor>
			         </v12:Version>
			         <v12:SelectionDetails>
			            <v12:PackageIdentifier>
			               <v12:Type>TRACKING_NUMBER_OR_DOORTAG</v12:Type>
			               <v12:Value>'.$trkNum.'</v12:Value>
			            </v12:PackageIdentifier>
			         </v12:SelectionDetails>
			         <v12:ProcessingOptions>INCLUDE_DETAILED_SCANS</v12:ProcessingOptions>
			      </v12:TrackRequest>
			   </soapenv:Body>
			</soapenv:Envelope>
			';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://ws.fedex.com:443/web-services');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			$result_xml = curl_exec($ch);

			// remove colons and dashes to simplify the xml
			$result_xml = str_replace(array(':','-'), '', $result_xml);
			$result = @simplexml_load_string($result_xml);

			$status = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->Description;
			
			$eDT = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[0]->DateOrTimestamp;

			$eDT = stringToDate($eDT);

			$dd = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime;

			$dd = stringToDate($dd);

			$pickup = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[1]->DateOrTimestamp;

			$pickup = stringToDate($pickup);

			$notification = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->Notification->Severity;
			
			if($notification == 'ERROR')
			{
				$shp_bol = $trkNum;
				//echo 'shp_bol: '.$shp_bol.'<br>';
				$shp_del_dt = null;
				//echo 'shp_bol: '.$shp_del_dt.'<br>';
				$shp_pickup_dt = null;
				//echo 'shp_bol: '.$shp_pickup_dt.'<br>';
				$shp_sts = 'Not Available';
				//echo 'shp_bol: '.$shp_sts.'<br>';
				$shp_eta_dt = null;
				//echo 'shp_bol: '.$shp_eta_dt.'<br>';
				$shp_is_complete = 5;

			   updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
			}
			elseif ($status == 'Delivered') 
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = $dd;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 1;

			   updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
			   //test($conn, $shp_is_complete, $shp_bol);
			}
			else
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = null;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 0;

			   updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);

			}

		}
}


/************************INSERT INTO FUNCTION**************************/
/*function insertInto($var1, $var2, $var3, $var4, $var5, $var6)
{

	$sql = "INSERT INTO SHIP_FedEx (SHP_BOL, SHP_DEL_DT, SHP_PICKUP_DT, SHP_STS, SHP_ETA_DT, SHP_IS_COMPLETE) VALUES (?, ?, ?, ?, ?, ?)";
	$params = array(
		'SHP_BOL'=>$var1,
		'SHP_DEL_DT'=>$var2,
		'SHP_PICKUP_DT'=>$var3,
		'SHP_STS'=>$var4,
		'SHP_ETA_DT'=>$var5,
		'SHP_IS_COMPLETE'=>$var6
		);
	$stmt = sqlsrv_query($conn, $sql, $params);
	if($stmt === false)
	{
		echo 'insert<br>';
		die(print_r(sqlsrv_errors(), true));
	}

	sqlsrv_free_stmt($stmt);
}
*/
/*
function test($var0, $var1, $var2)
{
	$sql = "UPDATE SHIP_FedEx SET SHP_IS_COMPLETE = $var1 WHERE SHP_BOL = $var2";

	$params = array(
		'SHP_IS_COMPLETE'=>$var1,
		'SHP_BOL'=>(string)$var2
		);
	$stmt = sqlsrv_query($var0, $sql);

	if($stmt === false)
	{
		echo 'test<br>';
		die(print_r(sqlsrv_errors(), true));
	}

	sqlsrv_free_stmt($stmt);
}
*/

?>