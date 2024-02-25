<?php
    class Recurrente extends WC_Payment_Gateway {
        public $public_key;    
        public $secret_key;
        private static $instance;     
        function __construct() {
          // global ID
            $this->id = "recurrente";
            // Show Title
            $this->method_title = __( "Recurrente", 'recurrente' );
            // Show Description
            $this->method_description = __( "Plugin de Recurrente para WooCommerce", 'recurrente' );
            // vertical tab title
            $this->title = __( "Recurrente", 'recurrente' );
            $this->icon = $this->get_option('icon');
            $this->has_fields = false;
            $this->description = "<img src".$this->icon."/>";
            
            // setting defines
            $this->init_form_fields();
            // load time variable setting
            $this->init_settings();
            
            // Turn these settings into variables we can use
            foreach ( $this->settings as $setting_key => $value ) {
                $this->$setting_key = $value;
            }

            // further check of SSL if you want
            add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );
            add_action( 'admin_notices', array( $this,  'validate_activation' ) );
            add_action('woocommerce_api_recurrente', array($this, 'redirect_callback'));

            // Save settings
            if ( is_admin() ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
          }    
        } // Here is the  End __construct()
      // administration fields for specific Gateway
      public function init_form_fields() {
        $this->form_fields = array(
          'enabled' => array(
            'title'    => __( 'Activar  / Desactivar', 'recurrente' ),
            'label'    => __( 'Activa la pasarela de pago', 'recurrente' ),
            'type'    => 'checkbox',
            'default'  => 'no',
          ),
          'title' => array(
            'title'    => __( 'Título', 'recurrente' ),
            'type'    => 'text',
            'desc_tip'  => __( 'Titulo a mostrar en el checkout.', 'recurrente' ),
            'default'  => __( 'Pago con tarjeta', 'recurrente' ),
          ),
          'description' => array(
            'title'    => __( 'Descripcion', 'recurrente' ),
            'type'    => 'textarea',
            'desc_tip'  => __( 'Descripcion a mostrar en el checkout.', 'recurrente' ),
            'default'  => __( 'Procesa tu pago a travez de recurrente', 'recurrente' ),
            'css'    => 'max-width:450px;'
          ),
          'public_key' => array(
            'title'    => __( 'Clave Pública', 'recurrente' ),
            'type'    => 'text',
            'desc_tip'  => __( 'Esta llave la puedes encontrar en el portal de recurrente en el área de Desarrolladores y API.', 'recurrente' ),
          ),
          'secret_key' => array(
            'title'    => __( 'Clave Secreta', 'recurrente' ),
            'type'    => 'text',
            'desc_tip'  => __( 'Esta llave la puedes encontrar en el portal de recurrente en el área de Desarrolladores y API.', 'recurrente' ),
          )
        );    
      }


      public static function get_instance() {
        if (is_null(self::$instance)) {
          self::$instance = new self();
        }
        return self::$instance;
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
          $order->add_order_note( 'Recurrente: '.'Se retorna a sitio web.' );
          wp_safe_redirect($redirect_url);
        }else if ($status_id == 0){
          $checkout_url = add_query_arg( [
            'cancel' => 'true',
          ], wc_get_checkout_url() );
          $order->add_order_note( 'Recurrente: '.'La transacción fue cancelada.' );
          wp_safe_redirect($checkout_url);
        }
      }

      public function process_webhook(){
        $jsonData = file_get_contents('php://input');
        // Decode the JSON data into a PHP associative array
        $data = json_decode($jsonData);
        if($data->event_type == 'payment_intent.failed'){
          $checkout_id = $data->checkout->id;
          $fail_message = $data->failure_reason;

          $args = array(
            'meta_key'      => 'recurrente_checkout_id', 
            'meta_value'    => $checkout_id, 
            'return'        => 'objects' 
          );

          $orders = wc_get_orders( $args );
          $order = $orders[0];
          $order->add_order_note( 'Recurrente: '.$fail_message );
          $order->update_status( 'wc-cancelled' );
        }//Cobro fallido con tarjeta
        else if($data->event_type == 'payment_intent.succeeded'){
          $checkout_id = $data->checkout->id;
          $args = array(
            'meta_key'      => 'recurrente_checkout_id', 
            'meta_value'    => $checkout_id, 
            'return'        => 'objects' 
          );

          $orders = wc_get_orders( $args );
          $order = $orders[0];
          $order->add_order_note( 'Recurrente: '.'Se completo el pago correctamente' );
          $order->update_status( 'wc-completed' );
        }//Cobro exitoso con tarjeta
      }

      // Response handled for payment gateway
      public function process_payment( $order_id ) {
        include dirname(__FILE__) . '/../utils/curl.php';
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

        // This is where the fun stuff begins
        // $payload = array(
        //   // Authorize.net Credentials and API Info
        //   "x_tran_key"             => $this->trans_key,
        //   "x_login"                => $this->api_login,
        //   "x_version"              => "3.1",
          
        //   // Order total
        //   "x_amount"               => $customer_order->order_total,
          
        //   // Credit Card Information
        //   "x_card_num"             => str_replace( array(' ', '-' ), '', $_POST['cwoa_authorizenet_aim-card-number'] ),
        //   "x_card_code"            => ( isset( $_POST['cwoa_authorizenet_aim-card-cvc'] ) ) ? $_POST['cwoa_authorizenet_aim-card-cvc'] : '',
        //   "x_exp_date"             => str_replace( array( '/', ' '), '', $_POST['cwoa_authorizenet_aim-card-expiry'] ),
          
        //   "x_type"                 => 'AUTH_CAPTURE',
        //   "x_invoice_num"          => str_replace( "#", "", $customer_order->get_order_number() ),
        //   "x_test_request"         => $environment,
        //   "x_delim_char"           => '|',
        //   "x_encap_char"           => '',
        //   "x_delim_data"           => "TRUE",
        //   "x_relay_response"       => "FALSE",
        //   "x_method"               => "CC",
          
        //   // Billing Information
        //   "x_first_name"           => $customer_order->billing_first_name,
        //   "x_last_name"            => $customer_order->billing_last_name,
        //   "x_address"              => $customer_order->billing_address_1,
        //   "x_city"                => $customer_order->billing_city,
        //   "x_state"                => $customer_order->billing_state,
        //   "x_zip"                  => $customer_order->billing_postcode,
        //   "x_country"              => $customer_order->billing_country,
        //   "x_phone"                => $customer_order->billing_phone,
        //   "x_email"                => $customer_order->billing_email,
          
        //   // Shipping Information
        //   "x_ship_to_first_name"   => $customer_order->shipping_first_name,
        //   "x_ship_to_last_name"    => $customer_order->shipping_last_name,
        //   "x_ship_to_company"      => $customer_order->shipping_company,
        //   "x_ship_to_address"      => $customer_order->shipping_address_1,
        //   "x_ship_to_city"         => $customer_order->shipping_city,
        //   "x_ship_to_country"      => $customer_order->shipping_country,
        //   "x_ship_to_state"        => $customer_order->shipping_state,
        //   "x_ship_to_zip"          => $customer_order->shipping_postcode,
          
        //   // information customer
        //   "x_cust_id"              => $customer_order->user_id,
        //   "x_customer_ip"          => $_SERVER['REMOTE_ADDR'],
          
        // );
      
        // Send this payload to Authorize.net for processing

        // $response = wp_remote_post( $url.'/products', array(
        //     'method'    => 'POST',
        //     'headers'     => [
        //         'Content-Type' => 'application/json',
        //         'X-PUBLIC-KEY' => $this->public_key,
        //         'X-SECRET-KEY' => $this->secret_key,
        //     ],
        //     'body'      => wp_json_encode($product),
        //     'timeout'   => 90,
        //     'sslverify' => false,
        // ) );

        

        // // values get
        // $r['response_code']             = $resp[0];
        // $r['response_sub_code']         = $resp[1];
        // $r['response_reason_code']      = $resp[2];
        // $r['response_reason_text']      = $resp[3];
        // // 1 or 4 means the transaction was a success
        // if ( ( $r['response_code'] == 1 ) || ( $r['response_code'] == 4 ) ) {
        //   // Payment successful
        //   $customer_order->add_order_note( __( 'Authorize.net complete payment.', 'cwoa-authorizenet-aim' ) );
                             
        //   // paid order marked
        //   $customer_order->payment_complete();
        //   // this is important part for empty cart
        //   $woocommerce->cart->empty_cart();
        //   // Redirect to thank you page
        //   return array(
        //     'result'   => 'success',
        //     'redirect' => $this->get_return_url( $customer_order ),
        //   );
        // } else {
        //   //transiction fail
        //   wc_add_notice( $r['response_reason_text'], 'error' );
        //   $customer_order->add_order_note( 'Error: '. $r['response_reason_text'] );
        // }
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

      public function do_ssl_check() {
        if( $this->enabled == "yes" ) {
          if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
            echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> Tu sitio no cuenta con un certificado SSL valido. Porfavor asegurate de contar con un certificado SSL <a href=\"%s\">para tener un proceso de compra seguro.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";  
          }
        }    
      }
    }