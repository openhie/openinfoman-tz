<?php
function initialize_curl() {
	$ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_VERBOSE, true);
   return $ch;
}

function getDHIS2Fac () {
	$username = "";
	$password = "";
	$url = "http://41.217.202.50:8080/dhis/api/organisationUnits";
	$ch = initialize_curl();
	$facilities = array();
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	do {
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		$url = false;
		$data = json_decode($data,true);
		$facilities = array_merge($facilities,$data['organisationUnits']);
		if(array_key_exists('nextPage',$data['pager']))
		$url = $data['pager']['nextPage'];
	} while($url);
	return $facilities;
}

function createCSDFacility($facDetails) {
	if(array_key_exists('parent',$facDetails)) {
		if(array_key_exists('id',$facDetails['parent']))
		$pid = $facDetails['parent']['id'];
		else
		$pid = '';
	}
	else
	$pid = '';
	$name = trim($facDetails['name']);
	$post = '<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
  					<csd:facility id="'.$facDetails['id'].'">
    					<csd:hfr id="'.$facDetails['code'].'"/>
    					<csd:name>'.$name.'</csd:name>
    					<csd:parent id="'.$pid.'"/>
  					</csd:facility>
					 </csd:requestParams>';
	$ch = initialize_curl();
	$url = "http://localhost:8984/CSD/csr/test/careServicesRequest/update/urn:openhie.org:openinfoman-tz:facility_create_dhis_tz";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
}

function createCSDOrganization($orgDetails) {
	if(array_key_exists('parent',$orgDetails)) {
		if(array_key_exists('id',$orgDetails['parent']))
		$pid = $orgDetails['parent']['id'];
		else
		$pid = '';
	}
	else
	$pid = '';
	$name = $orgDetails['name'];
	$post = '<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
  					<csd:organization id="'.$orgDetails['id'].'">
    					<csd:name>'.$name.'</csd:name>
    					<csd:parent id="'.$pid.'"/>
  					</csd:organization>
					 </csd:requestParams>';
	$ch = initialize_curl();
	$url = "http://localhost:8984/CSD/csr/test/careServicesRequest/update/urn:openhie.org:openinfoman-tz:organization_create_dhis_tz";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
}

function getDetails ($id) {
	$username = "";
	$password = "";
	$ch = initialize_curl();
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	$url = 'http://41.217.202.50:8080/dhis/api/organisationUnits/'.$id.'?format=json';
	curl_setopt($ch, CURLOPT_URL, $url);
	$details = curl_exec($ch);
	$details = json_decode($details,true);
	return $details;
}

$facilities = getDHIS2Fac();
$processed_orgs = array();
foreach ($facilities as $key => $facility) {
	$facDet = getDetails ($facility['id']);
	createCSDFacility($facDet);
	$distrId = $facDet['parent']['id'];
	$distrDet = getDetails ($distrId);
	if(!in_array($distrId,$processed_orgs)) {
		createCSDOrganization($distrDet);
		$processed_orgs[] = $distrId;
	}
	$regId = $distrDet['parent']['id'];
	$regDet = getDetails ($regId);
	if(!in_array($regId,$processed_orgs)) {
		createCSDOrganization($regDet);
		$processed_orgs[] = $regId;
	}
	$countryId = $regDet['parent']['id'];
	$countryDet = getDetails ($countryId);
	$countryId = trim($countryId);
	if(!in_array($countryId,$processed_orgs)) {
		createCSDOrganization($countryDet);
		$processed_orgs[] = $countryId;
	}
}
?>
