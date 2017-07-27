<!DOCTYPE html>
<html>
<head>
<title></title>
</head>
<body>
<?php
//account details
$key = 'gtFbRYRR2Yid6g66';
$password = 'kTzaKCdTd5YF8rlWeDLHL4Nya';
$account_number = '510087160';
$meter_number = '100336990';
$tracking_number = '741234208628';

$xml = '
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
<TrackReply xmlns="http://fedex.com/ws/track/v12">
<HighestSeverity>SUCCESS</HighestSeverity>
<Notifications>
<Severity>SUCCESS</Severity>
<Source>trck</Source>
<Code>0</Code>
<Message>Request was successfully processed.</Message>
<LocalizedMessage>Request was successfully processed.</LocalizedMessage>
</Notifications>
<TransactionDetail>
<CustomerTransactionId>Track By Number_v12</CustomerTransactionId>
</TransactionDetail>
<Version>
<ServiceId>trck</ServiceId>
<Major>12</Major>
<Intermediate>0</Intermediate>
<Minor>0</Minor>
</Version>
<CompletedTrackDetails>
<HighestSeverity>SUCCESS</HighestSeverity>
<Notifications>
<Severity>SUCCESS</Severity>
<Source>trck</Source>
<Code>0</Code>
<Message>Request was successfully processed.</Message>
<LocalizedMessage>Request was successfully processed.</LocalizedMessage>
</Notifications>
<DuplicateWaybill>false</DuplicateWaybill>
<MoreData>false</MoreData>
<TrackDetailsCount>0</TrackDetailsCount>
<TrackDetails>
<Notification>
<Severity>FAILURE</Severity>
<Source>trck</Source>
<Code>9080</Code>
<Message>Sorry, we are unable to process your tracking request.  Please contact Customer Service at 1.800.Go.FedEx(R) 800.463.3339.</Message>
<LocalizedMessage>Sorry, we are unable to process your tracking request.  Please contact Customer Service at 1.800.Go.FedEx(R) 800.463.3339.</LocalizedMessage>
</Notification>
<TrackingNumber>'.$tracking_number.'</TrackingNumber>
<StatusDetail>
<Location>
<Residential>false</Residential>
</Location>
</StatusDetail>
<PackageSequenceNumber>0</PackageSequenceNumber>
<PackageCount>0</PackageCount>
<DeliveryAttempts>0</DeliveryAttempts>
<TotalUniqueAddressCountInConsolidation>0</TotalUniqueAddressCountInConsolidation>
<DeliveryOptionEligibilityDetails>
<Option>INDIRECT_SIGNATURE_RELEASE</Option>
<Eligibility>INELIGIBLE</Eligibility>
</DeliveryOptionEligibilityDetails>
<DeliveryOptionEligibilityDetails>
<Option>REDIRECT_TO_HOLD_AT_LOCATION</Option>
<Eligibility>INELIGIBLE</Eligibility>
</DeliveryOptionEligibilityDetails>
<DeliveryOptionEligibilityDetails>
<Option>REROUTE</Option>
<Eligibility>INELIGIBLE</Eligibility>
</DeliveryOptionEligibilityDetails>
<DeliveryOptionEligibilityDetails>
<Option>RESCHEDULE</Option>
<Eligibility>INELIGIBLE</Eligibility>
</DeliveryOptionEligibilityDetails>
</TrackDetails>
</CompletedTrackDetails>
</TrackReply>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';


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
print 'Tracking: ';
//print (string) $result->SOAPENVBody->RateReply->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
print (string) $result -> SOAPENVBody -> TrackReply -> CompletedTrackDetails -> TrackDetails;
//print (string) $result->SOAPENVBody->StatusDetail;
print '<hr/>';
print_r($result);
?>
</body>
</html>

