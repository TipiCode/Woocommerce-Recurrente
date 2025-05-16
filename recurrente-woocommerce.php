<?php
/**
* Plugin Name: Recurrente - WooCommerce
* Plugin URI: https://github.com/TipiCode/Woocommerce-Recurrente
* Description: Plugin para Woocommerce que habilita la pasarela de pago Recurrente como método de pago en el checkout de tú sitio web.
* Version:     2.1.1
* Requires PHP: 7.4
* Author:      tipi(code)
* Author URI: https://codingtipi.com
* License:     MIT
* WC requires at least: 7.4.0
* WC tested up to: 8.7.0
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
    error_log('Recurrente Debug: Iniciando recurrente_init()');
    
    if (!class_exists('WC_Payment_Gateway')) {
        error_log('Recurrente Debug: WC_Payment_Gateway no está disponible');
        return;
    }

    // Definir la ruta base del plugin
    define('RECURRENTE_PLUGIN_DIR', dirname(__FILE__));

    // Cargar todos los archivos necesarios en el orden correcto
    if (!class_exists('Curl')) {
        error_log('Recurrente Debug: Cargando clase Curl');
        require_once RECURRENTE_PLUGIN_DIR . '/utils/curl.php';
    }

    if (!class_exists('HandleApiError')) {
      require_once RECURRENTE_PLUGIN_DIR . '/utils/handleApiError.php';
    }
    
    error_log('Recurrente Debug: Cargando clases principales');
    require_once RECURRENTE_PLUGIN_DIR . '/classes/recurrente.php';
    require_once RECURRENTE_PLUGIN_DIR . '/classes/single-checkout.php';
    require_once RECURRENTE_PLUGIN_DIR . '/classes/subscription-checkout.php';
    require_once RECURRENTE_PLUGIN_DIR . '/includes/recurrente-response.php';
    require_once RECURRENTE_PLUGIN_DIR . '/includes/recurrente-settings.php';
    require_once RECURRENTE_PLUGIN_DIR . '/includes/recurrente-block-checkout.php';

    // Obtener las credenciales y asegurar que el token exista
    $settings = get_option('recurrente_settings', array());
    error_log('Recurrente Debug: Configuración cargada: ' . json_encode($settings));
    
    if (!empty($settings['public_key']) && !empty($settings['secret_key'])) {
        $token = get_option('recurrente_api_token');
        error_log('Recurrente Debug: Token actual: ' . (!empty($token) ? substr($token, 0, 10) . '...' : 'no encontrado'));
        
        if (empty($token)) {
            error_log('Recurrente Debug: Token no encontrado, intentando obtener uno nuevo');
            RecurrenteSettings::obtener_y_almacenar_token($settings['public_key'], $settings['secret_key']);
        }
    }

    error_log('Recurrente Debug: Inicializando instancia de Recurrente');
    Recurrente::get_instance();

    include_once( 'includes/plugin-update-checker/plugin-update-checker.php');

    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://tipi-pod.sfo3.digitaloceanspaces.com/plugins/recurrente/details.json',
        __FILE__, //Full path to the main plugin file or functions.php.
        'woocommerce-recurrente'
    );
}

// Primero verificamos que WooCommerce esté activo
add_action( 'plugins_loaded', function() {
  if ( class_exists( 'WooCommerce' ) ) {
    // Luego inicializamos el plugin en el hook init
    add_action( 'init', 'recurrente_init', 0 );
  }
}, 0 );

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

/**
* Agrega el tipo de producto recurrente al dropdown de productos
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_add_custom_product_type( $types ){
  $types[ 'recurrente' ] = 'Producto Recurrente';
  return $types;
}
add_filter( 'product_type_selector', 'recurrente_add_custom_product_type' );


/**
* Se asegura que esté todo cargado e implementa la clase
* 
* @author Franco A. Cabrera <francocabreradev@gmail.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_include_product_class() {
  if ( class_exists( 'WC_Product' ) ) {
      include_once( 'includes/woocommerce/class-wc-product-recurrente.php' );
  }
}
add_action( 'woocommerce_loaded', 'recurrente_include_product_class' );

/**
* Agrega la clase del nuevo tipo de producto recurrente
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_woocommerce_product_class( $classname, $product_type ) {
  if ( $product_type == 'recurrente' ) {
    $classname = 'WC_Product_Recurrente';
  }
  return $classname;
}
add_filter( 'woocommerce_product_class', 'recurrente_woocommerce_product_class', 10, 2 );

/**
* Muestra el Tab de Precio al ser un producto no simple
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_product_type_show_price() {
  global $product_object;
  if ( $product_object && 'recurrente' === $product_object->get_type() ) {
    wc_enqueue_js( "
      $('.product_data_tabs .general_tab').addClass('show_if_recurrente').show();
      $('.pricing').addClass('show_if_recurrente').show();
    ");
  }
}
add_action( 'woocommerce_product_options_general_product_data', 'recurrente_product_type_show_price' );

/**
* Agrega los valores del tab de productos recurrentes
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_product_tab_product_tab_content() {
 ?><div id='recurrente_product_options' class='panel woocommerce_options_panel'><?php
 ?><div class='options_group'><?php
                
    woocommerce_wp_text_input(
    array(
      'id' => 'recurrente_price',
      'label' => __( 'Precio', 'recurrente_product' ),
      'placeholder' => '',
      'desc_tip' => 'true',
      'description' => __( 'Ingrese el precio de la susbcripción.', 'recurrente_product' ),
      'type' => 'number'
    )
    );
 ?></div>
 </div><?php
}
add_action( 'woocommerce_product_data_panels', 'recurrente_product_tab_product_tab_content' );

function recurrente_add_subscription_product_tab( $tabs ) {
  $tabs['recurrente_subscription'] = array(
      'label'    => __( 'Suscripción', 'recurrente_product' ),
      'target'   => 'recurrente_subscription_product_data', 
      'class'    => array('show_if_recurrente'),
      'priority' => 21,
  );
  return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'recurrente_add_subscription_product_tab' );

function recurrente_subscription_product_tab_content() {
  ?><div id='recurrente_subscription_product_data' class='panel woocommerce_options_panel'><?php
      ?><div class='options_group'><?php
          
          woocommerce_wp_text_input( array(
              'id'          => '_recurrente_subscription_price',
              'label'       => __( 'Precio de suscripción', 'recurrente_product' ),
              'placeholder' => '',
              'desc_tip'    => 'true',
              'description' => __( 'El precio que se cobrará en cada payday.', 'recurrente_product' ),
              'type'        => 'number',
              'custom_attributes' => array(
                  'min'  => '0',
                  'step' => '0.01',
              ),
          ) );

          woocommerce_wp_text_input( array(
            'id'          => '_recurrente_subscription_sale_price',
            'label'       => __( 'Precio en oferta', 'recurrente_product' ),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __( 'El precio que se cobrará en cada payday si hay una oferta.', 'recurrente' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'min'  => '0',
                'step' => '0.01',
            ),
        ) );
          
          woocommerce_wp_select( array(
              'id'      => '_recurrente_subscription_interval',
              'label'   => __( 'Intervalo de suscripción', 'recurrente_product' ),
              'options' => array(
                  'daily'   => __( 'Diario', 'recurrente_product' ),
                  'weekly'  => __( 'Semanal', 'recurrente_product' ),
                  'monthly' => __( 'Mensual', 'recurrente_product' ),
                  'yearly'  => __( 'Anual', 'recurrente_product' ),
              ),
              'description' => __( 'Selecciona la frecuencia de cobro de la suscripción.', 'recurrente_product' ),
          ) );
          
      ?></div>
  </div><?php
}
add_action( 'woocommerce_product_data_panels', 'recurrente_subscription_product_tab_content' );

function recurrente_save_subscription_product_meta( $post_id ) {
  // Guardar el precio de la suscripción
  $subscription_price = isset( $_POST['_recurrente_subscription_price'] ) ? sanitize_text_field( $_POST['_recurrente_subscription_price'] ) : '';
  update_post_meta( $post_id, '_recurrente_subscription_price', $subscription_price );
  
  $subscription_sale_price = isset( $_POST['_recurrente_subscription_sale_price'] ) ? sanitize_text_field( $_POST['_recurrente_subscription_sale_price'] ) : '';
    update_post_meta( $post_id, '_recurrente_subscription_sale_price', $subscription_sale_price );

  // Guardar el intervalo de suscripción
  $subscription_interval = isset( $_POST['_recurrente_subscription_interval'] ) ? sanitize_text_field( $_POST['_recurrente_subscription_interval'] ) : '';
  update_post_meta( $post_id, '_recurrente_subscription_interval', $subscription_interval );

  // Obtener el producto
  $product = wc_get_product( $post_id );

  // Si es un producto recurrente, actualiza el precio regular y el de oferta
  if ( 'recurrente' === $product->get_type() 
       || 1 == 1
       ) {
      // Asigna el precio regular
      if ( ! empty( $subscription_price ) ) {
          $product->regular_price = $subscription_price;
          $product->price = $subscription_price; // Establecer el precio general
      }

      // Asigna el precio de oferta, si existe
      if ( ! empty( $subscription_sale_price ) ) {
          $product->set_sale_price( $subscription_sale_price );
          $product->set_price( $subscription_sale_price ); // Cambia el precio si hay oferta
      }

      // Guarda los cambios en el producto
      $product->save();
  }
}
add_action( 'woocommerce_process_product_meta', 'recurrente_save_subscription_product_meta' );

/**
* Valida el contenido del carrito para asegurar que solo un producto recurrente pueda ser agregado a la vez.
* 
* @return bool Devuelve false si la validación falla, true en caso contrario.
* @autor Franco A. Cabrera <francocabreradev@gmail.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function validate_recurrente_cart($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    $is_recurrente = $product->get_type() === 'recurrente';

    // Si el producto es recurrente y hay otros productos en el carrito, limpiar el carrito
    if ($is_recurrente && !WC()->cart->is_empty()) {
        WC()->cart->empty_cart();
        wc_add_notice('Se ha limpiado el carrito para agregar la suscripción.', 'notice');
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $cart_product = $cart_item['data'];
        $is_cart_recurrente = $cart_product->get_type() === 'recurrente';

        if ($is_recurrente && $is_cart_recurrente) {
            wc_add_notice('Solo puedes adquirir una suscripción a la vez, procesa tu compra para poder agregar otra al carrito', 'error');
            return false;
        }

        if ($is_recurrente != $is_cart_recurrente) {
            wc_add_notice('Solo puedes adquirir una suscripción a la vez, procesa tu compra para poder agregar otra al carrito', 'error');
            return false;
        }
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'validate_recurrente_cart', 10, 3);

/**
* Modifica el proceso de checkout para productos recurrentes.
* 
* @return void
* @autor Franco A. Cabrera <francocabreradev@gmail.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function modify_checkout_for_recurrente() {
    $has_recurrente = false;
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if ($product->get_type() === 'recurrente') {
            $has_recurrente = true;
            break;
        }
    }

    if ($has_recurrente) {
        // Reemplazar el método de procesamiento de pago para productos recurrentes
        add_filter('woocommerce_payment_successful_result', 'recurrente_process_subscription_payment', 10, 2);
    }
}
add_action('woocommerce_checkout_process', 'modify_checkout_for_recurrente');

/**
* Procesa el pago de suscripción con Recurrente.
* 
* @param array $result Resultado del pago.
* @param int $order_id ID de la orden.
* @return array Resultado modificado con la URL de redirección al checkout de suscripción.
* @autor Franco A. Cabrera <francocabreradev@gmail.com>
* @link https://codingtipi.com/project/recurrente
* @since 2.1.0
*/
function recurrente_process_subscription_payment($result, $order_id) {
    // Obtener la orden
    $order = wc_get_order($order_id);
    
    // Crear el checkout de suscripción
    $subscription_checkout = new Subscription_Checkout($order);
    $checkout_result = $subscription_checkout->create();
    
    if (is_wp_error($checkout_result)) {
        // Manejar el error
        $order->add_order_note('Recurrente: Error al crear la suscripción: ' . $checkout_result->get_error_message());
        wc_add_notice('Error al procesar el pago con Recurrente: ' . $checkout_result->get_error_message(), 'error');
        return array(
            'result' => 'failure',
            'redirect' => wc_get_checkout_url()
        );
    }
    
    if ($subscription_checkout->code != 201) {
        // Manejar error de respuesta
        $order->add_order_note('Recurrente: Error al crear la suscripción. Código: ' . $subscription_checkout->code);
        wc_add_notice('Error al procesar el pago con Recurrente. Por favor, inténtelo de nuevo.', 'error');
        return array(
            'result' => 'failure',
            'redirect' => wc_get_checkout_url()
        );
    }
    
    // Guardar metadatos de la suscripción en la orden
    $order->add_order_note('Recurrente: Se inicializó el proceso de suscripción.');
    $order->update_meta_data('recurrente_checkout_id', $subscription_checkout->id);
    $order->update_meta_data('recurrente_checkout_url', $subscription_checkout->url);
    $order->update_meta_data('recurrente_product_id', $subscription_checkout->product_id);
    $order->update_meta_data('recurrente_is_subscription', 'yes');
    $order->save();
    
    // Redireccionar al usuario al checkout de suscripción
    return array(
        'result' => 'success',
        'redirect' => $subscription_checkout->url
    );
}

