<?php
/**
* Clase para interactuar con un Checkou de Cobro único dentro de Recurrente
*
* Objeto principal para interactuar con un checkout de Cobro único dentro de recurrente.
*
* @copyright  2024 - tipi(code)
* @since      2.0.1
*/ 
class Single_Checkout {
    private $gateway;
    private $customer_order;
    private $curl;
    public $id;
    public $url;
    public $product;
    public $code;

    /**
    * Constructor
    *
    * @param WC_Order  $customer_order  Orden de WooCommerce para procesar los datos del producto.
    * 
    */ 
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
        $this->curl = null;
    }

    /**
    * Obtiene una instancia de Curl
    */
    private function get_curl() {
        if ($this->curl === null) {
            $token = get_option('recurrente_api_token');
            $this->curl = new Curl($token);
        }
        return $this->curl;
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
        try {
            $url = 'https://aurora.codingtipi.com/pay/v2/recurrente/checkouts/hosted/single';
            //$url = 'http://localhost:8080/api/checkouts/';
            $curl = $this->get_curl();
            $checkout = $this->get_api_model();
            $response = $curl->execute_post($url, $checkout);
            
            $this->code = $response['code'];
            if($this->code == 201){
                $this->id = $response['body']->id;
                $this->url = $response['body']->url;
                return true;
            } else {
                return $response['body']->message;
            }
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
        try {
            $url = 'https://aurora.codingtipi.com/pay/v2/recurrente/products/'.$this->id;
            $curl = $this->get_curl();
            $response = $curl->execute_delete($url);
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
    * @since 2.0.1
    */ 
    private function get_api_model(){
        $installments = !empty( $this->gateway->get_option('installments')) ? str_replace(' Meses', '', join(',', $this->gateway->get_option('installments'))) : '';
        $transfers = $this->gateway->get_option('allow_transfer') == 'yes' ? true : false;

        return Array(
                "number"  => $this->customer_order->get_order_number(),
                "description"  => "Orden número ".$this->customer_order->get_order_number().'. al finalizar tu pago seras redirigido de vuelta al comerció para procesar tu orden.',
                "correlative"  => $this->customer_order->get_id(),
                "amount" => $this->customer_order->get_total(),
                "currency"  => $this->customer_order->get_currency(),
                "allowTransfer"  => $transfers,
                "installments"  => $installments,
                "billing" => Array(
                    "name" => $this->customer_order->get_billing_first_name(),
                    "surname" => $this->customer_order->get_billing_last_name(),
                    "email" => $this->customer_order->get_billing_email(),
                    "phone" => $this->customer_order->get_billing_phone()
                )
        );
    }
}