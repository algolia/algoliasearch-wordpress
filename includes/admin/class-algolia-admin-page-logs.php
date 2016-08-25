<?php

class Algolia_Admin_Page_Logs
{
	/**
	 * @var Algolia_Logger
	 */
	private $logger;

	/**
	 * @var Algolia_Settings
	 */
	private $settings;

	/**
	 * @param Algolia_Logger   $logger
	 * @param Algolia_Settings $settings
	 */
	public function __construct( Algolia_Logger $logger, Algolia_Settings $settings ) {
		$this->logger = $logger;
		$this->settings = $settings;

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_post_algolia_clear_logs', array( $this, 'clear_logs' ) );
		add_action( 'admin_post_algolia_disable_logging', array( $this, 'disable_logging' ) );
		add_action( 'admin_post_algolia_enable_logging', array( $this, 'enable_logging' ) );

		if ( get_transient( 'algolia_logs_cleared' ) ) {
			delete_transient( 'algolia_logs_cleared' );
			add_action( 'admin_notices', array( $this, 'logs_cleared_notice' ) );
		}

		if ( get_transient( 'algolia_logging_disabled' ) ) {
			delete_transient( 'algolia_logging_disabled' );
			add_action( 'admin_notices', array( $this, 'logging_disabled_notice' ) );
		}

		if ( get_transient( 'algolia_logging_enabled' ) ) {
			delete_transient( 'algolia_logging_enabled' );
			add_action( 'admin_notices', array( $this, 'logging_enabled_notice' ) );
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'algolia-logs' && false === $this->settings->get_logging_enabled() ) {
			add_action( 'admin_notices', array( $this, 'no_logs_notice' ) );
		}
	}

	public function clear_logs() {
		$this->logger->clear_logs();
		set_transient( 'algolia_logs_cleared', true );
		wp_redirect( admin_url( 'admin.php?page=algolia-logs' ) );
	}

	public function disable_logging() {
		$this->settings->set_logging_enabled( false );
		set_transient( 'algolia_logging_disabled', true );
		wp_redirect( admin_url( 'admin.php?page=algolia-logs' ) );
	}

	public function enable_logging() {
		$this->settings->set_logging_enabled( true );
		set_transient( 'algolia_logging_enabled', true );
		wp_redirect( admin_url( 'admin.php?page=algolia-logs' ) );
	}

	
	public function logs_cleared_notice() {
		echo '<div class="updated notice">
			      <p>' . esc_html__( 'The logs have been cleared.', 'algolia' ) . '</p>
			  </div>';
	}

	public function logging_disabled_notice() {
		echo '<div class="updated notice">
			      <p>' . esc_html__( 'Logging has been disabled.', 'algolia' ) . '</p>
			  </div>';
	}

	public function logging_enabled_notice() {
		echo '<div class="updated notice">
			      <p>' . esc_html__( 'Logging has been enabled.', 'algolia' ) . '</p>
			  </div>';
	}

	public function no_logs_notice() {
		echo '<div class="error notice">
			      <p>' . esc_html__( 'Logging is currently turned off which means you are still able to navigate through existing entries but no additional ones will be added.', 'algolia' ) . '</p>
			      <p>' . esc_html__( 'Note however that errors will be logged even if logging is turned off.', 'algolia' ) . '</p>
			  </div>';
	}

	public function add_page() {
		add_submenu_page(
			'algolia',
			__( 'Logs', 'algolia' ),
			__( 'Logs', 'algolia' ),
			'manage_options',
			'algolia-logs',
			array( $this, 'display_page' )
		);
	}
	
	public function display_page() {
		$paged = isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 0;
		$log_level = isset( $_GET['log_level'] ) ? $_GET['log_level'] : null;
		$logs_query = $this->logger->get_logs_query( $paged, $log_level );
		
		require_once dirname( __FILE__ ) . '/partials/page-logs.php';
	}
}
