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

if ( ! defined( 'ABSPATH' ) ) { 
  exit; // No permitir acceder el plugin directamente
}

/**
* Función encargada de inicializar la pasarela de pagos de Recurrente
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function recurrente_init() {
  if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
  include_once ('classes/recurrente.php') ;

  Recurrente::get_instance();

  include_once( 'includes/plugin-update-checker/plugin-update-checker.php');

  $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	  'https://tipi-pod.sfo3.digitaloceanspaces.com/plugins/recurrente/details.json',
	  __FILE__, //Full path to the main plugin file or functions.php.
	  'woocommerce-recurrente'
  );
}
add_action( 'plugins_loaded', 'recurrente_init', 0 );

/**
* Función encargada de agregar Recurrente en la lista de pasarelas de pago
* 
* @return Array Arreglo que contiene los metodos de pago disponibles.
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function add_recurrente_gateway( $methods ) {
	$methods[] = 'Recurrente';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_recurrente_gateway' );

/**
* Función encargada de agregar el link hacia la configuración de Recurrente
* 
* @return Array Arreglo que contiene los links de plugins disponibles
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function recurrente_action_links( $links ) {
  $plugin_links = array(
	'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'recurrente' ) . '</a>',
  );
  return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'recurrente_action_links' );


/**
* Añade funcionalidad para compatibilidad con HPO de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function recurrente_hpo(){
  if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
  }
} 
add_action('before_woocommerce_init', 'recurrente_hpo');

/**
* Añade funcionalidad para compatibilidad con Blocks de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function declare_cart_checkout_blocks_compatibility() {
  // Check if the required class exists
  if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
      // Declare compatibility for 'cart_checkout_blocks'
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
  }
}
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

/**
* Añade funcionalidad para mostrar la pasarela de pagos en el area de bloques de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function recurrente_register_order_approval_payment_method_type() {
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
      return;
    }

    require_once ('includes/recurrente-block-checkout.php');

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
          $payment_method_registry->register( new WC_Recurrente_Blocks );
        }
    );
}
add_action( 'woocommerce_blocks_loaded', 'recurrente_register_order_approval_payment_method_type' );

/**
* Añade el ícono de tarjetas aceptadas a la pasarela de pago
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function filter_woocommerce_gateway_icon( $icon, $this_id ) {	
	if($this_id == "recurrente") {
		$icon = "<img style='max-width: 100px;' src='".plugins_url('assets/providers.png', __FILE__)."' alt='card providers' />";
	}
	return $icon;

}
add_filter( 'woocommerce_gateway_icon', 'filter_woocommerce_gateway_icon', 10, 2 );

/**
* Cambia el mensaje de confirmación dentro de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.2.0
*/
function woo_change_order_received_text( $str, $order ) {
  $customer_order = wc_get_order( $order );
  return sprintf( "Gracias, %s!", esc_html( $customer_order->get_billing_first_name() ) );
}
add_filter('woocommerce_thankyou_order_received_text', 'woo_change_order_received_text', 10, 2 );