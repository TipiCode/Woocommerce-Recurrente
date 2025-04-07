<?php
/**
* Clase que habilita el soporte para el constructor de bloques de Wordpress
*
* Habilita los ¨Blocks¨ dentro de la vista de Checkout, para poder ampliar la compatibilidad
* del plugin.
*
* @copyright  2024 - tipi(code)
* @since      1.2.0
*/ 
final class WC_Recurrente_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'recurrente';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'recurrente_settings', [] );
        $this->gateway = Recurrente::get_instance();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    /**
    * Registra el Script para que se despliegue en el UI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string Integracion con el sistema de bloques.
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    public function get_payment_method_script_handles() {

        wp_register_script(
            'recurrente-blocks-integration',
            plugin_dir_url(__FILE__) . 'block/checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'recurrente-blocks-integration' );
            
        }
        return [ 'recurrente-blocks-integration' ];
    }

    /**
    * Obtiene la información a ser utilizada en el UI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Propiedades de la pasarela de pago.
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->method_description,
            'icon' => $this->gateway->icon,
        ];
    }

}