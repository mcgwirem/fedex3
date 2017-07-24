<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 6.0.0

echo '<p>Hello World</p>';
require_once('/fedex-common.php5');



//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "/TrackService_v12.wsdl";

echo '<p> before key </p>';

$key = getProperty('key');

$trackingNum = 0;

//$key = "foo";

echo '<p> after getproperty</p>';

echo $key;

echo '<p> after key </p>';

ini_set("soap.wsdl_cache_enabled", "0");

echo '<p> after init </p>';

echo $path_to_wsdl;

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

if (is_soap_fault($client)) {
	echo '<p>error in soap</p>';
}

echo $client;

echo '<p> after $client </p>';

$request['WebAuthenticationDetail'] = array(
	'ParentCredential' => array(
		'Key' => getProperty('parentkey'), 
		'Password' => getProperty('parentpassword')
	),
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);

echo '<p> after request </p>';

echo $request;

/*
$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'trck', 
	'Major' => '12', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['SelectionDetails'] = array(
	'PackageIdentifier' => array(
		
		'Type' => 'CUSTOMER_REFERENCE',
		'Value' => getProperty('customerreference') // Replace with a valid customer reference
	),
	//'ShipDateRangeBegin' => getProperty('begindate'),
	//'ShipDateRangeEnd' => getProperty('enddate'),
	'ShipmentAccountNumber' => getProperty('trackaccount') // Replace with account used for shipment
);



try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->track($request);

    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
		if($response->HighestSeverity != 'SUCCESS'){
			echo '<table border="1">';
			echo '<tr><th>Track Reply</th><th>&nbsp;</th></tr>';
			trackDetails($response->Notifications, '');
			echo '</table>';
		}else{
	    	if ($response->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
				echo '<table border="1">';
			    echo '<tr><th>Shipment Level Tracking Details</th><th>&nbsp;</th></tr>';
			    trackDetails($response->CompletedTrackDetails, '');
				echo '</table>';
			}else{
				echo '<table border="1">';
			    echo '<tr><th>Package Level Tracking Details</th><th>&nbsp;</th></tr>';
			    trackDetails($response->CompletedTrackDetails->TrackDetails, '');
				echo '</table>';
			}
		}
        printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}*/
?>
</body>
</html>