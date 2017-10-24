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

			add_action( 'wp_ajax_algolia_re_index', array( $this, 're_index' ) );
			add_action( 'wp_ajax_algolia_push_settings', array( $this, 'push_settings' ) );

			if ( isset( $_GET['page'] ) && substr( (string) $_GET['page'], 0, 7 ) === 'algolia' ) {
				add_action( 'admin_notices', array( $this, 'display_reindexing_notices' ) );
			}
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
		wp_enqueue_script( 'algolia-admin', plugin_dir_url( __FILE__ ) . 'js/algolia-admin.js', array( 'jquery', 'jquery-ui-sortable' ), ALGOLIA_VERSION );
		wp_enqueue_script( 'algolia-admin-reindex-button', plugin_dir_url( __FILE__ ) . 'js/reindex-button.js', array( 'jquery' ), ALGOLIA_VERSION );
		wp_enqueue_script( 'algolia-admin-push-settings-button', plugin_dir_url( __FILE__ ) . 'js/push-settings-button.js', array( 'jquery' ), ALGOLIA_VERSION );
	}

	/**
	 * Displays an error notice for every unmet requirement.
	 */
	public function display_unmet_requirements_notices() {
		if ( ! extension_loaded( 'mbstring' ) ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia Search requires the "mbstring" PHP extension to be enabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';
		} elseif ( ! function_exists( 'mb_ereg_replace' ) ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia needs "mbregex" NOT to be disabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';
		}

		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'Algolia Search requires the "cURL" PHP extension to be enabled. Please contact your hosting provider.', 'algolia' ) . '</p>
				  </div>';

			return;
		}

		$this->w3tc_notice();
	}

	/**
	 * Display notice to help users adding 'algolia_' as an ignored query string to the db caching configuration.
	 */
	public function w3tc_notice() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		$config   = w3_instance( 'W3_Config' );
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( 'algolia_', $settings ) ) {
			/* translators: placeholder contains the URL to the caching plugin's config page. */
			$message = sprintf( __( 'In order for <strong>database caching</strong> to work with Algolia you must add <code>algolia_</code> to the "Ignored Query Stems" option in W3 Total Cache settings <a href="%s">here</a>.', 'algolia' ), esc_url( admin_url( 'admin.php?page=w3tc_dbcache' ) ) );
			?>
			<div class="error">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>
			<?php
		}
	}

	public function display_reindexing_notices() {
		$indices = $this->plugin->get_indices(
			array(
				'enabled' => true,
			)
		);
		foreach ( $indices as $index ) {
			if ( $index->exists() ) {
				continue;
			}

	?>
	  <div class="error">
		<p>For Algolia search to work properly, you need to index: <strong><?php echo esc_html( $index->get_admin_name() ); ?></strong></p>
		<p><button class="algolia-reindex-button button button-primary" data-index="<?php echo esc_attr( $index->get_id() ); ?>">Index now</button></p>
	  </div>
<?php
		}
	}

	public function re_index() {
		try {
			if ( ! isset( $_POST['index_id'] ) ) {
				throw new RuntimeException( 'Index ID should be provided.' );
			}
			$index_id = (string) $_POST['index_id'];

			if ( ! isset( $_POST['p'] ) ) {
				throw new RuntimeException( 'Page should be provided.' );
			}
			$page = (int) $_POST['p'];

			$index = $this->plugin->get_index( $index_id );
			if ( null === $index ) {
				throw new RuntimeException( sprintf( 'Index named %s does not exist.', $index_id ) );
			}

			$total_pages = $index->get_re_index_max_num_pages();

			ob_start();
			if ( $page <= $total_pages || 0 === $total_pages ) {
				$index->re_index( $page );
			}
			ob_end_clean();

			$response = array(
				'totalPagesCount' => $total_pages,
				'finished'        => $page >= $total_pages,
			);

			wp_send_json( $response );
		} catch ( \Exception $exception ) {
			echo esc_html( $exception->getMessage() );
			throw $exception;
		}
	}

	public function push_settings() {
		try {
			if ( ! isset( $_POST['index_id'] ) ) {
				throw new RuntimeException( 'index_id should be provided.' );
			}
			$index_id = (string) $_POST['index_id'];

			$index = $this->plugin->get_index( $index_id );
			if ( null === $index ) {
				throw new RuntimeException( sprintf( 'Index named %s does not exist.', $index_id ) );
			}

			$index->push_settings();

			$response = array(
				'success' => true,
			);
			wp_send_json( $response );
		} catch ( \Exception $exception ) {
			echo esc_html( $exception->getMessage() );
			throw $exception;
		}
	}
}
