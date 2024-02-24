<?php
class Client {
    private $gateway;
    private $customer_order;
    public $id;
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
    }

    public function create(){
        try{
            $url = 'https://app.recurrente.com/api/users';
            $curl = new Curl($this->gateway->get_option('public_key'), $this->gateway->get_option('secret_key'));

            $user = $this->get_api_model();

            $response = $curl->execute_post($url, $user);
            $curl->terminate();

            $this->id = $response['body']->id;

            return $response['code'];

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		}
    }

    private function get_api_model(){
        return Array(
            "email"     => $this->customer_order->billing_email,
            "full_name" => $this->customer_order->billing_first_name.' '.$this->customer_order->billing_last_name,
        );
    }
}