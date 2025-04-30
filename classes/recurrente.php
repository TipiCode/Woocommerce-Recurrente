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
  public $allow_transfer;
  public $installments;
  public $order_status;
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
  * @author Franco A. Cabrera <francocabreradev@gmail.com>
  * @link https://codingtipi.com/project/recurrente
  * @return Array Arreglo que contiene el resultado del proceso de la transacción y el URL para redirigir
  * @since 2.0.0
  */
  public function process_payment($order_id){
    error_log('Recurrente Debug: ===== INICIO DE PROCESS_PAYMENT =====');
    error_log('Recurrente Debug: Orden ID: ' . $order_id);
    try {
      $order = wc_get_order($order_id);
      error_log('Recurrente Debug: Orden obtenida - ID: ' . $order->get_id());
      
      // Verificar todas las opciones de configuración disponibles
      $all_settings = get_option('woocommerce_recurrente_settings');
      error_log('Recurrente Debug: Configuración de WooCommerce: ' . json_encode($all_settings));
      
      $recurrente_settings = get_option('recurrente_settings');
      error_log('Recurrente Debug: Configuración de Recurrente: ' . json_encode($recurrente_settings));
      
      $token = get_option('recurrente_api_token');
      error_log('Recurrente Debug: Token actual: ' . (!empty($token) ? substr($token, 0, 10) . '...' : 'no encontrado'));
      
      if (empty($token)) {
        error_log('Recurrente Debug: Token no encontrado, intentando obtener uno nuevo');
        // Intentar obtener las credenciales de diferentes fuentes
        $settings = get_option('recurrente_settings');
        if (empty($settings)) {
          $settings = get_option('woocommerce_recurrente_settings');
        }
        
        error_log('Recurrente Debug: Configuración cargada: ' . json_encode(array(
          'public_key' => !empty($settings['public_key']) ? substr($settings['public_key'], 0, 5) . '...' : 'no encontrada',
          'secret_key' => !empty($settings['secret_key']) ? substr($settings['secret_key'], 0, 5) . '...' : 'no encontrada'
        )));
        
        if (!empty($settings['public_key']) && !empty($settings['secret_key'])) {
          error_log('Recurrente Debug: Credenciales encontradas, intentando obtener token');
          $result = RecurrenteSettings::obtener_y_almacenar_token($settings['public_key'], $settings['secret_key']);
          if (is_wp_error($result)) {
            error_log('Recurrente Debug: Error al obtener token - ' . $result->get_error_message());
            return $result;
          }
          $token = get_option('recurrente_api_token');
          error_log('Recurrente Debug: Nuevo token obtenido: ' . substr($token, 0, 10) . '...');
        } else {
          error_log('Recurrente Debug: Error - No se encontraron las credenciales de API');
          return new WP_Error('no_credentials', 'No se encontraron las credenciales de API. Por favor, verifica la configuración del plugin.');
        }
      }

      // Verificar si la orden contiene productos recurrentes
      $has_recurrente_product = false;
      foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        error_log('Recurrente Debug: Producto encontrado - ID: ' . $product->get_id() . ', Tipo: ' . $product->get_type());
        if ($product && $product->get_type() === 'recurrente') {
          $has_recurrente_product = true;
          error_log('Recurrente Debug: Producto recurrente encontrado en la orden: ' . $product->get_name());
          break;
        }
      }

      error_log('Recurrente Debug: ¿Es orden recurrente? ' . ($has_recurrente_product ? 'Sí' : 'No'));

      if ($has_recurrente_product) {
        error_log('Recurrente Debug: Usando Subscription_Checkout para orden recurrente');
        $subscription_checkout = new Subscription_Checkout($order);
        $result = $subscription_checkout->create();
        error_log('Recurrente Debug: Resultado de Subscription_Checkout: ' . json_encode($result));
      } else {
        error_log('Recurrente Debug: Usando Single_Checkout para orden normal');
        $single_checkout = new Single_Checkout($order);
        $result = $single_checkout->create();
        error_log('Recurrente Debug: Resultado de Single_Checkout: ' . json_encode($result));
      }
      
      if (is_wp_error($result)) {
        error_log('Recurrente Debug: Error en process_payment - ' . $result->get_error_message());
        return $result;
      }

      if ($result === true) {
        $checkout = $has_recurrente_product ? $subscription_checkout : $single_checkout;
        error_log('Recurrente Debug: Checkout creado exitosamente, redirigiendo a: ' . $checkout->url);
        return array(
          'result' => 'success',
          'redirect' => $checkout->url
        );
      } else {
        error_log('Recurrente Debug: Error en checkout - ' . $result);
        return new WP_Error('checkout_error', $result);
      }
    } catch (Exception $e) {
      error_log('Recurrente Debug: Excepción en process_payment - ' . $e->getMessage());
      return new WP_Error('exception', $e->getMessage());
    }
    error_log('Recurrente Debug: ===== FIN DE PROCESS_PAYMENT =====');
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
  public function fail($message){
    if (is_wp_error($message)) {
      throw new Exception($message->get_error_message());
    }
    throw new Exception($message);
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