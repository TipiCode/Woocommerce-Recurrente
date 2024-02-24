<?php
/**
 * Plugin Name: Recurrente - WooCommerce
 * Plugin URI: https://github.com/TipiCode/Woocommerce-Recurrente
 * Description: Plugin para Woocommerce que habilita la pasarela de pago Recurrente como método de pago en el checkout de tú sitio web.
 * Version:     1.2.0
 * Requires PHP: 7.4
 * Author:      tipi(code)
 * Author URI: https://codingtipi.com
 * License:     MIT
 * WC requires at least: 7.4.0
 * WC tested up to: 8.6.1
 *
 * @package WoocommerceRecurrente
*/


add_action( 'plugins_loaded', 'recurrente_init', 0 );
function recurrente_init() {
	//if condition use to do nothin while WooCommerce is not installed
  if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
  include_once ('classes/recurrente.php') ;
  // class add it too WooCommerce
  add_filter( 'woocommerce_payment_gateways', 'add_recurrente_gateway' );
  function add_recurrente_gateway( $methods ) {
	$methods[] = 'Recurrente';
	return $methods;
  }
}
// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'recurrente_action_links' );
function recurrente_action_links( $links ) {
  $plugin_links = array(
	'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'recurrente' ) . '</a>',
  );
  return array_merge( $plugin_links, $links );
}

//HPO Compatibility
add_action('before_woocommerce_init', function(){

  if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

  }
});

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature 
*/
function declare_cart_checkout_blocks_compatibility() {
  // Check if the required class exists
  if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
      // Declare compatibility for 'cart_checkout_blocks'
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
  }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action( 'woocommerce_blocks_loaded', 'recurrente_register_order_approval_payment_method_type' );

/**
 * Custom function to register a payment method type

 */
function recurrente_register_order_approval_payment_method_type() {
    // Check if the required class exists
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    // Include the custom Blocks Checkout class
    require_once ('includes/recurrente-block-checkout.php');

    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            // Register an instance of WC_Phonepe_Blocks
            $payment_method_registry->register( new WC_Recurrente_Blocks );
        }
    );
}