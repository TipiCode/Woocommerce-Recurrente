<?php
class Curl{
    private $ch;
    private $header;
    function __construct($public_key, $secret_key) {
        $this->ch = curl_init();
        $this->header  = Array(
            'X-PUBLIC-KEY:' . $public_key,
            'X-SECRET-KEY:' . $secret_key,
            'Content-type: application/json'
          );
    }

    function execute_post($url, $body){
        curl_setopt($this->ch, CURLOPT_URL, $url);
	    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header );
	    curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($body));
	    $response = json_decode(curl_exec($this->ch));
	    $response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        return Array(
            "code" => $response_code,
            "body" => $response
        );
    }

    function terminate(){
        curl_close($this->ch);
    }
}