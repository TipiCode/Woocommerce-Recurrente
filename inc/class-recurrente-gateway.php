<?php

/**
 * Payment Gateway class for Recurrente Online
 *
 * @package Abzer
 */
if (!defined('ABSPATH')) {
	exit;
}

require_once dirname(__FILE__) . '/config/class-recurrente-gateway-config.php';
require_once dirname(__FILE__) . '/http/class-recurrente-gateway-http-abstract.php';

/**
 * Recurrente_Gateway class.
 */
class Recurrente_Gateway extends WC_Payment_Gateway {


    /**
	 * Whether or not logging is enabled
	 *
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * Logger instance
	 *
	 * @var WC_Logger
	 */
	public static $log = false;

	/**
	 * Singleton instance
	 *
	 * @var Recurrente_Gateway
	 */
	private static $instance;

	/**
	 * Notice variable
	 *
	 * @var string
	 */
	private $message;


	/**
	 * Get instance of Recurrente_Gateway
	 *
	 * Returns a new instance of self, if it does not already exist.
	 *
	 */
	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    /**
	 * Constructor for the gateway.
	 */
	public function __construct() {

		$this->id = 'recurrente';
		$this->recurrente_icon = $this->get_option('recurrente_icon');
		$this->icon = ( !empty($this->recurrente_icon) ) ? $this->recurrente_icon : apply_filters('recurrente_icon', plugins_url('assets/visaMaster.png', __FILE__)); // displayed on checkout page near your gateway name.
		$this->has_fields = false; // in case you need a custom credit card form.
		$this->method_title = 'Recurrente Payment Gateway';
		$this->method_description = 'Recurrente Payment Gateway';
		// will be displayed on the options page
		// gateways can support subscriptions, saved payment methods.
		$this->supports = array(
			'products',
		);

		// Method with all the options fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->enabled = $this->get_option('enabled');
		$this->order_status = $this->get_option('order_status');
		$this->access_key = $this->get_option('access_key');
		$this->secret_key = $this->get_option('secret_key');
		$this->debug = 'yes' === $this->get_option('debug', 'no');
		self::$log_enabled = $this->debug;
	}

	/**
	 * Plug-in options
	 */
	public function init_form_fields() {
		$this->form_fields = include 'settings-recurrente.php';
	}

	/**
	 * Initilize module hooks
	 */
	public function init_hooks() {
		add_action('woocommerce_receipt_recurrente', array($this, 'process_payment_page'));

		add_action('woocommerce_api_recurrenteonline', array($this, 'update_recurrente_response'));
		if (is_admin()) {
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			// add_action('add_meta_boxes', array($this, 'recurrente_online_meta_boxes'));
			add_action('save_post', array($this, 'recurrente_online_actions'));
		}
	}

	/**
	 * Add notice query variable
	 *
	 * @param  string $location Location.
	 * @return string
	 */
	public function add_notice_query_var( $location) {
		remove_filter('redirect_post_location', array($this, 'add_notice_query_var'), 99);
		return add_query_arg(array('message' => false), $location);
	}

	/**
	 * Processing order
	 *
	 * @global object $woocommerce
	 * @param  int $order_id Order ID.
	 * @return array|null
	 */
	public function process_payment( $order_id) {

		$order = wc_get_order($order_id);
		$pay_url = add_query_arg(array(
			'key' => $order->get_order_key(),
			'pay_for_order' => false,
		), $order->get_checkout_payment_url());

		return array(
			'result' => 'success',
			'redirect' => $pay_url
		);
	}

