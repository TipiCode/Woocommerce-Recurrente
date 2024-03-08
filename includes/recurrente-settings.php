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
    * @return Array  Arreglo de campos para la vista de configuración
    * @since 1.2.0
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
            )
        );    
    }
}