<?php
/**
 * Payment Gateway class for Recurrente Online
 *
 * @package Abzer
 */

if (! defined('ABSPATH')) {
	exit;
}
/**
 * Recurrente_Gateway_Http_Sale class.
 */
class Recurrente_Gateway_Http_Sale extends Recurrente_Gateway_Http_Abstract {



	/**
	 * Processing of API request body
	 *
	 * @param  array $data Data.
	 * @return string
	 */
	protected function pre_process( array $data) {
		return wp_json_encode($data);
	}

	/**
	 * Processing of API response
	 *
	 * @param  array $response Response.
	 * @return array|null
	 */
	protected function post_process( $response) {
		
		if (isset($response['reference_number'])) {
			global $wp_session;
			$data                  = array();
			$data['reference']     = $response['reference_number'];
			$data['action']        = $response['transaction_type'];
			$data['state']         = 'STARTED';
			$data['status']        = substr($this->order_status[0]['status'], 3);
			$wp_session['recurrente'] = $data;
			//return [ 'payment_url' => $response['url'] ];
						return $data;
		} else {
			return null;
		}
	}
}
