<?php
get_orgs();
function get_orgs() {
	$ch = initialize_curl();
	$url = "http://resourcemap.instedd.org/en/collections/1652/fields.json";
	$url1 = "http://resourcemap.instedd.org/api/collections/1652.json?Admin_div[under]=TZ.NT.AS.KR.4.10&human=true";
	$url1 = "http://resourcemap.instedd.org/api/collections/1652.json?human=true";
	$username = "allyshaban5@gmail.com";
	$password = "Mw@n@m1gu";
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
  $data = json_decode($data,true);
   process_array($data["config"]["hierarchy"],"Top");
}
function initialize_curl() {
	$ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_VERBOSE, true);
   return $ch;
}

function process_array($array,$parent) {
	foreach($array as $array1) {
		if(array_key_exists("sub",$array1)) {
			process_array($array1["sub"],$array1["id"]);
		}
		echo "Parent => ".$parent." ";
		echo "Name => ".$array1["name"]." ";
		echo "ID => ".$array1["id"]."\n";
		$post = '<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
					<csd:organization id="'.$array1["id"].'">
					<csd:name>'.$array1["name"].'</csd:name>
					<csd:parent id="'.$parent.'"/>
					</csd:organization>
					</csd:requestParams>';
		$ch = initialize_curl();
		$url = "http://localhost:8984/CSD/csr/hfr/careServicesRequest/update/urn:openhie.org:openinfoman-tz:organization_create_hfr_tz";
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		curl_close($ch);
	}
}
?>
