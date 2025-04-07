<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo al archivo
}

class WC_Product_Recurrente extends WC_Product {
    private $debug_enabled;

    public function __construct( $product = 0 ) {
        $this->debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Inicializando producto recurrente ID: ' . (is_object($product) ? $product->get_id() : $product));
        }
        parent::__construct( $product );
        $this->product_type = 'recurrente'; // Establece el tipo de producto como recurrente
        $this->supports = array(
            'ajax_add_to_cart',
            'pricing',
            'core-fields',
            'add-to-cart'
        );
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Soporte habilitado: ' . print_r($this->supports, true));
        }
    }

    /**
     * Modifica la forma en la que se muestra el precio del producto recurrente.
     */
    public function get_price_html( $context = 'view' ) {
        $subscription_price = get_post_meta( $this->get_id(), '_recurrente_subscription_price', true );
        $subscription_interval = get_post_meta( $this->get_id(), '_recurrente_subscription_interval', true );
        
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Obteniendo precio HTML - Precio: ' . $subscription_price . ', Intervalo: ' . $subscription_interval);
        }

        if ( $subscription_price && $subscription_interval ) {
            return wc_price( $subscription_price ) . ' / ' . ucfirst( $subscription_interval );
        }

        return parent::get_price_html( $context );
    }

    /**
     * Obtiene el precio del producto recurrente
     */
    public function get_price($context = 'view') {
        $price = get_post_meta($this->get_id(), '_recurrente_subscription_price', true);
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Obteniendo precio base: ' . $price);
        }
        return $price ? $price : parent::get_price($context);
    }

    /**
     * Verifica si el producto está disponible para la venta
     */
    public function is_purchasable() {
        $purchasable = true;
        
        // Asegurarse de que el producto tenga precio
        if (!$this->get_price()) {
            $purchasable = false;
            if ($this->debug_enabled) {
                error_log('Recurrente Debug: Producto no comprable - Sin precio');
            }
        }
        
        $final_purchasable = apply_filters('woocommerce_is_purchasable', $purchasable, $this);
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: ¿Es comprable? ' . ($final_purchasable ? 'Sí' : 'No'));
        }
        return $final_purchasable;
    }

    /**
     * Verifica si el producto puede ser agregado al carrito
     */
    public function is_sold_individually() {
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Verificando si se vende individualmente');
        }
        return true;
    }

    /**
     * Obtiene el tipo de producto
     */
    public function get_type() {
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Obteniendo tipo de producto: recurrente');
        }
        return 'recurrente';
    }

    /**
     * Obtiene el texto del botón de agregar al carrito
     */
    public function add_to_cart_text() {
        $text = apply_filters('woocommerce_product_add_to_cart_text', __('Suscribirse', 'recurrente'), $this);
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Texto del botón de agregar al carrito: ' . $text);
        }
        return $text;
    }

    /**
     * Obtiene el texto del botón de agregar al carrito en la lista de productos
     */
    public function add_to_cart_description() {
        $description = apply_filters('woocommerce_product_add_to_cart_description', __('Suscribirse a este producto', 'recurrente'), $this);
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: Descripción de agregar al carrito: ' . $description);
        }
        return $description;
    }

    /**
     * Obtiene la URL de agregar al carrito
     */
    public function add_to_cart_url() {
        $url = apply_filters('woocommerce_product_add_to_cart_url', $this->get_permalink(), $this);
        if ($this->debug_enabled) {
            error_log('Recurrente Debug: URL de agregar al carrito: ' . $url);
        }
        return $url;
    }
}

