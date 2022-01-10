<?php

/**
 * Payment Gateway class for Recurrente Online
 *
 * @package Abzer
 */
if (!defined('ABSPATH')) {
	exit;
}
require_once 'class-recurrente-gateway-request-abstract.php';

/**
 * Recurrente_Gateway_Request_Sale class.
 */
class Recurrente_Gateway_Request_Sale extends Recurrente_Gateway_Request_Abstract {


	/**
	 * Builds sale request array
	 *
	 * @param  array $order Order.
	 * @return array
	 */
	public function get_build_array( $order) {

		$data = array(
			'transaction_type' => 'sale',
			'orderId' => $order->get_id(),
			'amount' => number_format($order->get_total(), 2, '.', ''),
			'currency' => $order->get_currency(),
			'bill_to_forename' => $order->get_billing_first_name(),
			// 'bill_to_surname' => $order->get_billing_last_name(),
			'bill_to_email' => $order->get_billing_email(),
			// 'bill_to_address_line1' => $order->get_billing_address_1(),
			// 'bill_to_address_line2' => $order->get_billing_address_1(),
			// 'bill_to_address_city' => $order->get_billing_city(),
			// 'bill_to_address_postal_code' => $order->get_billing_postcode(),
			// 'bill_to_address_state' => $order->get_billing_state(),
			// 'bill_to_address_country' => $order->get_billing_country(),
			'reference_number' => time(),
			'signed_date_time' => gmdate('Y-m-d\TH:i:s\Z'),
			'locale' => 'en',
			'transaction_uuid' => uniqid(),
			'unsigned_field_names' => '',
			'signed_field_names' => '',
		);
		$data['signed_field_names'] = implode(',', array_keys($data));

		$log['path'] = __METHOD__;
		$log['sale_request'] = $data;
		$this->config->gateway->debug($log);
		return $data;
	}
}
