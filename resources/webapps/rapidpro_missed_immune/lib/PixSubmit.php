<?php


class PixSumbit {
    var $cert_private_key;
    var $cert_client;
    var $pix_url;
    var $pass;
    var $ch;
    var $pix_client_id;
    var $ua;
    var $error;
    var $errno;
    var $output;


    public function __construct($pix_url,$pix_client_id) {
	$this->pix_url = $pix_url;
	$this->pix_client_id = $pix_client_id;

    }

    function send($content) {
	$ch = curl_init();

	
	curl_setopt($ch, CURLOPT_URL,$this->pix_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	if ($this->cert_private_key ) {
	    curl_setopt($ch, CURLOPT_SSLKEY,$this->cert_private_key);
	    curl_setopt($ch, CURLOPT_SSLKEYTYPE,"PEM"); 
	}
	if( $this->cert_client) {
	    curl_setopt($ch, CURLOPT_SSLCERT, $this->cert_client);
	    curl_setopt($ch, CURLOPT_SSLCERTTYPE,"PEM"); 
	}
	if ($this->pass) {
	    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->pass);
	    curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->pass);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
			"Content-Type: application/soap+xml; charset=utf-8",
			"Expect: 100-continue"
			));
	curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
	curl_setopt($ch, CURLOPT_CERTINFO, true);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$this->errno = curl_errno($ch);
	$this->output = curl_exec ($ch);
	$this->error = curl_error($ch);
	curl_close ($ch);
	return  ($this->errno == 0);
    }

}
