<?php
class Single_Product {
    private $gateway;
    private $customer_order;
    public $id;
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
    }

    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/products';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));

            $product = $this->get_api_model();

            $response = $curl->execute_post($url, $product);
            $curl->terminate();

            $this->id = $response['body']->prices[0]->id;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    private function get_api_model(){
        return Array(
            "product" => Array(
                "name"                          => $this->customer_order->get_order_number(),
                "phone_requirement"             => "none",
                "address_requirement"           => "none",
                "billing_info_requirement"      => "none",
                "cancel_url"                    => site_url() . "/wc-api/recurrenteonline?status=c",
                "success_url"                   => site_url() . "/wc-api/recurrenteonline?status=s",
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