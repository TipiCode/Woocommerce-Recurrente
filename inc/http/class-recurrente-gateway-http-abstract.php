<?php

/**
 * Payment Gateway class for Recurrente Online
 *
 * @package Abzer
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Recurrente_Gateway_Http_Abstract class.
 */
abstract class Recurrente_Gateway_Http_Abstract {


	/**
	 * Recurrente Order status.
	 *
	 * @var array $order_status
	 */
	protected $order_status;

	/**
	 * Gateway Object
	 *
	 * @var Recurrente_Gateway $gateway
	 */
	protected $gateway;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->order_status = include dirname(__FILE__) . '/../order-status-recurrente.php';
		$this->gateway = Recurrente_Gateway::get_instance();
	}

	/**
	 * Places request to gateway.
	 *
	 * @param  TransferInterface $transfer_object Transafer Factory.
	 * @return array|null
	 * @throws Exception Exception.
	 */
	public function place_request( $requestArr) {
		$this->order_status = include dirname(__FILE__) . '/../order-status-recurrente.php';
		$log['path'] = __METHOD__;
		try {
			$log['response'] = $requestArr;
			$result = $this->post_process($requestArr);
			return $result;
		} catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		} finally {
			$this->gateway->debug($log);
		}
	}
	
	
	/**
	 * Create product to get redirect url
	 *
	 * @param  TransferInterface $transfer_object Transafer Factory.
	 * @return array|null
	 * @throws Exception Exception.
	 */
	public function create_order( $requestArr,) {
		$this->order_status = include dirname(__FILE__) . '/../order-status-recurrente.php';
		$log['path'] = __METHOD__;
		try {
			$log['response'] = "Create url";

			$header  = Array(
				'X-PUBLIC-KEY:' . $this->gateway->get_option('access_key'),
				'X-SECRET-KEY:' . $this->gateway->get_option('secret_key'),
				'Content-type: application/json'
			);

			//Create product
			$item = Array(
				"product" => Array(
					"name"                          => "Order-".$requestArr["orderId"],
					"phone_requirement"             => "none",
					"address_requirement"           => "none",
					"billing_info_requirement"      => "none",
					"cancel_url"                    => site_url() . "/wc-api/recurrenteonline?status=c",
					"success_url"                   => site_url() . "/wc-api/recurrenteonline?status=s",
					"prices_attributes"             => Array(
						"0" => Array(
							"amount_as_decimal" => $requestArr["amount"],
							"currency"          => "GTQ",
							"charge_type"       => "one_time"
						)
					)
				)
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.recurrente.com/api/products");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($item));


			//Create user
			$UserData = Array(
				"email"     => $requestArr["bill_to_email"],
				"full_name" => $requestArr["bill_to_forename"],
			);
			$chUser = curl_init();
			curl_setopt($chUser, CURLOPT_URL,"https://app.recurrente.com/api/users");
			curl_setopt($chUser, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chUser, CURLOPT_HTTPHEADER, $header );
			curl_setopt($chUser, CURLOPT_POSTFIELDS, json_encode($UserData));
			

			// execute!
			$response = json_decode(curl_exec($ch));
			$responseUser = json_decode(curl_exec($chUser));


			//Create checkout width priceId & UserId
			$CheckoutData = Array(
				"items"     => Array(
					"0" => Array(
						"price_id" => $response->prices[0]->id
					)
				),
				"user_id" => $responseUser->id,
			);
			$chCheckout = curl_init();
			curl_setopt($chCheckout, CURLOPT_URL,"https://app.recurrente.com/api/checkouts");
			curl_setopt($chCheckout, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chCheckout, CURLOPT_HTTPHEADER, $header );
			curl_setopt($chCheckout, CURLOPT_POSTFIELDS, json_encode($CheckoutData));

			$responseCheckout = json_decode(curl_exec($chCheckout));

			$res = Array(
				"id"              => $response->id,
				"storefront_link" => $responseCheckout->checkout_url
			);

			// close the connection, release resources used
			curl_close($ch);
			curl_close($chUser);
			curl_close($chCheckout);

			return $res;
		} catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
		} finally {
			$this->gateway->debug($log);
		}
	}

	/**
	 * Processing of API request body
	 *
	 * @param  array $data Data.
	 * @return string|array
	 */
	abstract protected function pre_process( array $data);

	/**
	 * Processing of API response
	 *
	 * @param  array $response Response.
	 * @return array|null
	 */
	abstract protected function post_process( $response);
}
