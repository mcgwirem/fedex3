<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php
//your account details here
$key = 'gtFbRYRR2Yid6g66';
$password = 'i9ftkkaHkvQzZd2Wafma1LJiP';
$account_number = '510087160';
$meter_number = '100336990';
$tracking_number = '741234208628';

$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v12="http://fedex.com/ws/track/v12">
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
            <v12:Intermediate>1</v12:Intermediate>
            <v12:Minor>0</v12:Minor>
         </v12:Version>
         <v12:SelectionDetails>
            <v12:CarrierCode>FDXE</v12:CarrierCode>
            <v12:PackageIdentifier>
               <v12:Type>'.$tracking_number.'</v12:Type>
               <v12:Value>Input Your Information</v12:Value>
            </v12:PackageIdentifier>
            <v12:ShipmentAccountNumber> Input Your Information</v12:ShipmentAccountNumber>
            <v12:SecureSpodAccount>Input Your Information</v12:SecureSpodAccount>
            <v12:Destination>
               <v12:StreetLines>Input Your Information</v12:StreetLines>
               <v12:City>Texas</v12:City>
               <v12:StateOrProvinceCode>TX</v12:StateOrProvinceCode>
               <v12:PostalCode>73301</v12:PostalCode>
               <v12:CountryCode>US</v12:CountryCode>
            </v12:Destination>
         </v12:SelectionDetails>
      </v12:TrackRequest>
   </soapenv:Body>
</soapenv:Envelope>';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://wsbeta.fedex.com:443/web-services');
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

print '<pre>';
print 'Rate: $';
print (string) $result->SOAPENVBody->RateReply->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
print '<hr/>';
print_r($result);
?>
</body>
</html>
