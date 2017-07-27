<?php

function foo (&$array, $var1, $key, $pswd, $acct, $meter)
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
			               <v12:Key>'.$key.'</v12:Key>
			               <v12:Password>'.$pswd.'</v12:Password>
			            </v12:UserCredential>
			         </v12:WebAuthenticationDetail>
			         <v12:ClientDetail>
			            <v12:AccountNumber>'.$acct.'</v12:AccountNumber>
			            <v12:MeterNumber>'.$meter.'</v12:MeterNumber>
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

			$dd = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime;

			$pickup = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->DatesOrTimes[1]->DateOrTimestamp;

			$notification = $result->SOAPENVBody->TrackReply->CompletedTrackDetails->TrackDetails->Notification->Severity;
			
			if($notification == 'ERROR')
			{
				$shp_bol = $trkNum;
				$shp_del_dt = 'Not Available';
				$shp_pickup_dt = 'Not Available';
				$shp_sts = 'Not Available';
				$shp_eta_dt = 'Not Available';
				$shp_is_complete = null;

			   insertInto($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
				/*
				print '<pre>';
				print 'Tracking Number: '.$shp_bol.'<br>';
				print 'Status: '.$shp_sts.'<br>';
				print 'Delivery Date: '.$shp_del_dt.'<br>';
				print 'Estimated Delivery Date:  '.$shp_eta_dt.'<br>';
				print '<hr/>';
				print_r($result);
				*/
			}
			elseif ($status == 'Delivered') 
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = $dd;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 1;

			   insertInto($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);
			   
			   /*
				print '<pre>';
				print 'Tracking Number: '.$shp_bol.'<br>';
				print 'Status: '.$shp_sts.'<br>';
				print 'Delivery Date: '.$shp_del_dt.'<br>';
				print 'Estimated Delivery Date:  '.$shp_eta_dt.'<br>';
				print '<hr/>';
				print_r($result);
				*/
			}
			else
			{
			   $shp_bol = $tracking_number;
			   $shp_del_dt = null;
			   $shp_pickup_dt = $pickup;
			   $shp_sts = $status;
			   $shp_eta_dt = $eDT;
			   $shp_is_complete = 0;

			   insertInto($shp_bol, $shp_del_dt, $shp_pickup_dt, $shp_sts, $shp_eta_dt, $shp_is_complete);

			   /*
				print '<pre>';
				print 'Tracking Number: '.$shp_bol.'<br>';
				print 'Status: '.$shp_sts.'<br>';
				print 'Delivery Date: '.$shp_del_dt.'<br>';
				print 'Estimated Delivery Date:  '.$shp_eta_dt.'<br>';
				print '<hr/>';
				print_r($result);
				*/
			}

		}
}

function insertInto($var1, $var2, $var3, $var4, $var5, $var6)
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
		die(print_r(sqlsrv_errors(), true));
	}

	sqlsrv_free_stmt($stmt);

}
?>