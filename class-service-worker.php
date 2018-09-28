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

		// Filters.
		add_filter( 'site_icon_image_sizes', array( $this, 'prefix_add_site_icon_size' ) );
		add_filter( 'site_icon_meta_tags', array( $this, 'prefix_add_site_icon_tag' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		// Actions.
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'init', array( $this, 'register_rewrite_rule' ) );

		// priority 9 is set to stop canonical redirect.
		add_action( 'template_redirect', array( $this, 'render_service_worker_js' ), 9 );
		add_action( 'template_redirect', array( $this, 'render_manifest' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_service_worker' ) );
		add_action( 'wp_head', array( $this, 'add_link_meta' ) );

	}

	/**
	 * Custom icon sizes used for pwa.
	 *
	 * @param array $sizes Icon sizes.
	 *
	 * @return array
	 */
	public function prefix_add_site_icon_size( $sizes ) {

		$sizes[] = 48;
		$sizes[] = 256;

		return $sizes;
	}

	/**
	 * Add icons in head section.
	 *
	 * @param array $meta_tags Icons meta tags.
	 *
	 * @return array
	 */
	public function prefix_add_site_icon_tag( $meta_tags ) {

		$meta_tags[] = sprintf( '<link rel="icon" href="%s" sizes="48x48" />', esc_url( get_site_icon_url( 48 ) ) );
		$meta_tags[] = sprintf( '<link rel="icon" href="%s" sizes="256x256" />', esc_url( get_site_icon_url( 256 ) ) );

		return $meta_tags;

	}

	/**
	 * Progressive Web App Settings.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer object.
	 */
	public function customize_register( \WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_section(
			'pwa_options',
			array(
				'title'       => __( 'PWA WordPress', 'pwa-wordpress-plugin' ),
				'priority'    => 35,
				'capability'  => 'edit_theme_options',
				'description' => __( 'Setup PWA Options.', 'pwa-wordpress-plugin' ),
			)
		);

		$wp_customize->add_setting(
			'pwa_theme_color',
			array(
				'type'       => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default'    => '#000000',
			)
		);

		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'pwa_control_theme_color',
				array(
					'label'    => __( 'Theme Color', 'pwa-wordpress-plugin' ),
					'settings' => 'pwa_theme_color',
					'priority' => 10,
					'section'  => 'pwa_options',
				)
			)
		);

		$wp_customize->add_setting(
			'pwa_background_color',
			array(
				'type'       => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default'    => '#ffffff',
			)
		);

		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'pwa_control_background_color',
				array(
					'label'    => __( 'Background Color', 'pwa-wordpress-plugin' ),
					'settings' => 'pwa_background_color',
					'priority' => 10,
					'section'  => 'pwa_options',
				)
			)
		);

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

			$theme_color      = sanitize_hex_color( $this->get_manifest_theme_color() );
			$background_color = sanitize_hex_color( $this->get_manifest_background_color() );

			$manifest = array(
				'background_color' => $background_color,
				'description'      => get_bloginfo( 'description' ),
				'display'          => 'standalone',
				'name'             => get_bloginfo( 'name' ),
				'short_name'       => get_bloginfo( 'name' ),
				'start_url'        => get_bloginfo( 'url' ),
				'theme_color'      => $theme_color,
			);

			$manifest['icons'] = array(
				array(
					'src'   => get_site_icon_url( 48 ),
					'sizes' => '48x48',
				),
				array(
					'src'   => get_site_icon_url( 192 ),
					'sizes' => '192x192',
				),
				array(
					'src'   => get_site_icon_url( 256 ),
					'sizes' => '256x256',
				),
				array(
					'src'   => get_site_icon_url( 512 ),
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
		<link rel="manifest" href="<?php echo esc_url( $this->get_manifest_url() ); ?>">
		<?php

	}

	/**
	 * Load service worker on client.
	 */
	public function load_service_worker() {

		wp_enqueue_script(
			'pwa-wp-plugin-sw',
			sprintf( '%s/js/main.js', untrailingslashit( PWA_WP_PLUGIN_DIR_URL ) ),
			[],
			PWA_WP_PLUGIN_VERSION,
			true
		);

	}

	/**
	 * Get theme color for manifest.
	 *
	 * @return mixed
	 */
	public function get_manifest_theme_color() {

		$theme_color = sanitize_hex_color( get_theme_mod( 'pwa_theme_color', '#000000' ) );

		return apply_filters( 'pwa_wp_plugin_get_theme_color', $theme_color );

	}

	/**
	 * Get background color for manifest.
	 *
	 * @return mixed
	 */
	public function get_manifest_background_color() {

		$background_color = sanitize_hex_color( get_theme_mod( 'pwa_background_color', '#ffffff' ) );

		return apply_filters( 'pwa_wp_plugin_get_background_color', $background_color );

	}

	/**
	 * Get manifest url.
	 */
	public function get_manifest_url() {

		return add_query_arg( PWA_WP_PLUGIN_MANIFEST, '1', site_url() );

	}

}

Service_Worker::instance();
