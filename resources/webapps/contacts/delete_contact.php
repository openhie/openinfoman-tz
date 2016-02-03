<?php
$contact_details=strtolower($_REQUEST["contact_details"]);
$contact_details=str_replace("delete","",$contact_details);
$phone=trim($contact_details);
$phone_back=explode(" ",$phone);

if(count($phone_back)>1 or $phone_back=="") {
header('Content-Type: application/json');
echo '{"response":"Error:Invalid Delete Format.The format Should Be delete 07XXXXXXXX"}';
return false;
}

$phone='tel:'.$phone;
$url = "http://54.148.103.198:8000/api/v1/contacts.json?urns=$phone";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                                                   "Content-Type: application/json",
                                                   "Authorization: Token b9d090b6890b92bf43d254536c2b1a29308f32d4",
                                                  ));
$output = curl_exec ($ch);
header('Content-Type: application/json');
if(curl_errno($ch))
echo '{"response":"An Error Occured While Deleting Contact,Try Later"}';
else
echo '{"response":"Contact Deleted Successfully"}';
curl_close ($ch);
?>
