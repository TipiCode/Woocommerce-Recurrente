<?php
/**
* Clase para interactuar con los Checkouts de Recurrente
*
* Objeto principal para realizar cobros dentro de la plataforma
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class Checkout {
    private $gateway;
    private $product_id;
    private $client_id;
    public $id;
    public $url;

    /**
    * Constructor
    *
    * @param string   $product_id  Id del producto para cobrar dentro del checkout.
    * @param string $client_id Id del cliente, esto es utilizado para auto llenar los datos del cliente.
    * 
    */ 
    function __construct($product_id, $client_id) {
        $this->gateway = Recurrente::get_instance();
        $this->product_id = $product_id;
        $this->client_id = $client_id;
    }

    /**
    * Crea un nuevo Checkout
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/checkouts';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));// Inicializar Curl

            $checkout = $this->get_api_model();//Obtiene objeto en formato JSON como lo requiere Recurrente

            $response = $curl->execute_post($url, $checkout);
            $curl->terminate();

            $this->id = $response['body']->id;
            $this->url = $response['body']->checkout_url;

            return $response['code'];// 201 si se creo correctamente

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    /**
    * Obtiene el modelo de Checkout para uso con el API de Recurrente
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Objeto para usar con el API de Recurrente
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function get_api_model(){
        return Array(
            "items"     => Array(
                "0" => Array(
                    "price_id" => $this->product_id
                )
            ),
            "user_id" => $this->client_id,
        );
    }
}