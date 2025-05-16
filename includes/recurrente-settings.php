<?php
/**
* Clase para obtener la configuración de Recurrente
*
* Clase encargada de obtener el arreglo que define los campos a utilizar dentro de la configuración del plugin.
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
class RecurrenteSettings 
{
    /**
    * Obtiene el arreglo de configuraciones
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return Array  Arreglo de campos para la vista de configuración
    * @since 1.2.0
    * Actualizado en la 2.1.1
    */ 
    public static function get_settings(){
        return array(
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
              'type'    => 'text',
              'desc_tip'  => __( 'Descripcion a mostrar en el checkout.', 'recurrente' ),
              'default'  => __( 'Procesa tu pago a travez de recurrente', 'recurrente' )
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
            ),
            'allow_transfer' => array(
              'title'    => __( 'Habilitar Transferencia Bancaria', 'recurrente' ),
              'label'    => __( 'Activa la opción de pago por transferencia bancaria.', 'recurrente' ),
              'type'    => 'checkbox',
              'default'  => 'no',
              'desc_tip'  => __( 'Esta opción muestra transferencia bancaria como las posibles opciones de pago.', 'recurrente' ),
            ),
            'installments' => array(
              'title'    => __( 'Habilitar Cuotas', 'recurrente' ),
              'type'    => 'multiselect',
              'options'     => array( // Array of options for select/multiselect inputs only.
                '3 Meses' => '3',
                '6 Meses' => '6',
                '12 Meses' => '12',
                '18 Meses' => '18'
              ),
              'desc_tip'  => __( 'Preciona la opción + CTRL para poder seleccionar varias.', 'recurrente' ),
            ),
            'order_status' => array(
                'title'       => __( 'Estado Predeterminado de la orden', 'my-text-domain' ),
                'type'        => 'select',
                'description' => __( 'Selecciona el estado predeterminado para las órdenes procesadas.', 'my-text-domain' ),
                'options'     => self::get_filtered_order_statuses(),
                'default'     => 'wc-completed',
            ),
        );    
    }

    /**
     * Obtiene los estados de orden filtrados
     * 
     * @return array Estados de orden filtrados
     */
    private static function get_filtered_order_statuses() {
        $order_statuses = wc_get_order_statuses();
        $excluded_statuses = array('wc-cancelled', 'wc-refunded', 'wc-failed');
        
        return array_diff_key($order_statuses, array_flip($excluded_statuses));
    }

    // Función para obtener y almacenar el token
    public static function obtener_y_almacenar_token($public_key, $secret_key) {
        error_log('Recurrente Debug: ===== INICIO DE OBTENCIÓN DE TOKEN =====');
        error_log('Recurrente Debug: Endpoint: https://aurora.codingtipi.com/pay/v2/recurrente/setup');
        
        if (empty($public_key) || empty($secret_key)) {
            error_log('Recurrente Debug: Error - Credenciales vacías');
            error_log('Recurrente Debug: Public Key: ' . (empty($public_key) ? 'vacío' : 'presente'));
            error_log('Recurrente Debug: Secret Key: ' . (empty($secret_key) ? 'vacío' : 'presente'));
            return new WP_Error('invalid_credentials', 'Las credenciales de Recurrente son inválidas');
        }

        $url = 'https://aurora.codingtipi.com/pay/v2/recurrente/setup';
        // $url = 'http://localhost:8080/api/auth/token';
        $data = array(
            'publicKey' => $public_key,
            'secretKey' => $secret_key
        );

        error_log('Recurrente Debug: Enviando petición a: ' . $url);
        error_log('Recurrente Debug: Datos enviados: ' . json_encode(array(
            'publicKey' => $public_key ,
            'secretKey' => $secret_key
        )));

        error_log('Recurrente Debug: Configurando petición HTTP...');
        $response = wp_remote_post($url, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('Recurrente Debug: Error en la petición - ' . $response->get_error_message());
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        $code = wp_remote_retrieve_response_code($response);

        error_log('Recurrente Debug: Código de respuesta: ' . $code);
        error_log('Recurrente Debug: Cuerpo de respuesta: ' . json_encode($body));

        if ($code === 200 && isset($body->token)) {
            update_option('recurrente_api_token', $body->token);
            error_log('Recurrente Debug: Token almacenado exitosamente');
            error_log('Recurrente Debug: Token almacenado: ' . substr($body->token, 0, 10) . '...');
            return true;
        } else {
            $error_message = isset($body->message) ? $body->message : 'Error desconocido al obtener token';
            error_log('Recurrente Debug: Error al obtener token - Código: ' . $code . ', Mensaje: ' . $error_message);
            return new WP_Error('token_error', $error_message);
        }
        error_log('Recurrente Debug: ===== FIN DE OBTENCIÓN DE TOKEN =====');
    }

    // Función para inicializar acciones
    public static function init_actions() {
        add_action('update_option_woocommerce_recurrente_settings', function($old_value, $value, $option) {
            error_log('Recurrente Debug: Actualizando configuración de WooCommerce');
            error_log('Recurrente Debug: Nueva configuración: ' . json_encode($value));
            
            if (isset($value['public_key']) && isset($value['secret_key'])) {
                // Guardar las credenciales en ambas opciones para compatibilidad
                update_option('recurrente_settings', array(
                    'public_key' => $value['public_key'],
                    'secret_key' => $value['secret_key']
                ));
                
                error_log('Recurrente Debug: Credenciales guardadas en recurrente_settings');
                RecurrenteSettings::obtener_y_almacenar_token($value['public_key'], $value['secret_key']);
            }
        }, 10, 3);
        
        // Verificar si las credenciales existen al cargar el plugin
        add_action('plugins_loaded', function() {
            error_log('Recurrente Debug: Verificando configuración al cargar el plugin');
            $woocommerce_settings = get_option('woocommerce_recurrente_settings');
            $recurrente_settings = get_option('recurrente_settings');
            
            error_log('Recurrente Debug: Configuración de WooCommerce: ' . json_encode($woocommerce_settings));
            error_log('Recurrente Debug: Configuración de Recurrente: ' . json_encode($recurrente_settings));
            
            // Si no hay configuración en recurrente_settings pero sí en woocommerce_recurrente_settings
            if (empty($recurrente_settings) && !empty($woocommerce_settings)) {
                if (isset($woocommerce_settings['public_key']) && isset($woocommerce_settings['secret_key'])) {
                    update_option('recurrente_settings', array(
                        'public_key' => $woocommerce_settings['public_key'],
                        'secret_key' => $woocommerce_settings['secret_key']
                    ));
                    error_log('Recurrente Debug: Credenciales migradas desde WooCommerce');
                }
            }
        });
    }
}
// Llamar a la función de inicialización
RecurrenteSettings::init_actions();