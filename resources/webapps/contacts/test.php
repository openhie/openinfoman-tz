<?php
$url = "http://54.148.103.198:8000/api/v1/runs.json?contact=c7910146-194a-4be5-8f50-9e817db574d2";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                                                   "Content-Type: application/json",
                                                   "Authorization: Token b9d090b6890b92bf43d254536c2b1a29308f32d4",
                                                  ));
$output = curl_exec ($ch);
curl_close ($ch);
echo $output;
?>
