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
		global  $woocommerce;
   		$myCurrency = get_option('woocommerce_currency');
		$this->order_status = include dirname(__FILE__) . '/../order-status-recurrente.php';
		$log['path'] = __METHOD__;
		try {
			$log['response'] = "Create url";

			$header  = Array(
				'X-PUBLIC-KEY:' . $this->gateway->get_option('access_key'),
				'X-SECRET-KEY:' . $this->gateway->get_option('secret_key'),
				'Content-type: application/json'
			);

			//Validate site currency
			$currency = "GTQ";
			if( $myCurrency == "USD") {
				$currency = "USD";
			}

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
							"currency"          => $currency,
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
			// execute!
			$response = json_decode(curl_exec($ch));
			$resCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($resCode != 201){
				$log['CreateUrl_responseCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$log['CreateUrl_responseMsg'] = $response;
			}
			curl_close($ch);

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
			$responseUser = json_decode(curl_exec($chUser));
			$userResCode = curl_getinfo($chUser, CURLINFO_HTTP_CODE);
			if($resCode != 201){
				$log['CreateUser_responseCode'] = curl_getinfo($chUser, CURLINFO_HTTP_CODE);
				$log['CreateUser_responseMsg'] = $responseUser;
			}
			curl_close($chUser);

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
			$checkoutResCode = curl_getinfo($chCheckout, CURLINFO_HTTP_CODE);
			if($checkoutResCode != 201){
				$log['Checkout_responseCode'] = curl_getinfo($chCheckout, CURLINFO_HTTP_CODE);
				$log['Checkout_responseMsg'] = $checkoutResCode;
			}
			// close the connection, release resources used
			curl_close($chCheckout);

			$res = Array(
				"id"              => $response->id,
				"storefront_link" => $responseCheckout->checkout_url
			);

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
