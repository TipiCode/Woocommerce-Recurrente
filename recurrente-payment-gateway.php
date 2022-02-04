<?php
/**
 * Plugin Name: Recurrente Payment Gateway WooCommerce
 * Plugin URI: https://github.com/TipiCode/woocommerce-gateway-recurrente
 * Description: Recurrente Payment gateway extension for WooCommerce.
 * Version:     1.0.4
 * Requires PHP: 7.2
 * Author:      tipi(code)
 * Author URI: https://codingtipi.com
 * License:     MIT
 * WC requires at least: 5.8.0
 * WC tested up to: 6.0.0
 *
 * @package RecurrenteGateway
*/

function Register_Recurrente_Order_status() {
	$statuses = include 'inc/order-status-recurrente.php';
	foreach ($statuses as $status) {
		$label = $status['label'];
		register_post_status(
			$status['status'],
			array(
			'label' => $label,
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: count */
			'label_count' => array(
				$label . ' <span class="count">(%s)</span>', // NOSONAR.
				$label . ' <span class="count">(%s)</span>' // NOSONAR.
			),
				)
		);
	}
}
add_action('init', 'Register_Recurrente_Order_status');

/**
 * Function to register woocommerce order statuses
 *
 * @param array $order_statuses Order Statuses.
 */
function Recurrente_Order_status( $order_statuses) {
	$statuses = include 'inc/order-status-recurrente.php';
	$id = get_the_ID();
	$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
	if ('shop_order' === get_post_type() && $id && isset($action) && 'edit' === $action) {
		$order = wc_get_order($id);
		if ($order) {
			$current_status = $order->get_status();
			foreach ($statuses as $status) {
				if ('wc-' . $current_status === $status['status']) {
					$order_statuses[$status['status']] = $status['label'];
				}
			}
		}
	} else {
		foreach ($statuses as $status) {
			$order_statuses[$status['status']] = $status['label'];
		}
	}
	return $order_statuses;
}

add_filter('wc_order_statuses', 'Recurrente_Order_status');

global $wpdb;

/**
 * Function to add action links
 *
 * @param $links Links.
 */
function Plugin_Action_Links_recurrente( $links) {
	$plugin_links = array(
		'<a href="admin.php?page=wc-settings&tab=checkout&section=recurrente">' . esc_html__('Settings', 'woocommerce') . '</a>',
	);
	return array_merge($plugin_links, $links);
}
/**
 * Filter to add action links
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'Plugin_Action_Links_recurrente');




/**
 * Print admin errors
 */
function Print_Errors_recurrente() {
    settings_errors('recurrente_error');
}


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'Recurrente_Init_Gateway_class');

/**
 * Initialise the gateway class
 */
function Recurrente_Init_Gateway_class() {
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}
	include_once 'inc/class-recurrente-gateway.php';
	Recurrente_Gateway::get_instance()->init_hooks();
}

// define the woocommerce_gateway_icon callback
function filter_woocommerce_gateway_icon( $icon, $this_id ) {	
	if($this_id == "recurrente") {
		$icon = "<img style='max-width: 100px;' src='".plugins_url('inc/assets/visaMaster.png', __FILE__)."' alt='recurrente icon' />";
	}
	return $icon;
}
add_filter( 'woocommerce_gateway_icon', 'filter_woocommerce_gateway_icon', 10, 2 );

/**
 * Add to woocommorce gateway list
 *
 * @param array $gateways Gateways.
 */
function Recurrente_Add_Gateway_class( $gateways) {
	$gateways[] = 'recurrente_gateway';
	return $gateways;
}

add_filter('woocommerce_payment_gateways', 'Recurrente_Add_Gateway_class');
