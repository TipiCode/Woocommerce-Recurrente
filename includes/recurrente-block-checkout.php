<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Recurrente_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'recurrente';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'recurrente_settings', [] );
        $this->gateway = Recurrente::get_instance();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

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

    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->method_description,
            'icon' => $this->gateway->icon,
        ];
    }

}