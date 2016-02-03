<?php
$phone='tel:079695632';
$url = "http://54.229.198.184:8081/api/v1/contacts.json?urns=$phone";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                                                   "Content-Type: application/json",
                                                   "Authorization: Token 0d7a400d8d2d92db321ee5ee716d20c0a724844d",
                                                  ));
$output = curl_exec ($ch);
print_r($output);
header('Content-Type: application/json');
if(curl_errno($ch))
echo '{"response":"An Error Occured While Deleting Contact,Try Later"}';
else
echo '{"response":"Contact Deleted Successfully"}';
curl_close ($ch);

?>
