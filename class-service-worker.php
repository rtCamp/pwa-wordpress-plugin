<?php
/**
 * Service Worker Related functions
 *
 * @package Pwa_Ready
 */

if ( ! defined( 'PWA_READY_QUERY_VAR' ) ) {
	define( 'PWA_READY_QUERY_VAR', 'pwa_ready_sw' );
}

/**
 * Class Service_Worker
 */
class Service_Worker {

	/**
	 *
	 * Class instance.
	 *
	 * @var null/Service_Worker Instance.
	 */
	private static $__instance = null;

	/**
	 * Singleton implementation
	 *
	 * @return object
	 */
	public static function instance() {

		if ( ! is_a( self::$__instance, 'Service_Worker' ) ) {
			self::$__instance = new Service_Worker();
		}

		return self::$__instance;
	}

	/**
	 * Registers actions
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'register_rewrite_rule' ), 99 );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'render_service_worker_js' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_service_worker' ) );
	}

	/**
	 * Function will add rewrite rule to create virtual service worker file.
	 */
	public function register_rewrite_rule() {
		add_rewrite_rule( '^sw.js$', 'index.php?' . PWA_READY_QUERY_VAR . '=1', 'top' );
	}

	/**
	 * Register query var for service worker.
	 *
	 * @param array $vars Array of registered query vars.
	 *
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = PWA_READY_QUERY_VAR;
		return $vars;
	}

	/**
	 * Render virtual service worker file to root scope.
	 */
	public function render_service_worker_js() {
		global $wp_query;

		if ( $wp_query->get( PWA_READY_QUERY_VAR ) ) {

			header( 'Content-Type: application/javascript; charset=utf-8' );

			// fake localize - service worker is not loaded in page context, so regular localize doesn't work.
			$pwa_vars = array(
				'admin_url'     => admin_url(),
				'site_url'      => site_url(),
				'sw_config_url' => site_url( '/sw.js' ),
				'ver'           => PWA_READY_VERSION,
				'precache'      => [],
			);

			$pwa_vars = apply_filters( 'pwa_ready_localize_data', $pwa_vars );

			echo preg_replace( '/pwa_vars_json/', json_encode( $pwa_vars ), file_get_contents( PWA_READY_DIR . '/service-worker.js' ) ); // @codingStandardsIgnoreLine.
			die;
		}
	}

	/**
	 * Load service worker on client.
	 */
	public function load_service_worker() {
		wp_enqueue_script( 'pwa-ready-sw', sprintf( '%s/js/main.js', untrailingslashit( PWA_READY_DIR_URL ) ), [], PWA_READY_VERSION, true );
	}
}

Service_Worker::instance();
