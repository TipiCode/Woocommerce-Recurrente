<?php
class Checkout {
    private $gateway;
    private $product_id;
    private $client_id;
    public $id;
    public $url;
    function __construct($product_id, $client_id) {
        $this->gateway = Recurrente::get_instance();
        $this->product_id = $product_id;
        $this->client_id = $client_id;
    }

    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/checkouts';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));

            $checkout = $this->get_api_model();

            $response = $curl->execute_post($url, $checkout);
            $curl->terminate();

            $this->id = $response['body']->id;
            $this->url = $response['body']->checkout_url;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

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