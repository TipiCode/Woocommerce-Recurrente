<?php
/**
* Clase para interactuar con un Checkou de Cobro único dentro de Recurrente
*
* Objeto principal para interactuar con un checkout de Cobro único dentro de recurrente.
*
* @copyright  2024 - tipi(code)
* @since      2.0.0
*/ 
class Single_Checkout {
    private $gateway;
    private $customer_order;
    public $id;
    public $url;
    public $product;

    /**
    * Constructor
    *
    * @param WC_Order  $customer_order  Orden de WooCommerce para procesar los datos del producto.
    * 
    */ 
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
    }

    /**
    * Crea un nuevo Checkout de cobro único
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 2.0.0
    */
    public function create(){
        try{
            $url = 'https://aurora.codingtipi.com/pay/v1/recurrente/checkouts';
            $curl = new Curl(
                $this->gateway->get_option('public_key'), 
                $this->gateway->get_option('secret_key')
            );// Inicializar Curl

            $checkout = $this->get_api_model();//Obtiene objeto en formato JSON como lo requiere Recurrente
            
            $response = $curl->execute_post($url, $checkout);
            $curl->terminate();

            error_log( print_r( $response, true ) );

            $this->id = $response['body']->id;
            $this->product = $response['body']->product;
            $this->url = $response['body']->url;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    /**
    * Elimina un producto de la biblioteca de Recurrente
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 2.0.0
    */
    public function clean(){
        try{
            $url = 'https://aurora.codingtipi.com/pay/v1/recurrente/products/'.$this->id ;
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));// Inicializar Curl

            $response = $curl->execute_delete($url);
            $curl->terminate();

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    /**
    * Obtiene el modelo de un checkout para poder interactual con el API de recurrente
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Objeto para usar con el API de Recurrente
    * @link https://codingtipi.com/project/recurrente
    * @since 2.0.0
    */ 
    private function get_api_model(){
        return Array(
                "number"  => $this->customer_order->get_order_number(),
                "description"  => "Orden número ".$this->customer_order->get_order_number().'. al finalizar tu pago seras redirigido de vuelta al comerció para procesar tu orden.',
                "correlative"  => $this->customer_order->id,
                "amount" => $this->customer_order->order_total,
                "currency"  => $this->customer_order->get_currency(),
                "billing" => Array(
                    "name" => $this->customer_order->billing_first_name,
                    "surname" => $this->customer_order->billing_last_name,
                    "email" => $this->customer_order->billing_email,
                    "phone" => $this->customer_order->billing_phone
                )
        );
    }
}