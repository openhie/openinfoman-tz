<?php
error_log(print_r($_REQUEST,true));
require_once('config.php');
require_once('lib/PixSubmitNewBirthRegistration.php');
if (! isset($pix_url)) {
    error_log( "Config \$pix_url not set in config.php");
    exit(1);
}
if (! isset($pix_client_id)) {
    error_log("Config \$pix_client_id not set in config.php");
    exit(1);
} 

$pix = new PixSumbitNewBirthRegistration($pix_url,$pix_client_id);

$pix->cert_private_key = 'privateKey.pem';
$pix->cert_client = 'clientCert.pem';
$pix->pass = 'client';
$pix->ua ='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

$submission = $pix->create_submission($_REQUEST);

if ($submission) {
    error_log( "Sending $submission");
    if (($pix->send($submission))) {
	error_log( "Sent:\n$submission\n{$pix->output}");
    } else {
	error_log( "Could not send:\n$submission");
    }
} else {
    error_log( "Invalid submission");
}



