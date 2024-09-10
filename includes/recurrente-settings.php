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
                'options'     => array(
                    'wc-completed'  => __( 'Completada', 'my-text-domain' ),
                    'wc-on-hold'    => __( 'En espera', 'my-text-domain' ),
                    'wc-cancelled'  => __( 'Cancelada', 'my-text-domain' ),
                ),
                'default'     => 'wc-completed',
            ),
        );    
    }
}