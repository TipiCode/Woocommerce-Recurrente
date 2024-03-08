<?php
/**
* Clase para interactuar con CURL dentro de PHP
*
* Objeto utilizado para cada llamada del API
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class Curl{
    private $ch;
    private $header;

    /**
    * Constructor
    *
    * @param string $public_key  Clave pública que provee Recurrente.
    * @param string $secret_key Clave secreta que provee Recurrente.
    * 
    */
    function __construct($public_key, $secret_key) {
        $this->ch = curl_init();
        $this->header  = Array(
            'X-PUBLIC-KEY:' . $public_key,
            'X-SECRET-KEY:' . $secret_key,
            'X-ORIGIN:' . get_site_url('url'),
            'X-STORE:'.get_bloginfo('name'),
            'Content-type: application/json'
          );
    }

    /**
    * Procesa el metodo de POST
    * 
    * @param string   $url  Url donde se llevara acabo la llamada.
    * @param string   $body Objeto para colocar en el cuerpo del Request.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Arreglo que contiene el código HTTP de la respuesta y el cuerpo de la respuesta.
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */
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

    /**
    * Procesa el metodo de DELETE
    * 
    * @param string   $url  Url donde se llevara acabo la llamada.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Arreglo que contiene el código HTTP de la respuesta y el cuerpo de la respuesta.
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */
    function execute_delete($url){
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header );

        $response = curl_exec($this->ch);
        $response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        return Array(
            "code" => $response_code,
            "body" => $response
        );
    }

    /**
    * Cierra la conexión de CURL
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */
    function terminate(){
        curl_close($this->ch);
    }
}