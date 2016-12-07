<?php

class Algolia_Admin_Page_Indexing
{
	/**
	 * @var string
	 */
	private $slug = 'algolia-indexing';

	/**
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * @var string
	 */
	private $section = 'algolia_section_indices_configuration';

	/**
	 * @var string
	 */
	private $option_group = 'algolia_indices_configuration';

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	/**
	 *
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_notices', array( $this, 'display_errors' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'update_option_algolia_index_name_prefix', array( $this, 'index_name_prefix_updated' ), 5, 2 );
		add_action( 'update_option_algolia_synced_indices_ids', array( $this, 'synced_indices_ids_updated' ), 10, 2 );

		add_action( 'wp_ajax_algolia_run_queue', array( $this, 'run_queue' ) );
		add_action( 'wp_ajax_algolia_stop_queue', array( $this, 'stop_queue' ) );
		add_action( 'wp_ajax_algolia_queue_status', array( $this, 'queue_status' ) );

		add_action( 'admin_post_algolia_re_index_all', array( $this, 're_index_all' ) );
		add_action( 'admin_post_algolia_delete_pending_tasks', array( $this, 'delete_pending_tasks' ) );

		if ( get_transient( 'algolia_indices_notice' ) ) {
			add_action( 'admin_notices', array( $this, 'post_types_notice' ) );
		}

		if ( get_transient( 'algolia_index_name_prefix_notice' ) ) {
			add_action( 'admin_notices', array( $this, 'index_name_prefix_notice' ) );
		}

		if ( get_transient( 'algolia_all_re_indexed' ) ) {
			delete_transient( 'algolia_all_re_indexed' );
			add_action( 'admin_notices', array( $this, 'all_re_indexed_notice' ) );
		}

		if ( get_transient( 'algolia_deleted_pending_tasks' ) ) {
			delete_transient( 'algolia_deleted_pending_tasks' );
			add_action( 'admin_notices', array( $this, 'deleted_pending_tasks_notice' ) );
		}
		$this->plugin = $plugin;
	}

	public function enqueue_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->slug ) {
			wp_enqueue_script(
				'algolia-admin-indexing',
				plugin_dir_url( __FILE__ ) . 'js/algolia-admin-indexing.js',
				array( 'jquery' ),
				ALGOLIA_VERSION
			);
		}
	}

	public function run_queue() {
		do_action( 'algolia_process_queue' );
		wp_die();
	}

	public function stop_queue() {
		set_transient( 'algolia_stop_queue', true );
		wp_die();
	}

	public function queue_status() {
		$queue = $this->plugin->get_task_queue();
		
		wp_send_json( array(
			'tasks'           => $queue->get_queued_tasks_count(),
			'running'         => $queue->was_recently_running(),
			'current'         => get_transient( 'algolia_running_task' ),
			'recently_failed' => get_transient( 'algolia_recently_failed_task' ),
		) );
	}

	public function re_index_all() {
		$ids = $this->plugin->get_settings()->get_synced_indices_ids();
		$queue = $this->plugin->get_task_queue();
		foreach ( $ids as $id ) {
			$queue->queue( 're_index_items', array( 'index_id' => $id ) );
		}

		set_transient( 'algolia_all_re_indexed', true );
		wp_redirect( admin_url( 'admin.php?page=' . $this->slug ) );
	}

	public function delete_pending_tasks() {
		Algolia_Task::delete_all_tasks();

		set_transient( 'algolia_deleted_pending_tasks', true );
		wp_redirect( admin_url( 'admin.php?page=' . $this->slug ) );
	}

	public function all_re_indexed_notice() {
		echo '<div class="updated notice">
			      <p>' . esc_html__( 'All indices were queued for re-indexing.', 'algolia' ) . '</p>
			  </div>';
	}

	public function deleted_pending_tasks_notice() {
		echo '<div class="updated notice">
			      <p>' . esc_html__( 'All pending tasks were deleted.', 'algolia' ) . '</p>
			  </div>';
	}

	public function add_page() {
		add_submenu_page(
			'algolia',
			__( 'Indexing', 'algolia' ),
			__( 'Indexing', 'algolia' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
	}

	public function add_settings() {
		add_settings_section(
			$this->section,
			__( 'Content Types', 'algolia' ),
			array( $this, 'print_section_settings' ),
			$this->slug
		);

		add_settings_field(
			'algolia_index_name_prefix',
			__( 'Index name prefix' ),
			array( $this, 'index_name_prefix_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'algolia_synced_indices_ids',
			__( 'Indices', 'algolia' ),
			array( $this, 'synced_indices_ids_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'algolia_index_name_prefix', array( $this, 'sanitize_index_name_prefix' ) );
		register_setting( $this->option_group, 'algolia_synced_indices_ids', array( $this, 'sanitize_synced_indices_ids' ) );
	}

	/**
	 *
	 */
	public function synced_indices_ids_callback() {
		$indices = $this->plugin->get_indices();

		$html = '';
		foreach ( $indices as $index ) {
			/** @var Algolia_Index $index */
			$value = esc_attr( $index->get_id() );
			$label = esc_html( $index->get_admin_name() . ' [' . $index->get_id() . ']' );

			$checked = '';
			if ( $index->is_enabled() ) {
				$checked = 'checked ';
			}

			$input = "<input type='checkbox' id='$value' name='algolia_synced_indices_ids[]' value='$value' $checked/>";
			$html .= "<label for='$value'>$input $label</label><br/>";
		}
		$html .= '<p class="description" id="home-description">' . __( 'Each Content type is pushed to its own Algolia index. Configure here the list of indices you want to use.', 'algolia' ) . '</p>';

		echo $html;
	}
	

