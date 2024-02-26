<?php
  class Recurrente extends WC_Payment_Gateway {
    public $public_key;    
    public $secret_key;
    private static $instance;

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
        
        // setting defines
        $this->init_form_fields();
        // load time variable setting
        $this->init_settings();
        $this->init_actions();
        
        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
          $this->$setting_key = $value;
        }
          
    } // Here is the  End __construct()

    public static function get_instance() {
      if (!isset(self::$instance)) {
        error_log("New Instance");
        error_log($_SERVER['REQUEST_URI']);
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function init_actions(){
      add_action( 'admin_notices', array( $this,  'validate_activation' ) );
      add_action('woocommerce_api_recurrente', array($this, 'redirect_callback'));
          // Save settings
      if ( is_admin() ) {
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      }  
    }  

    public function init_form_fields() {
      include_once dirname(__FILE__) . '/../includes/recurrente-settings.php';
      $this->form_fields = RecurrenteSettings::get_settings();
    }

    public function redirect_callback(){
      if (isset($_GET["status"])) {
        $this->answer_redirect();
      }else{
        $this->process_webhook();
      }
    }

    public function answer_redirect(){
      $status_id = $_GET["status"];
      $order_id = $_GET["order"];
      $order = wc_get_order( $order_id );

      if($status_id == 1){
        $redirect_url = $order->get_checkout_order_received_url();
        $order->add_order_note( 'Recurrente: '.'La transacción fue completada por el usuario.' );
        wp_safe_redirect($redirect_url);
      }else if ($status_id == 0){
        $checkout_url = add_query_arg( [
          'cancel' => 'true',
        ], wc_get_checkout_url() );
        $order->add_order_note( 'Recurrente: '.'La transacción fue cancelada por el usuario.' );
        wp_safe_redirect($checkout_url);
      }
    }

    public function process_webhook(){
      $jsonData = file_get_contents('php://input');
      $data = json_decode($jsonData); //Convertir de JSON a objeto
      $event = $data->event_type; //Intent del Webhook
      
      include_once dirname(__FILE__) . '/../includes/recurrente-response.php';
      $response = new RecurrenteResponse($event);
      $response->execute($data);
    }

      // Response handled for payment gateway
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
      
      // Validate fields
    public function validate_fields() {
      return true;
    }

    public function fail(){
      throw new Exception( __( 'Lo sentimos, ocurrio un error comunicandose con la pasarela de pago, disculpa el inconveniente.', 'recurrente' ) );
    }

    public function validate_activation(){
      if( $this->enabled == "yes" ) {
        if( empty( $this->secret_key ) || empty( $this->public_key  ) ) {
          echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> No tienes configurado correctamente el plugin, <a href=\"%s\">porfavor dirigete a la configuracion.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout&section=recurrente' ) ) ."</p></div>";  
        }
      }   
    }
  }