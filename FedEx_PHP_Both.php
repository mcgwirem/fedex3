<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<?php
//account details
$key = 'LH9RgUjrvEqHHgaq';
$password = '3vo6fcxMhfN3ejYRBZPiESJTM';
$account_number = '304955554';
$meter_number = '111359514';
//intransit
//$tracking_number = '741234216122';
//delivered
$tracking_number = '741234208628';



include(database_connect.php);

// PHP Data Objects(PDO) Sample Code:
/*
try {
    $conn = new PDO($dbhost, $dbuser, $dbpswd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
}
*/
/*$connectionInfo = array("UID" => $dbuserserver, "pwd" => $dbpswd, "Database" => "testDB", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:travismathew-analytics-database1.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);

if($conn === false)
{
   die(print_r(sqlsrv_errors(), true));
}

$sql = "SELECT count(IV_BOL) from SHIPMENTS;";

$stmt = sqlsrv_query($conn, $sql);

if($stmt === false)
{
   die(print_r(sqlsrv_errors (), true));
}

echo "the stmt";

echo $stmt;

sqlsrv_free_stmt($stmt);

sqlsrv_close($conn);
*/

$xml = '
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v12="http://fedex.com/ws/track/v12">
  <soapenv:Header></soapenv:Header>
   <soapenv:Body>
      <v12:TrackRequest>
         <v12:WebAuthenticationDetail>
 <v12:UserCredential>
               <v12:Key>'.$key.'</v12:Key>
               <v12:Password>'.$password.'</v12:Password>
            </v12:UserCredential>
         </v12:WebAuthenticationDetail>
         <v12:ClientDetail>
            <v12:AccountNumber>'.$account_number.'</v12:AccountNumber>
            <v12:MeterNumber>'.$meter_number.'</v12:MeterNumber>
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
               <v12:Value>'.$tracking_number.'</v12:Value>
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

$dd = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime;

$pickup = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[1]->DateOrTimestamp;

/*
function insertInTo($var1, $var2, $var3, $var4, $var5, $var6)
{

}
*/

if ($status == 'Delivered') 
{
   $shp_bol = $tracking_number;
   $shp_del_dt = $dd;
   $shp_pickup_dt = $pickup;
   $shp_sts = $status;
   $shp_eta_dt = $eDT;
   $shp_is_complete = 1;

   print '<pre>';
   print 'Tracking Number: '.$shp_bol.'<br>';
   print 'Status: '.$shp_sts.'<br>';
   print 'Delivery Date: '.$shp_del_dt.'<br>';
   print 'Estimated Delivery Date:  '.$shp_eta_dt.'<br>';
   print '<hr/>';
   print_r($result);
}
else
{
   $shp_bol = $tracking_number;
   $shp_del_dt = null;
   $shp_pickup_dt = $pickup;
   $shp_sts = $status;
   $shp_eta_dt = $eDT;
   $shp_is_complete = 1;

   print '<pre>';
   print 'Tracking Number: '.$tracking_number.'<br>';
   print 'Status: '.$shp_sts.'<br>';
   print 'Delivery Date: '.$shp_del_dt.'<br>';
   print 'Estimated Delivery Date:  '.$shp_eta_dt.'<br>';
   print '<hr/>';
   print_r($result);
}

?>
</body>
</html>

