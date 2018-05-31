<?php
/**
 * Service Worker Related functions
 *
 * @package PWA_WP_Plugin
 */

namespace PWA_WP_Plugin\Service_Worker;

if ( ! defined( 'PWA_WP_PLUGIN_SW' ) ) {
	define( 'PWA_WP_PLUGIN_SW', 'pwa_wp_plugin_sw' );
}

if ( ! defined( 'PWA_WP_PLUGIN_MANIFEST' ) ) {
	define( 'PWA_WP_PLUGIN_MANIFEST', 'pwa_wp_plugin_manifest' );
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

		add_action( 'init', array( $this, 'register_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		// priority 9 is set to stop canonical redirect.
		add_action( 'template_redirect', array( $this, 'render_service_worker_js' ), 9 );
		add_action( 'template_redirect', array( $this, 'render_manifest' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_service_worker' ) );
		add_action( 'wp_head', array( $this, 'add_link_meta' ) );
	}

	/**
	 * Function will add rewrite rule to create virtual service worker file.
	 */
	public function register_rewrite_rule() {
		add_rewrite_rule( '^sw.js$', 'index.php?' . PWA_WP_PLUGIN_SW . '=1', 'top' );
		add_rewrite_rule( '^theme-manifest.json$', 'index.php?' . PWA_WP_PLUGIN_MANIFEST . '=1', 'top' );
	}

	/**
	 * Register query var for service worker.
	 *
	 * @param array $vars Array of registered query vars.
	 *
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = PWA_WP_PLUGIN_SW;
		$vars[] = PWA_WP_PLUGIN_MANIFEST;
		return $vars;
	}

	/**
	 * Render virtual service worker file to root scope.
	 */
	public function render_service_worker_js() {
		global $wp_query;

		if ( $wp_query->get( PWA_WP_PLUGIN_SW ) ) {

			header( 'Content-Type: application/javascript; charset=utf-8' );

			// fake localize - service worker is not loaded in page context, so regular localize doesn't work.
			$pwa_vars = array(
				'admin_url'     => admin_url(),
				'site_url'      => site_url(),
				'sw_config_url' => site_url( '/sw.js' ),
				'ver'           => PWA_WP_PLUGIN_VERSION,
				'precache'      => [],
			);

			$pwa_vars = apply_filters( 'pwa_ready_localize_data', $pwa_vars );

			echo preg_replace( '/pwa_vars_json/', json_encode( $pwa_vars ), file_get_contents( PWA_WP_PLUGIN_DIR . '/service-worker.js' ) ); // @codingStandardsIgnoreLine.
			die;
		}
	}

	/**
	 * Render manifest file for theme
	 */
	public function render_manifest() {

		global $wp_query;

		if ( $wp_query->get( PWA_WP_PLUGIN_MANIFEST ) ) {

			$theme_color = sanitize_hex_color( $this->get_manifest_theme_color() );

			$manifest = array(
				'start_url'        => get_bloginfo( 'url' ),
				'short_name'       => get_bloginfo( 'name' ),
				'name'             => get_bloginfo( 'name' ),
				'display'          => 'standalone',
				'background_color' => $theme_color,
				'theme_color'      => $theme_color,
			);

			$manifest['icons'] = array(
				array(
					'src'   => $this->get_manifest_icon_url( 72 ),
					'sizes' => '48x48',
				),
				array(
					'src'   => $this->get_manifest_icon_url( 192 ),
					'sizes' => '192x192',
				),
				array(
					'src'   => $this->get_manifest_icon_url( 512 ),
					'sizes' => '512x512',
				),
			);

			$manifest = apply_filters( 'pwa_ready_manifest', $manifest );

			wp_send_json( $manifest );
		}
	}

	/**
	 * Add manifest file in header of theme,
	 */
	public function add_link_meta() {
		?>
		<meta name="theme-color" content="<?php echo sanitize_hex_color( $this->get_manifest_theme_color() ); // @codingStandardsIgnoreLine. ?>" />
		<link rel="manifest" href="<?php echo esc_url( site_url( '/theme-manifest.json' ) ); ?>">
		<?php
	}

	/**
	 * Load service worker on client.
	 */
	public function load_service_worker() {
		wp_enqueue_script( 'pwa-wp-plugin-sw', sprintf( '%s/js/main.js', untrailingslashit( PWA_WP_PLUGIN_DIR_URL ) ), [], PWA_WP_PLUGIN_VERSION, true );
	}

	/**
	 * Get theme color for manifest.
	 *
	 * @return mixed
	 */
	public function get_manifest_theme_color() {
		$theme_color = '#ffffff';
		return apply_filters( 'pwa_wp_plugin_get_theme_color', $theme_color );
	}

	/**
	 * Get site icon url.
	 *
	 * @param string $size Image size.
	 *
	 * @return string
	 */
	public function get_manifest_icon_url( $size ) {

		$path = sprintf( '%1$s/images/icons/icon-%2$sx%2$s.png', untrailingslashit( PWA_WP_PLUGIN_DIR_URL ), $size );
		$path = apply_filters( 'pwa_wp_plugin_manifest_icon_url', $path, $size );
		return $path;
	}
}

Service_Worker::instance();
