<?php
//$_REQUEST["contact_details"]="Add 0683088392 Ally Shaban";
$contact_details=strtolower($_REQUEST["contact_details"]);
$contact_details=str_replace("add","",$contact_details);
$contact_details=str_replace("update","",$contact_details);
$contact_details=str_replace("delete","",$contact_details);
if(!$contact_details) {
header('Content-Type: application/json');
echo '{
       "response":"Error:No Phone Number Specified,make sure you dont have space between numbers",
       "validity":"not_valid"
      }';
return false;
}
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
//verifying phone
$phone_back=str_replace("tel:","",$phone);
$phone_back=str_replace("+","",$phone_back);
$phone_back=str_replace("\"","",$phone_back);
if(strlen($phone_back)!=10) {
header('Content-Type: application/json');
echo '{
	"response":"Error:Invalid Phone Number,make sure you dont have space between numbers",
	"validity":"not_valid"
      }';
return false;
}

if(!ctype_digit($phone_back)) {
header('Content-Type: application/json');
echo '{
	"response":"Error:Invalid Phone Number,Make sure The Phone Has Numbers Only",
	"validity":"not_valid"
      }';
return false;
}

header('Content-Type: application/json');
echo '{
        "validity":"valid"
      }';
?>
