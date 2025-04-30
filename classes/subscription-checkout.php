<?php
/**
* Clase para interactuar con un Checkout de Suscripción dentro de Recurrente
*
* Objeto principal para interactuar con un checkout de Suscripción dentro de recurrente.
*
* @copyright  2024 - tipi(code)
* @since      2.1.0
* @author     Franco A. Cabrera <francocabreradev@gmail.com>
*/ 
class Subscription_Checkout {
    private $gateway;
    private $customer_order;
    private $product;
    private $curl;
    public $id;
    public $url;
    public $product_id;
    public $code;

    /**
    * Constructor
    *
    * @param WC_Order  $customer_order  Orden de WooCommerce para procesar los datos del producto.
    * 
    */ 
    function __construct($customer_order) {
        $this->gateway = Recurrente::get_instance();
        $this->customer_order = $customer_order;
        $this->curl = null;
        
        // Obtener el producto recurrente del pedido
        $items = $customer_order->get_items();
        foreach ($items as $item) {
            $product = $item->get_product();
            if ($product->get_type() === 'recurrente') {
                $this->product = $product;
                break;
            }
        }
    }

    /**
    * Obtiene una instancia de Curl
    */
    private function get_curl() {
        if ($this->curl === null) {
            $token = get_option('recurrente_api_token');
            error_log('Recurrente Debug: Obteniendo token para Subscription_Checkout');
            error_log('Recurrente Debug: Token encontrado: ' . (!empty($token) ? substr($token, 0, 10) . '...' : 'no encontrado'));
            
            if (empty($token)) {
                error_log('Recurrente Debug: Error - No se encontró el token de API en Subscription_Checkout');
                throw new Exception('No se encontró el token de API. Por favor, verifica la configuración del plugin.');
            }
            $this->curl = new Curl($token);
        }
        return $this->curl;
    }

    /**
    * Crea un nuevo Checkout de suscripción
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 2.1.0
    */
    public function create(){
        try {
            error_log('Recurrente Debug: ===== INICIO DE CREATE SUBSCRIPTION CHECKOUT =====');
            //$url = 'https://aurora.codingtipi.com/pay/v2/recurrente/checkouts/hosted/subscription';
            $url = 'http://localhost:8080/api/checkouts/';
            error_log('Recurrente Debug: URL del endpoint: ' . $url);
            
            $curl = $this->get_curl();
            $checkout = $this->get_api_model();
            error_log('Recurrente Debug: Datos del checkout: ' . json_encode($checkout));
            
            error_log('Recurrente Debug: Enviando petición al endpoint...');
            $response = $curl->execute_post($url, $checkout);
            error_log('Recurrente Debug: Respuesta del endpoint: ' . json_encode($response));
            
            $this->code = $response['code'];
            error_log('Recurrente Debug: Código de respuesta: ' . $this->code);
            
            if($this->code == 201){
                $this->id = $response['body']->id;
                $this->product_id = $response['body']->product;
                $this->url = $response['body']->url;
                error_log('Recurrente Debug: Checkout creado exitosamente - ID: ' . $this->id);
                error_log('Recurrente Debug: URL de redirección: ' . $this->url);
                return true;
            } else if ($this->code == 401) {
                error_log('Recurrente Debug: Error 401 - Token inválido o expirado');
                $settings = get_option('recurrente_settings');
                if (isset($settings['public_key']) && isset($settings['secret_key'])) {
                    error_log('Recurrente Debug: Intentando renovar token');
                    RecurrenteSettings::obtener_y_almacenar_token($settings['public_key'], $settings['secret_key']);
                    error_log('Recurrente Debug: Token renovado, intentando checkout nuevamente');
                    $response = $curl->execute_post($url, $checkout);
                    if ($response['code'] == 201) {
                        $this->id = $response['body']->id;
                        $this->product_id = $response['body']->product;
                        $this->url = $response['body']->url;
                        error_log('Recurrente Debug: Checkout creado exitosamente después de renovar token');
                        return true;
                    }
                }
                return new WP_Error('auth_error', 'Las credenciales de Recurrente son inválidas. Por favor, verifica la configuración del plugin.');
            } else {
                error_log('Recurrente Debug: Error en checkout - Código: ' . $this->code);
                return $response['body']->message;
            }
        } catch (Exception $e) {
            error_log('Recurrente Debug: Excepción en create() - ' . $e->getMessage());
            return new WP_Error('error', $e->getMessage());
        }
        error_log('Recurrente Debug: ===== FIN DE CREATE SUBSCRIPTION CHECKOUT =====');
    }

    /**
    * Elimina un producto de suscripción de la biblioteca de Recurrente
    * 
    * @throws Exception Si la llamada a recurrente falla
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 2.1.0
    */
    public function clean(){
        try {
            // $url = 'https://aurora.codingtipi.com/pay/v2/recurrente/products/'.$this->product_id;
            $url = 'http://localhost:8080/api/checkouts/'.$this->product_id;
            $curl = $this->get_curl();
            $response = $curl->execute_delete($url);
            return $response['code'];
        } catch (Exception $e) {
            return new WP_Error('error', $e->getMessage());
        }
    }

    /**
    * Obtiene el modelo de un checkout de suscripción para poder interactuar con el API de recurrente
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return Array Objeto para usar con el API de Recurrente
    * @link https://codingtipi.com/project/recurrente
    * @since 2.1.0
    */ 
    private function get_api_model(){
        // Mapeo de intervalos de WooCommerce a los valores esperados por Recurrente
        $interval_mapping = [
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            'yearly' => 'year'
        ];

        // Obtener el intervalo de la suscripción del producto
        $interval = get_post_meta($this->product->get_id(), '_recurrente_subscription_interval', true);
        $interval = isset($interval_mapping[$interval]) ? $interval_mapping[$interval] : 'month'; // Valor por defecto: month
        
        // Obtener el precio de la suscripción
        $amount = $this->product->get_sale_price() ? $this->product->get_sale_price() : $this->product->get_regular_price();
        
        // Construir URLs de redirección
        $success_url = $this->customer_order->get_checkout_order_received_url();
        $cancel_url = add_query_arg(['cancel' => 'true', 'order' => $this->customer_order->get_id()], wc_get_checkout_url());

        // Crear el modelo para la API
        return array(
            "number" => $this->customer_order->get_order_number(),
            "correlative" => $this->customer_order->get_id(),
            "description" => "Suscripción a " . $this->product->get_name() . ". Orden número " . $this->customer_order->get_order_number(),
            "amount" => floatval($amount),
            "currency" => $this->customer_order->get_currency(),
            "count" => 1, // Por defecto es 1
            "interval" => $interval,
            "billing" => array(
                "name" => $this->customer_order->get_billing_first_name(),
                "surname" => $this->customer_order->get_billing_last_name(),
                "taxId" => "", // Opcional
                "email" => $this->customer_order->get_billing_email(),
                "phone" => $this->customer_order->get_billing_phone(),
                "address" => $this->customer_order->get_billing_address_1()
            ),
            "redirection" => array(
                "successUrl" => $success_url,
                "cancelUrl" => $cancel_url
            )
        );
    }
} 