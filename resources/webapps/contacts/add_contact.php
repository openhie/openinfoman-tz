<?php
$orgid=$_REQUEST["orgid"];
$orgcode=$_REQUEST["orgcode"];
$contact_details=strtolower($_REQUEST["contact_details"]);
$village=$_REQUEST["village"];
$contact_details=str_replace("add","",$contact_details);
$contact_details=trim($contact_details);
$contact_details=explode(" ",$contact_details);

$counter=0;
foreach ($contact_details as $detail) {
	if($counter==0)
	$phone='"tel:'.$detail.'"';
	else
	$name=$name." ".$detail;
	$counter++;
	}
$name=trim($name);
$name=ucwords($name);
$name='"'.$name.'"';

add_contact($orgid,$orgcode,$village,$phone,$name);
function add_contact($orgid,$orgcode,$village,$phone,$name) {
$ch=initialize_curl();
        $url = "http://54.148.103.198:8000/api/v1/contacts.json";
	curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                                                   "Content-Type: application/json",
                                                   "Authorization: Token b9d090b6890b92bf43d254536c2b1a29308f32d4",
                                                  ));
        curl_setopt($ch, CURLOPT_POSTFIELDS,'{
						    "name":'.$name.',
						    "groups": [
    						    "Village Executive Officers"],
						    "urns":['.$phone.'],
						    "fields": {
							  "Village Select Orgid":"'.$orgid.'",
                                                          "Village Select OrgName":"'.$village.'",
                                                          "org code":"'.$orgcode.'"
							      }
						}');

         $output = curl_exec ($ch);
	$url = "http://54.148.103.198:8000/api/v1/broadcasts.json";
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POSTFIELDS,'{ "urns": ['.$phone.'], "text": "You have been registered into the TIIS,To register a new birth sms birth G DD MM MOTHERNAME MOTHERNAME. Where G-Gender (K for Female,M for Male),DD and MM are two digits date and month respectively." }');
	$output = curl_exec ($ch);
	if(curl_errno($ch))
	echo '{"response":"An Error Occured While Adding Contact,Try Later"}';
	else
	echo '{"response":"Contact Added Successfully"}';
	curl_close ($ch);
}

function initialize_curl() {
        $ch = curl_init();
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return $ch;
}
?>
