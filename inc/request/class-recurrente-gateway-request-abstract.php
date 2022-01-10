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
 * Recurrente_Gateway_Request_Abstract class.
 */
abstract class Recurrente_Gateway_Request_Abstract {


	/**
	 * Config
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param Recurrente_Gateway_Config $config Config.
	 */
	public function __construct( Recurrente_Gateway_Config $config) {
		$this->config = $config;
	}

	/**
	 * Builds request array
	 *
	 * @param  array $order Order.
	 * @return array
	 */
	public function build( $order) {
		return $this->get_build_array($order);
	}

	/**
	 * Builds abstract request array
	 *
	 * @param  array $order Order.
	 * @return array
	 */
	abstract public function get_build_array( $order);
}
