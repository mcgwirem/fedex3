<?php
/*******************************************FUNCTIONS********************************************/

/************************UPDATE FUNCTION**************************/
function updatedDB ($var1, $var2, $var3, $var4, $var5, $var6)
{
	//connect to the database
	try 
	{
		$conn = new PDO(db_pdohost, db_user, db_pswd);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	//return error if something goes wrong
	catch (PDOException $e) 
	{
		die(print_r($e->getMessage()));
	}

	//prepare update statment for query
	$stmtU = $conn->prepare('UPDATE SHIP_FedEx SET SHP_DEL_DT = ?, SHP_PICKUP_DT = ?, SHP_STS = ?, SHP_ETA_DT = ?, SHP_IS_COMPLETE = ? WHERE SHP_BOL = ?');
	//create an array for the variables to be passed into the statement
	$stmtU->execute(array($var2, $var3, $var4, $var5, $var6, $var1));
	//check if the query was a success
	if ($stmtU->rowCount())
	{
		echo 'success';
	}
	else
	{
		echo 'failure';
	}
	$stmtU = null;
	$conn = null;	
}

/************************STRING TO DATE FUNCTION**************************/
function stringToDate($var)
{
	//store the new date format in this variable
	$newFormat = date('Y-m-d', strtotime($var));
	//return the new format
	return $newFormat;
}

/********************************* MAIN FUNCTION **********************************************/
function foo (&$array, $var1)
{
	//for loop to go through the array
	for ($i=0; $i < count($array); $i++) 
		{
			//store the tracking number in this variable
			$trkNum = $array[$i][$var1];
			
			//xml to pass to fedex
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

			//store status of shipment
			$status = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->Description;
			
			//store estimated delivery time
			$eDT = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[0]->DateOrTimestamp;

			//reformat estimated delivery time
			$eDT = stringToDate($eDT);

			//store delivery date
			$dd = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime;

			//reforat delivery date
			$dd = stringToDate($dd);

			//store pickup date
			$pickup = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[1]->DateOrTimestamp;

			//reformat pickup date
			$pickup = stringToDate($pickup);

			//store notification
			$notification = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->Notification->Severity;
			
			//if notification is error then tracking number is not available
			//return the following to the update query
			if($notification == 'ERROR')
			{
				$shp_bol = $trkNum;
				$shp_del_dt = null;
				$shp_pickup_dt = null;
				$shp_sts = 'Not Available';
				$shp_eta_dt = null;
				$shp_is_complete = 5;

				//call update query function
				updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
			}
			//if status is delivered then return the following to update query
			elseif ($status == 'Delivered') 
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = $dd;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 1;

			   //call update query function
			   updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
			}
			//if package is In Transit or not Delivered 
			//then return the following to the database
			else
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = null;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 0;

			   //call update query function
			   updatedDB($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);

			}

		}
}

?>