<?php
/**
* Clase principal para interactuar con el API de Recurrente
*
* Esta clase es la principal para interactuar con la pasarela de pagos.
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class Recurrente extends WC_Payment_Gateway {
  public $public_key;    
  public $secret_key;
  private static $instance;

  /**
  * Constructor
  * 
  */ 
  function __construct() {
    // Id global
    $this->id = "recurrente";
    // titulo a mostrar
    $this->method_title = __( "Recurrente", 'recurrente' );
    // Descripcion a mostrar
    $this->method_description = __( "Plugin de Recurrente para WooCommerce", 'recurrente' );
    // Seccion de tabs verticales
    $this->title = __( "Recurrente", 'recurrente' );
    $this->icon = $this->get_option('icon');
    $this->has_fields = false;
    $this->description = "<img src".$this->icon."/>";
      
    // Define los campos a utilizar en el formulario de configuración
    $this->init_form_fields();
    // Carga de Variables
    $this->init_settings();
    // Se agregan las acciónes a los plugins
    $this->init_actions();
      
    // Proceso para convertir las configuraciones a variables.
    foreach ( $this->settings as $setting_key => $value ) {
      $this->$setting_key = $value;
    }     
  } 

  /**
  * Función para patron de singleton
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @return Recurrente Clase inicializada
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */ 
  public static function get_instance() {
    if (!isset(self::$instance)) {
      error_log("New Instance");
      error_log($_SERVER['REQUEST_URI']);
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
  * Función que inicializa las acciones
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */ 
  public function init_actions(){
    add_action( 'admin_notices', array( $this,  'validate_activation' ) );
    add_action('woocommerce_api_recurrente', array($this, 'redirect_callback'));
    if ( is_admin() ) {
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }  
  }  

  /**
  * Función encargada de inicializar el formulario de configuración del pugin
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function init_form_fields() {
    include_once dirname(__FILE__) . '/../includes/recurrente-settings.php';
    $this->form_fields = RecurrenteSettings::get_settings();
  }

  /**
  * Función encargada del manejo de Callbacks por parte de la pasarela de pago
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function redirect_callback(){
    if (isset($_GET["status"])) {
      $this->answer_redirect(); //Esto quiere decir que es el redirect URL del checkout
    }else{
      $this->process_webhook(); //Esto quiere decir que es el Webhook
    }
  }

  /**
  * Función encargada del manejo de la redirección
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function answer_redirect(){
    $status_id = $_GET["status"];
    $order_id = $_GET["order"];
    $order = wc_get_order( $order_id );

    if($status_id == 1){ //El pago fue exitoso
      $redirect_url = $order->get_checkout_order_received_url();
      $order->add_order_note( 'Recurrente: '.'La transacción fue completada por el usuario.' );
      wp_safe_redirect($redirect_url);
    }else if ($status_id == 0){ //La operación fue cancelada
      $checkout_url = add_query_arg( [
        'cancel' => 'true',
      ], wc_get_checkout_url() );
      $order->add_order_note( 'Recurrente: '.'La transacción fue cancelada por el usuario.' );
      wp_safe_redirect($checkout_url);
    }
  }

  /**
  * Función encargada de procesar las respuesta del WebHook.
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function process_webhook(){
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData); //Convertir de JSON a objeto
    $event = $data->event_type; //Intent del Webhook
    
    include_once dirname(__FILE__) . '/../includes/recurrente-response.php';
    $response = new RecurrenteResponse($event);
    $response->execute($data);
  }

  /**
  * Función encargada de procesar el pago de WooCommerce
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @return Array Arreglo que contiene el resultado del proceso de la transacción y el URL para redirigir
  * @since 1.2.0
  */
  public function process_payment( $order_id ) {
    include_once dirname(__FILE__) . '/../utils/curl.php';
    include_once 'single-product.php';
    include_once 'client.php';
    include_once 'checkout.php';
    global $woocommerce;
    $customer_order = new WC_Order( $order_id ); //Crear Orden de WooCommerce
      
    $single_product = new Single_Product($customer_order); //Inicia un producto simpre 
    $product_transaction = $single_product->create(); 

    if ( is_wp_error( $product_transaction ) ) //Valida por error en la llamada del API
      $this->fail();
    if ( $product_transaction != 201 ) //Valida el return del status code 
      $this->fail();
      
    $client = new Client($customer_order); //Inicia la instancia del cliente para que aparezca lleno el checkout en recurrente
    $client_transaction = $client->create();
    if ( is_wp_error( $client_transaction ) ) //Valida por error en la llamada del API
      $this->fail();
    if ( $client_transaction != 200 ) //Valida el return del status code 
      $this->fail();

    $checkout = new Checkout($single_product->id, $client->id); //Inicia la instancia del checkout para que aparezca lleno el checkout en recurrente
    $checkout_transaction = $checkout->create();
    if ( is_wp_error( $checkout_transaction ) ) //Valida por error en la llamada del API
      $this->fail();
    if ( $checkout_transaction != 201 ) //Valida el return del status code 
      $this->fail();

    $customer_order->add_order_note( 'Recurrente: '.'Se inicializó el proceso de pago.' ); //Actualizar los comentarios 
    $customer_order->update_meta_data( 'recurrente_checkout_id', $checkout->id ); //Agregar el Id del checkout en la orden.
    $customer_order->save();

    return array(
      'result'   => 'success',
      'redirect' => $checkout->url,
    );
  }
  
  /**
  * Función encargada de validar los campos de la configuración.
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function validate_fields() {
    return true;
  }

  /**
  * Función encargada de mostrar el mensaje de error al usuario.
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function fail(){
    throw new Exception( __( 'Lo sentimos, ocurrio un error comunicandose con la pasarela de pago, disculpa el inconveniente.', 'recurrente' ) );
  }

  /**
  * Función encargada de validar la correcta activación del plugin.
  * 
  * @author Luis E. Mendoza <lmendoza@codingtipi.com>
  * @link https://codingtipi.com/project/recurrente
  * @since 1.2.0
  */
  public function validate_activation(){
    if( $this->enabled == "yes" ) {
      if( empty( $this->secret_key ) || empty( $this->public_key  ) ) {
        echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> No tienes configurado correctamente el plugin, <a href=\"%s\">porfavor dirigete a la configuracion.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout&section=recurrente' ) ) ."</p></div>";  
      }
    }   
  }
}