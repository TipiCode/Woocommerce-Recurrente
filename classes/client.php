<?php
/**
* Clase para interactuar con los Clientes de Recurrente
*
* Objeto principal para guardar la información de usuarios.
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class Client {
    private $gateway;
    private $customer_order;
    public $id;

    /**
    * Constructor
    *
    * @param WC_Order   $customer_order  Orden de WooCommerce para procesar los datos del cliente.
    * 
    */ 
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
    }

    /**
    * Crea un nuevo Cliente
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/users';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));// Inicializar Curl

            $user = $this->get_api_model();//Obtiene objeto en formato JSON como lo requiere Recurrente

            $response = $curl->execute_post($url, $user);
            $curl->terminate();

            $this->id = $response['body']->id;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    /**
    * Obtiene el modelo de Cliente para uso con el API de Recurrente
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Objeto para usar con el API de Recurrente
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function get_api_model(){
        return Array(
            "email"     => $this->customer_order->billing_email, //Se utiliza el billing email para el email del cliente.
            "full_name" => $this->customer_order->billing_first_name.' '.$this->customer_order->billing_last_name, //Se requieren ambos first y last name del billign para el nombre del cliente.
        );
    }
}