	/**
	 * Payment processing page
	 *
	 * @param int $order_id
	 */
	public function process_payment_page( $order_id) {
		session_start();
		$_SESSION['orderId'] = $order_id;

		global $woocommerce;

		$log['path'] = __METHOD__;
		$log['is_configured'] = false;
		$order = wc_get_order($order_id);

		include_once dirname(__FILE__) . '/request/class-recurrente-gateway-request-sale.php';
		include_once dirname(__FILE__) . '/http/class-recurrente-gateway-http-sale.php';

		$order = wc_get_order($order_id);
		$config = new Recurrente_Gateway_Config($this);

		if ($config->is_complete()) {
			$log['is_configured'] = true;

			$request_class = new Recurrente_Gateway_Request_Sale($config);
			$request_http =  new Recurrente_Gateway_Http_Sale();

			$requestArr = $request_class->build($order);

			$request_http->place_request($requestArr);
			$redirectUrl = $request_http->create_order($requestArr, $this);

			if($redirectUrl == null) {
				wc_add_notice('Error! Invalid configuration.', 'error');
				return false;
			}

			$_SESSION['ProdId'] = $redirectUrl["id"];

			$woocommerce->cart->empty_cart();
			$log['action'] = 'Redirecting to payment gateway...';
			$this->debug($log);
			?>
			<p class="loading-payment-text">
				<?php echo 'Please do not refresh the page, the page will be redirected to payment gateway'; ?>
				<?php
				?>
				<style>
					.lds-ring {
					  margin: 0 auto;
					  position: relative;
					  width: 80px;
					  height: 80px;
					}
					.lds-ring div {
					  box-sizing: border-box;
					  display: block;
					  position: absolute;
					  width: 64px;
					  height: 64px;
					  margin: 8px;
					  border: 8px solid #fff;
					  border-radius: 50%;
					  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
					  border-color: #000 #00000059 #0000000a transparent;
					}
					.lds-ring div:nth-child(1) {
					  animation-delay: -0.45s;
					}
					.lds-ring div:nth-child(2) {
					  animation-delay: -0.3s;
					}
					.lds-ring div:nth-child(3) {
					  animation-delay: -0.15s;
					}
					@keyframes lds-ring {
					  0% {transform: rotate(0deg);}
					  100% {transform: rotate(360deg);}
					}
				</style>
				<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
			</p>
			<a href="<?php echo esc_attr($redirectUrl["storefront_link"]); ?>" id="recurrente_payment_form"></a>

			<script type="text/javascript">
				setTimeout(function () {
					document.getElementById('recurrente_payment_form').click();
				}, 2000);
			</script>
			<?php
		} else {
			wc_add_notice('Error! Invalid configuration.', 'error');
			return false;
		}
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. Default 'info'. Possible values:
	 *                        emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public static function log( $message, $level = 'debug') {
		if (self::$log_enabled) {
			if (empty(self::$log)) {
				self::$log = wc_get_logger();
			}
			self::$log->log($level, $message . "\r\n", array('source' => 'recurrente'));
		}
	}

	/**
	 * Debug method.
	 *
	 * @param array $message Log message.
	 */
	public function debug( array $message) {
		self::log(wp_json_encode($message), 'debug');
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the error field out.
	 *
	 * @return bool was anything saved?
	 */
	public function process_admin_options() {
		$saved = parent::process_admin_options();

		if ('yes' === $this->get_option('enabled', 'no')) {
			if (empty($this->get_option('access_key'))) {
				add_settings_error('recurrente_error', esc_attr('settings_updated'), __('Invalid Access Key'), 'error');
			}
			if (empty($this->get_option('secret_key'))) {
				add_settings_error('recurrente_error', esc_attr('settings_updated'), __('Invalid Secret Key'), 'error');
			}
			add_action('admin_notices', 'print_errors');
		}
		if ('yes' !== $this->get_option('debug', 'no')) {
			if (empty(self::$log)) {
				self::$log = wc_get_logger();
			}
			self::$log->clear('recurrente');
		}
		return $saved;
	}

	/**
	 * Catch response from recurrente Online
	 */
	public function update_recurrente_response() {
		session_start();
		$order  = $_SESSION['orderId'];
		$prodId = $_SESSION['ProdId'];

		$_SESSION['orderId'] = "null";
		$_SESSION['ProdId'] = "null";

		$status = $_GET["status"];

		include plugin_dir_path(__FILE__) . '/class-recurrente-gateway-payment.php';
		$payment = new Recurrente_Gateway_Payment();
		$result = $payment->deleteProduct($prodId);
		$payment->execute($order, $status);
		die;
	}

	/**
	 * Handle actions on order page
	 *
	 * @param  int $post_id Post ID.
	 * @return null
	 */
	public function recurrente_online_actions( $post_id) {
		$this->message = '';
		WC_Admin_Notices::remove_all_notices();
		// $order_item = $this->fetch_order($post_id);
		$order = wc_get_order($post_id);
		$this->message = 'Order #' . $post_id . ' not found.';
		WC_Admin_Notices::add_custom_notice('recurrente', $this->message);
		add_filter('redirect_post_location', array($this, 'add_notice_query_var'), 99);
		return true;
	}
}