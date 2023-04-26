<?php

/**
 * Payment Gateway class for CyberSource Online
 *
 * @package Abzer
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cybersource_Gateway_Payment class.
 */
class Recurrente_Gateway_Payment {


	/**
	 * recurrente Online states
	 */
	const RECURRENTE_STARTED = 'STARTED';
	const RECURRENTE_ACCEPT = 'ACCEPT';
	const RECURRENTE_CANCEL = 'CANCEL';
	const RECURRENTE_DECLINE = 'DECLINE';

	/**
	 * Order Status Variable
	 *
	 * @var string Order Status.
	 */
	protected $order_status;

	/**
	 * recurrente State Variable
	 *
	 * @var string recurrente Online state
	 */
	protected $recurrente_state;

	/**
	 * Gateway
	 *
	 * @var Recurrente_Gateway $gateway
	 */
	protected $gateway;

	/**
	 * Status
	 *
	 * @var string status
	 */
	protected $status_set_to;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->order_status = include dirname(__FILE__) . '/order-status-recurrente.php';
		$this->gateway = Recurrente_Gateway::get_instance();
	}

	/**
	 * Execute action.
	 *
	 * @param string $order_ref Order reference.
	 */
	//public function execute(string $order_ref) {
	public function execute( $order, $status ) {
		global $woocommerce;
		$log['path'] = __METHOD__;
		$redirect_url = $woocommerce->cart->get_checkout_url();

		$log['is_valid_ref'] = true;

		$order_item = wc_get_order($order);
		$orderData = $this->process_order($status, $order_item);
		$redirect_url = $orderData->get_checkout_order_received_url();

		$log['redirected_to'] = $redirect_url;
		$this->gateway->debug($log);
		wp_safe_redirect($redirect_url);
		exit();
	}

	/**
	 * Process Order.
	 *
	 * @param  array  $payment_result Payment Results.
	 * @param  object $order_item Order Item.
	 * @return object
	 */
	public function process_order( $status, $order_item) {

		include_once dirname(__FILE__) . '/config/class-recurrente-gateway-config.php';
		include_once dirname(__FILE__) . '/validator/class-recurrente-gateway-validator-response.php';

		$log['path'] = __METHOD__;
		$order = $order_item;

		if ($order && $order->get_id()) {
			if ($status == "c") {
				$order->update_status($this->order_status[5]['status'], 'The transaction has been failed.');
				$order->update_status('failed');
				$this->status_set_to = $this->order_status[5]['status'];
				$message = "Cancelado";
				$order->add_order_note($message);
			} else {
				$order->update_status($this->order_status[1]['status']);
				$this->status_set_to = $this->order_status[1]['status'];
				$order->add_order_note($message);
				$log['msg'] = $message;
				$this->gateway->debug($log);
			}
			$this->gateway->debug($log);
			return $order;
		} else {
			return new WP_Error('recurrente_error', 'Order Not Found');
		}
	}

	public function deleteProduct($prodId) {
		$header  = Array(
			'X-PUBLIC-KEY:' . $this->gateway->get_option('access_key'),
			'X-SECRET-KEY:' . $this->gateway->get_option('secret_key'),
			'Content-type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://app.recurrente.com/api/products/{$prodId}");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header );

		$result = false;

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($httpCode == "200") $result = true;
		curl_close($ch);

		return($result);
	}
}
