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
 * Recurrente_Gateway_Config class.
 */
class Recurrente_Gateway_Config {

	/**
	 * Pointer to gateway making the request.
	 *
	 * @var Recurrente_Gateway
	 */
	public $gateway;

	/**
	 * Constructor.
	 *
	 * @param Recurrente_Gateway $gateway Recurrente Online gateway object.
	 */
	public function __construct( Recurrente_Gateway $gateway) {
		$this->gateway = $gateway;
	}

	/**
	 * Retrieve apikey and outletReferenceId empty or not
	 *
	 * @return bool
	 */
	public function is_complete() {
		return ( !empty($this->get_seceret_key()) && !empty($this->get_access_key())) ? (bool) true : (bool) false;
	}

	/**
	 * Get Secret Key
	 *
	 * @return string
	 */
	public function get_seceret_key() {
		return $this->gateway->get_option('secret_key');
	}

	/**
	 * Get Access Key
	 *
	 * @return string
	 */
	public function get_access_key() {
		return $this->gateway->get_option('access_key');
	}
}
