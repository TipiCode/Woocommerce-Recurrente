<?php

/**
 * Side menu for admin dashboard
 *
 * @package WoocommerceRecurrente
 */
if (!defined('ABSPATH')) {
	exit;
}

if ( ! class_exists( 'Recurrente_Side_Menu' ) ) {

	/**
	 * Class Recurrente_Side_Menu for registering the side menu to the admin dashboard and all of the necessary actions.
	 */
	class Recurrente_Side_Menu {

        /**
		 *  Constructor.
		 */
		public function __construct() {
            
			add_action( 'init', array( $this, 'register_recurrente_post_type' ), 0 );
			add_action( 'admin_menu', array( $this, 'create_recurrente_submenus' ), 0 );
            add_action( 'wp_loaded', array( $this, 'save_recurrente_configs' ), 12 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );
			
		}

        /**
		 * Import admin css for card styling,
		 */
         public function admin_enqueue_css(){
            wp_enqueue_style( 'WoocommerceRecurrente-admin-css', RECURRENTE_ASSETS_DIR_URL . '/css/admin.css' );
         }

         /**
		 * Save the credentials to database,
		 */
         public function save_recurrente_configs(){

            if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
				if ( empty( ( sanitize_text_field( wp_unslash( $_POST['save_recurrente_credentials'] ) ) ) ) ) {
					return;
				} else{
	
				
					try {
                        update_option('recurrente_secret_key', sanitize_text_field($_POST['secret_key']));
                        update_option('recurrente_public_key', sanitize_text_field($_POST['public_key']));
        
                        $location = $_SERVER['HTTP_REFERER'].'&status=success';
                        wp_safe_redirect($location);
                        exit();
                    } catch (\Throwable $th) {
                        $location = $_SERVER['HTTP_REFERER'].'&status=error';
                        wp_safe_redirect($location);
                        exit();
                    }

				}
			}
        }



        /**
		 * Create recurrente submenus,
		 */
		public function create_recurrente_submenus() {
            // Remove the default "Recurrente" submenu
            remove_submenu_page( 'edit.php?post_type=recurrente', 'edit.php?post_type=recurrente' );

            // Add new submenus
			add_submenu_page( 'edit.php?post_type=recurrente', 'Inicio', 'Inicio', 'manage_options', 'recurrente-main-view', array( $this, 'recurrente_main_view' ) );
			add_submenu_page( 'edit.php?post_type=recurrente', 'Configuracion', 'Configuracion', 'manage_options', 'recurrente-configurations', array( $this, 'recurrente_configurations' ) );
		}

        public function recurrente_configurations() {
			include_once RECURRENTE_ABSPATH . '/inc/template/admin/recurrente-configs-template.php';
		}

        public function recurrente_main_view() {
			include_once RECURRENTE_ABSPATH . '/inc/template/admin/recurrente-main-view-template.php';
		}

        /**
		 * Register recurrente post types.
		 */
		public function register_recurrente_post_type() {
            add_rewrite_endpoint( 'recurrente', EP_PAGES );
            $labels = array(
                'name'               => _x( 'Recurrente', 'post type general name', 'WoocommerceRecurrente' ),
                'singular_name'      => _x( 'Recurrente', 'post type singular name', 'WoocommerceRecurrente' ),
                'menu_name'          => _x( 'Recurrente', 'admin menu', 'WoocommerceRecurrente' ),
                'name_admin_bar'     => _x( 'Recurrente', 'add new on admin bar', 'WoocommerceRecurrente' ),
                
            );
            $args   = array(
                'labels'             => $labels,
                'description'        => __( 'Recurrente Side Menu.', 'WoocommerceRecurrente' ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => array( 'slug' => 'recurrente' ),
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'menu_icon'          => RECURRENTE_ASSETS_DIR_URL .'/recurrente-logo.svg',
                'supports'           => array( 'title', 'author', 'comments' ),
                'capability_type'    => 'post',
                'capabilities'       => array(
                    'create_posts' => false,
                ),
                'map_meta_cap'       => true,
            );
            register_post_type( 'recurrente', $args );

           
        }


    }

}
new Recurrente_Side_Menu();