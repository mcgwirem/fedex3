<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<form action="FedEx_PHP_Entry.php" method="POST">
Enter Tracking Number: <input type="text" name="tnum"/>
<input type="submit"/>
</form>

<?php
//account details
$key = 'LH9RgUjrvEqHHgaq';
$password = '3vo6fcxMhfN3ejYRBZPiESJTM';
$account_number = '304955554';
$meter_number = '111359514';
$tracking_number = $_POST['tnum'];


/*
include(database_connect.php);

$connectionInfo = 
array(
      "UID"=>$dbuser,
      "PWD"=>$dbpswd,
      "Database"=>$dbhost
   );

$conn = sqlsrv_connect($dbhost, $connectionInfo);

$tsql = "SELECT IV_BOL from SHIPMENTS";

$stmt = sqlsrv_query($conn, $tsql);

if($stmt)
{
   echo "Statement executed. <br>\n";
}
else
{
   echo "Error in statement<br>\n";
   die(print_r(sqlsrv_errors(), true));
}
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

$estDeliveryDate = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[0]->DateOrTimestamp;


if ($status <> 'Delivered') 
{
   $deliveryDate = 'TBD';
}
elseif ($status = 'Delivered')
{
   $estDeliveryDate = '';
}
else
{
   $deliveryDate = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime;
}

print '<pre>';
print 'Status: '.$status.'<br>';
print 'Delivery Date: '.$deliveryDate.'<br>';
print 'Estimated Delivery Date:  '.$estDeliveryDate.'<br>';
print '<hr/>';
print_r($result);
?>
</body>
</html>