/**
 * Agrega soporte para el template por defecto de WooCommerce
 */
function recurrente_add_to_cart_template() {
    global $product;
    
    if ($product && $product->get_type() === 'recurrente') {
        error_log('Recurrente Debug: Usando template por defecto para producto recurrente');
        wc_get_template('single-product/add-to-cart/simple.php');
    }
}
add_action('woocommerce_recurrente_add_to_cart', 'recurrente_add_to_cart_template');

/**
 * Formatea el precio del producto recurrente en el carrito y checkout
 * 
 * NO ANDA FALTA ARREGLARLO
 */
function recurrente_cart_price_format($price_html, $cart_item, $cart_item_key) {
    $product = $cart_item['data'];
    
    if ($product && $product->get_type() === 'recurrente') {
        error_log('Recurrente Debug: Formateando precio en carrito para producto recurrente');
        $subscription_interval = get_post_meta($product->get_id(), '_recurrente_subscription_interval', true);
        if ($subscription_interval) {
            return wc_price($product->get_price()) . ' / ' . ucfirst($subscription_interval);
        }
    }
    
    return $price_html;
}
add_filter('woocommerce_cart_item_price', 'recurrente_cart_price_format', 10, 3);
add_filter('woocommerce_cart_item_subtotal', 'recurrente_cart_price_format', 10, 3);
add_filter('woocommerce_order_formatted_line_subtotal', 'recurrente_cart_price_format', 10, 3);

//TODO si se agregar un producto recurrente borrar el carrito si hay items y yo agrego un item recurrente llamar WC()->cart->empty_cart();