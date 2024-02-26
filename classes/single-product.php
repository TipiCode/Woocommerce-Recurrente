<?php
/**
* Clase para interactuar con un Producto de Cobro único dentro de Recurrente
*
* Objeto principal para interactuar con un producto de Cobro único dentro de recurrente.
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class Single_Product {
    private $gateway;
    private $customer_order;
    public $id;

    /**
    * Constructor
    *
    * @param WC_Order   $customer_order  Orden de WooCommerce para procesar los datos del producto.
    * 
    */ 
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
    }

    /**
    * Crea un nuevo Producto de cobro único
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */
    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/products';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));// Inicializar Curl

            $product = $this->get_api_model();//Obtiene objeto en formato JSON como lo requiere Recurrente

            $response = $curl->execute_post($url, $product);
            $curl->terminate();

            $this->id = $response['body']->prices[0]->id;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    /**
    * Obtiene el modelo de Pruducto para uso con el API de Recurrente
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Objeto para usar con el API de Recurrente
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function get_api_model(){
        return Array(
            "product" => Array(
                "name"                          => $this->customer_order->get_order_number(),
                "phone_requirement"             => "none",
                "address_requirement"           => "none",
                "billing_info_requirement"      => "none",
                "cancel_url"                    => site_url() . "?wc-api=recurrente&status=0&order=".$this->customer_order->id,
                "success_url"                   => site_url() . "?wc-api=recurrente&status=1&order=".$this->customer_order->id,
                "prices_attributes"             => Array(
                    "0" => Array(
                        "amount_as_decimal" => $this->customer_order->order_total,
                        "currency"          => $this->customer_order->get_currency(),
                        "charge_type"       => "one_time"
                    )
                )
            )
        );
    }
}