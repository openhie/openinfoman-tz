<?php
function initialize_curl() {
	$ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_VERBOSE, true);
   return $ch;
}

function getVIMSFac () {
	$content = file('VIMS_facilities_codes.csv');
	$rows = array_map('str_getcsv', $content,array_fill(0, count($content), ","));
	return $rows;
}

function createCSDFacility($facDetails) {
	$name = $facDetails['facility']['name'];
	$code = $facDetails['facility']['code'];
	$type = $facDetails['facility']['facilityType'];
	$geographicZone = $facDetails['facility']['geographicZone'];
	$gln = $facDetails['facility']['gln'];
	$id = $facDetails['facility']['id'];

	$post = '<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
  					<csd:facility id="'.$id.'">
    					<csd:code>'.$code.'</csd:code>
    					<csd:name>'.$name.'</csd:name>
    					<csd:district>'.$geographicZone.'</csd:district>
							<csd:gln>'.$gln.'</csd:gln>
							<csd:ftype>'.$type.'</csd:ftype>
  					</csd:facility>
					 </csd:requestParams>';
	$ch = initialize_curl();
	$url = "http://localhost:8984/CSD/csr/vims/careServicesRequest/update/urn:openhie.org:openinfoman-tz:facility_create_vims_tz";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
}

function getDetails ($code) {
	$username = "";
	$password = "";
	$ch = initialize_curl();
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	$url = 'https://vimstraining.elmis-dev.org:443/rest-api/facilities/'.$code;
	curl_setopt($ch, CURLOPT_URL, $url);
	$details = curl_exec($ch);
	$details = json_decode($details,true);
	return $details;
}

$facilities = getVIMSFac();
foreach ($facilities as $key => $facility) {
	echo "processing =====>".$key;
	$facDet = getDetails ($facility['0']);
	if(!array_key_exists("facility",$facDet)) {
		echo $key;
		print_r($facility);
		exit;
	}
	createCSDFacility($facDet);
}
?>
