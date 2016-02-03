<?php
	https://52.11.215.89/SVC/VaccinationEvent.svc/GetDefaulters
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	$data = curl_exec($ch);
	$data = json_decode($data, true);
	foreach($data as $id) {
		$client_id=$id["id"];
		$url2 = "https://54.148.48.20:8443/fhir/Patient?identifier=1.3.6.1.4.1.33349.3.1.5.102.3.5.1|$client_id&_format=json";
		curl_setopt($ch, CURLOPT_URL, $url2);
		$data = curl_exec($ch);
	        $data = json_decode($data, true);
		$phone=$data["entry"][0]["content"]["telecom"][0]["value"];
		$phone=str_replace("tel:","",$phone);
		$phone=str_replace(" ","",$phone);
		$phonenumber[]=$phone;
		}

	initiate_flow($phonenumber);
	curl_close($ch);
	function initiate_flow($phone) {
	$phone_numbers="";
        foreach($phone as $telephone) {
		if($phone_numbers=="")
		$phone_numbers="\"".$telephone."\"";
		else
		$phone_numbers=$phone_numbers.",\"".$telephone."\"";
                }
        $ch = curl_init();
	$url = "https://rapidpro.ngrok.com/api/v1/runs.json";
	$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,'{
                                                  "flow": 10,
                                                  "phone": ['.$phone_numbers.'],
                                              }');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                                                   "Content-Type: application/json",
                                                   "Authorization: Token d5d1cc85a578876ea21d125dd7c79000446fe217",
                                                                                //"Expect: 100-continue"
                                                  ));
	curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	curl_setopt($ch, CURLOPT_CERTINFO, true);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec ($ch);
	curl_error($ch);
	curl_close ($ch);
	var_dump($output);
	}
?>
