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

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'algolia-indexing' ) {
			return;
		}

		$scheme = ( defined( 'ALGOLIA_LOOPBACK_HTTP' ) && ALGOLIA_LOOPBACK_HTTP === true ) ? 'http' : 'admin';
		$url = admin_url( 'admin-post.php', $scheme );

		$request_args = array(
			'blocking'    		=> true,
			'redirection' 		=> 0,
			'algolia_loopback' 	=> true,
			'sslverify'   		=> apply_filters( 'https_local_ssl_verify', true ),
		);

		add_filter( 'use_curl_transport', function( $flag, array $args ) {
			if( ! isset( $args['algolia_loopback'] ) || $args['algolia_loopback'] !== true ) {
				// Only alter Algolia loopback calls.
				return $flag;
			}
			
			$version = curl_version();
			
			// Do not use cURL for loopback if version is lower than 7.34 because it does not support
			// TLS > 1.0 nor SSLv2
			return version_compare( $version['version'], '7.34', '>' );
		}, 10, 2 );

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
}
