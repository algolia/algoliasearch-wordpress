<?php

class Algolia_Admin {
	
	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;
	
	/**
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		new Algolia_Cache_Helper();

		$api = $plugin->get_api();
		if ( $api->is_reachable() ) {
			new Algolia_Admin_Page_Autocomplete( $plugin->get_settings(), $this->plugin->get_autocomplete_config() );
			new Algolia_Admin_Page_Native_Search( $plugin );
			new Algolia_Admin_Page_Indexing( $plugin );
			new Algolia_Admin_Page_Logs( $plugin->get_logger(), $plugin->get_settings() );
		}

		new Algolia_Admin_Page_Settings( $plugin );

		add_action( 'admin_notices', array( $this, 'display_unmet_requirements_notices' ) );
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'algolia-admin', plugin_dir_url( __FILE__ ) . 'css/algolia-admin.css', array(), ALGOLIA_VERSION );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'algolia-admin', plugin_dir_url( __FILE__ ) . 'js/algolia-admin.js', array( 'jquery' ), ALGOLIA_VERSION );
	}

	/**
	 * Displays an error notice for every unmet requirement.
	 */
	public function display_unmet_requirements_notices() {
		if ( ! extension_loaded('mbstring') ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia Search requires the "mbstring" PHP extension to be enabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';
		} elseif ( ! function_exists( 'mb_ereg_replace' ) ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia needs "mbregex" NOT to be disabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';
		}

		if ( ! extension_loaded('curl') ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia Search requires the "cURL" PHP extension to be enabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';

			return;
		}

		$this->w3tc_notice();

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'algolia-indexing' ) {
			return;
		}

		
		$url = Algolia_Utils::get_loopback_request_url();
		$request_args = Algolia_Utils::get_loopback_request_args( array(
			'timeout'     => 60,
			'blocking'    => true,
		) );
		
		$result = wp_remote_post( $url, $request_args );
		if( ! $result instanceof WP_Error && $result['response']['code'] === 200 ) {
			// Remote call check was successful.
			return;
		}

		$this->plugin->get_logger()->log_error( 'wp_remote_post() check failed.', $result );

		echo '<div class="error notice">
				<p>' . esc_html__( "wp_remote_post() failed, indexing won't work. Checkout the logs for more details.", 'algolia' ) . '</p>
				<p>URL called: ' . $url . '</p>
				<p><code><pre>' . print_r( $result, true ) . '</pre></code></p>
			</div>';
	}

	/**
	 * Display notice to help users adding 'algolia_' as an ignored query string to the db caching configuration.
	 */
	public function w3tc_notice() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		$config   = w3_instance('W3_Config');
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( 'algolia_', $settings ) ) {
			?>
			<div class="error">
				<p><?php printf( __( 'In order for <strong>database caching</strong> to work with Algolia you must add <code>algolia_</code> to the "Ignored Query Stems" option in W3 Total Cache settings <a href="%s">here</a>.', 'algolia' ), admin_url( 'admin.php?page=w3tc_dbcache' ) ); ?></p>
			</div>
			<?php
		}
	}
}