	public function index_name_prefix_callback()
	{
		$settings = $this->plugin->get_settings();
		$index_name_prefix = $settings->get_index_name_prefix();
		$disabled_html = $settings->is_index_name_prefix_in_config() ? ' disabled' : '';

		echo '<input type="text" name="algolia_index_name_prefix" value="' . esc_attr( $index_name_prefix ) . '" ' . $disabled_html . '/>' .
			'<p class="description" id="home-description">' . __( 'This prefix will be added to your index names.', 'algolia' ) . '</p>';
	}

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	public function sanitize_synced_indices_ids( $values ) {
		if ( ! is_array( $values ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $values as $value ) {
			if ( null === $this->plugin->get_index( $value ) ) {
				continue;
			}
			$sanitized[] = $value;
		}

		return $sanitized;
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function sanitize_index_name_prefix( $value ) {
		if ( $this->is_valid_index_name_prefix( $value ) ) {
			return $value;
		}

		add_settings_error(
			$this->option_group,
			'wrong_prefix',
			__( 'Indices prefix can only contain alphanumeric characters and underscores.', 'algolia' )
		);

		$value = get_option( 'algolia_index_name_prefix' );

		return $this->is_valid_index_name_prefix( $value ) ? $value : 'wp_';
	}

	/**
	 * @param $index_name_prefix
	 *
	 * @return string
	 */
	public function is_valid_index_name_prefix( $index_name_prefix ) {
		$to_validate = str_replace( '_', '', $index_name_prefix );

		return ctype_alnum( $to_validate );
	}

	/**
	 * Handles deletion of indices that are no longer needed.
	 * Schedules posts indexing for newly added post types.
	 *
	 * @param array $old_values
	 * @param array $values
	 */
	public function synced_indices_ids_updated( $old_values, $values ) {
		$indices_added = array_diff( $values, $old_values );
		$indices_removed = array_diff( $old_values, $values );

		$added_labels = array();
		foreach ( $indices_added as $index_id ) {
			$index = $this->plugin->get_index( $index_id );
			if ( null === $index ) {
				continue;
			}
			$this->plugin->get_task_queue()->queue( 're_index_items', array( 'index_id' => $index->get_id() ) );
			$added_labels[] = $index->get_admin_name();
		}

		$removed_labels = array();
		foreach ( $indices_removed as $index_id ) {
			$index = $this->plugin->get_index( $index_id );
			if ( null === $index ) {
				continue;
			}
			$this->plugin->get_task_queue()->queue( 'de_index_items', array( 'index_id' => $index->get_id() ) );
			$removed_labels[] = $index->get_admin_name();
		}

		if ( empty( $added_labels ) && empty( $removed_labels ) ) {
			return;
		}

		$messages = array();
		if ( ! empty( $added_labels ) ) {
			$messages[] = sprintf( __( 'Queued re-indexing of: %s.', 'algolia' ), implode( ', ', $added_labels ) ) . ' ';
		}
		if ( ! empty( $removed_labels ) ) {
			$messages[] = sprintf( __( 'Queued de-indexing of: %s.', 'algolia' ), implode( ', ', $removed_labels ) ) . ' ';
		}

		if ( ! empty( $messages ) ) {
			set_transient( 'algolia_indices_notice', implode( '<br>', $messages ) );
		}
	}

	public function post_types_notice() {
		echo '<div class="updated notice">
				  <p>' . wp_kses( get_transient( 'algolia_indices_notice' ), array( 'br' => array() ) ) . '</p>
			  </div>';

		delete_transient( 'algolia_indices_notice' );
	}

	public function index_name_prefix_updated( $old_value, $value ) {
		if ( $old_value === $value ) {
			return;
		}
	
		$indices = $this->plugin->get_indices();
		$queue = $this->plugin->get_task_queue();
		foreach ( $indices as $index ) {
			if ( ! $index->is_enabled() ) {
				continue;
			}

			$queue->queue( 're_index_items', array( 'index_id' => $index->get_id() ) );
		}
		
		$message = __( 'Your new index prefix has correctly been taken into account. Everything will now be re-indexed.', 'algolia' );
		set_transient( 'algolia_index_name_prefix_notice', $message );
	}

	public function index_name_prefix_notice() {
		echo '<div class="updated notice">
				  <p>' . esc_html( get_transient( 'algolia_index_name_prefix_notice' ) ) . '</p>
			  </div>';

		delete_transient( 'algolia_index_name_prefix_notice' );
	}

	/**
	 * Display the page.
	 */
	public function display_page() {
		$queue = $this->plugin->get_task_queue();
		$enabled_indices = $this->plugin->get_indices( array( 'enabled' => true ) );

		require_once dirname( __FILE__ ) . '/partials/page-indexing.php';
	}

	/**
	 * Display the errors.
	 */
	public function display_errors() {
		settings_errors( $this->option_group );

		if ( defined( 'ALGOLIA_HIDE_HELP_NOTICES' ) && ALGOLIA_HIDE_HELP_NOTICES ) {
			return;
		}

		$indices = $this->plugin->get_indices( array( 'enabled' => true ) );

		if ( empty( $indices ) ) {
			echo '<div class="error notice">
					<p>' . sprintf( __( 'You have no indexed content in Algolia. Please index some content on the <a href="%s">Algolia: Indexing page</a>.', 'algolia' ), admin_url( 'admin.php?page=' . $this->slug ) ) . '</p>
				</div>';
		}
	}

	/**
	 * Prints the section text.
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'Configure here the content types you want to push to Algolia.', 'algolia' ) . '</p>';
		echo '<p>' . esc_html__( 'Changing configuration will automatically schedule a new indexation task.', 'algolia' ) . '</p>';
	}
}
