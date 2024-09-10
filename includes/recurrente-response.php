<?php
/**
* Clase encargada del manejo de las respuestas del Webhook de Recurrente
*
* Contiene una serie de validaciónes para todos los esenarios propuestos por el plugin de recurrente.
*
* @copyright  2024 - tipi(code)
* @since 1.2.0
*/ 
class RecurrenteResponse 
{
    public $intent;
    public $settings;

    /**
    * Constructor
    *
    * @param string   $intent  Representa el tipo de evento que responde el WebHook.
    * 
    */ 
    function __construct($intent) {
        $this->intent = $intent;
        $this->settings = get_option( 'recurrente_settings', [] );
    }

    /**
    * Ejecuta la respuesta y procesa la orden según el resultado del evento del WebHook
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    public function execute($data){
        if($this->intent === 'payment_intent.failed'){
            $this->payment_failed($data);
        }//Fallo el pago con tarjeta de crédito / debito
        elseif($this->intent === 'payment_intent.succeeded'){
            $this->payment_succeeded($data);
        }//Se completo el pago con tarjeta de crédito / debito
        elseif($this->intent === 'bank_transfer_intent.pending'){
            $this->bank_transfer_pending($data);
        }//Se inicio el proceso de transferencia bancaria.
        elseif($this->intent === 'bank_transfer_intent.succeeded'){
            $this->bank_transfer_succeeded($data);
        }//Se completo el pago con transferencia bancaria.
        elseif($this->intent === 'bank_transfer_intent.failed'){
            $this->bank_transfer_failed($data);
        }//Fallo el pago con transferencia bancaria.
    }

    /**
    * Procesa el resultado fallido del intento de pago con tarjeta de crédito o débito
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */  
    private function payment_failed($data){
        $checkout_id = $data->checkout->id;
        $fail_message = $data->failure_reason;
        $this->process_order($checkout_id, 'wc-cancelled', 'Recurrente: '.$fail_message, true);
    }

    /**
    * Procesa el resultado satisfactorio del intento de pago con tarjeta de crédito o débito
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function payment_succeeded($data){
        $checkout_id = $data->checkout->id;
        $success_message = 'Se completo correctamente el pago con tarjeta.';

        $order_status = isset($this->settings['order_status']) ? $this->settings['order_status'] : 'wc-completed';

        $this->process_order($checkout_id, $order_status, 'Recurrente: '.$success_message, true);
    }

    /**
    * Procesa el el intento de pago con transferencia bancaria
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function bank_transfer_pending($data){
        $checkout_id = $data->checkout->id;
        $hold_message = 'Se inicio un proceso de transferencia bancaria.';
        $this->process_order($checkout_id, 'wc-on-hold', 'Recurrente: '.$hold_message, false);
    }

    /**
    * Procesa el resultado satisfactorio del intento de pago con transferencia bancaria
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function bank_transfer_succeeded($data){
        $checkout_id = $data->checkout->id;
        $success_message = 'Se completo correctamente el pago por transferencia bancaria.';
        $this->process_order($checkout_id, 'wc-completed', 'Recurrente: '.$success_message, true);
    }

    /**
    * Procesa el resultado satisfactorio del intento de pago con transferencia bancaria
    * 
    * @param Object   $data  Objeto que contiene la respuesta del webhook de Recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */ 
    private function bank_transfer_failed($data){
        $checkout_id = $data->checkout->id;
        $fail_message = $data->failure_reason;;
        $this->process_order($checkout_id, 'wc-cancelled', 'Recurrente: '.$fail_message, true);
    }

    /**
    * Procesa el estado de la orden dentro de WooCommerce
    * 
    * @param string   $checkout_id  Id del checkout de Recurrente.
    * @param string   $status  Estado al cual se cambiara al pedido.
    * @param string   $note  Nota que se le sera agregada al pedido.
    * @param string   $cleanup  Si se desea remover el producto de recurrente.
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/recurrente
    * @since 1.2.0
    */
    private function process_order($checkout_id, $status, $note, $cleanup){
        $args = array(
            'meta_key'      => 'recurrente_checkout_id', 
            'meta_value'    => $checkout_id, 
            'return'        => 'objects' 
        );
        $orders = wc_get_orders( $args );
        $order = $orders[0];
        $order->add_order_note( $note );
        $order->update_status( $status );

        if($cleanup == true){
            include_once dirname(__FILE__) . '/../utils/curl.php';
            include_once dirname(__FILE__) . '/../classes/single-checkout.php';
            $clean_product = $order->get_meta('recurrente_product_id');
            $single_checkout = new Single_Checkout($order);
            $single_checkout->id = $clean_product;
            $single_checkout->clean();
        }
    }
}
