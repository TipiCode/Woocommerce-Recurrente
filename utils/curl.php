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
    private $token;
    private $debug_enabled;

    /**
    * Constructor
    *
    * @param string $token  Token de autenticación que provee Recurrente.
    * 
    */
    function __construct($token) {
        $this->ch = curl_init();
        $this->header  = Array(
            'X-TOKEN:' . $token,
            'X-ORIGIN:' . get_site_url('url'),
            'X-STORE:'.get_bloginfo('name'),
            'Content-type: application/json'
          );
        $this->token = $token;
        $this->debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
        error_log('Recurrente Debug: Inicializando Curl con token: ' . substr($token, 0, 10) . '...');
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
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Ejecutando POST a: ' . $url);
            error_log('Recurrente Debug: Datos enviados: ' . json_encode($body));
        }

        try {
            
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header );
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($body));
            $response = curl_exec($this->ch);
            $response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            
            if(curl_errno($this->ch)){
                $error = curl_error($this->ch);
                throw new Exception($error);
            }
            
            if ($this->debug_enabled) {
                error_log('Recurrente Debug: Código de respuesta: ' . $response_code);
                error_log('Recurrente Debug: Cuerpo de la respuesta: ' . json_encode($response));
            }
            
            return Array(
                "code" => $response_code,
                "body" => json_decode($response)
            );
        } catch (Exception $e) {
            throw $e;
        }
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