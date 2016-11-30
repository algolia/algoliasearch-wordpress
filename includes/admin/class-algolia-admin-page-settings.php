<?php

class Algolia_Admin_Page_Settings
{
	/**
	 * @var string
	 */
	private $slug = 'algolia-account-settings';

	/**
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * @var string
	 */
	private $section = 'algolia_section_settings';

	/**
	 * @var string
	 */
	private $option_group = 'algolia_settings';
	
	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;
	
	/**
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_notices', array( $this, 'display_errors' ) );

		// Display a link to this page from the plugins page.
		add_filter( 'plugin_action_links_' . ALGOLIA_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function add_action_links( array $links ) {
		return array_merge( $links, array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->slug ) . '">' . __( 'Settings' ) . '</a>',
		) );
	}

	public function add_page() {
		$api = $this->plugin->get_api();
		if ( ! $api->is_reachable() ) {
			// Means this is the only reachable admin page, so make it the default one!
			return add_menu_page(
				'Algolia Search',
				__( 'Algolia search', 'algolia' ),
				'manage_options',
				$this->slug,
				array( $this, 'display_page' ),
				''
			);
		} 
		
		add_submenu_page(
			'algolia',
			__( 'Settings', 'algolia' ),
			__( 'Settings', 'algolia' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
	}

	public function add_settings() {
		add_settings_section(
			$this->section,
			null,
			array( $this, 'print_section_settings' ),
			$this->slug
		);

		add_settings_field(
			'algolia_application_id',
			__( 'Application ID', 'algolia' ),
			array( $this, 'application_id_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'algolia_search_api_key',
			__( 'Search-only API key', 'algolia' ),
			array( $this, 'search_api_key_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'algolia_api_key',
			__( 'Admin API key', 'algolia' ),
			array( $this, 'api_key_callback' ),
			$this->slug,
			$this->section
		);

		/*add_settings_field(
			'algolia_powered_by_enabled',
			__( 'Remove Algolia powered by logo', 'algolia' ),
			array( $this, 'powered_by_enabled_callback' ),
			$this->slug,
			$this->section
		);*/

		register_setting( $this->option_group, 'algolia_application_id', array( $this, 'sanitize_application_id' ) );
		register_setting( $this->option_group, 'algolia_search_api_key', array( $this, 'sanitize_search_api_key' ) );
		register_setting( $this->option_group, 'algolia_api_key', array( $this, 'sanitize_api_key' ) );
		// register_setting( $this->option_group, 'algolia_powered_by_enabled', array( $this, 'sanitize_powered_by_enabled' ) );
	}

	public function application_id_callback() {

		$settings = $this->plugin->get_settings();
		$setting = esc_attr( $settings->get_application_id() );
		$disabled_html = $settings->is_application_id_in_config() ? ' disabled' : '';

		echo "<input type='text' name='algolia_application_id' class='regular-text' value='$setting' $disabled_html/>" .
			'<p class="description" id="home-description">' . __( 'Your Algolia Application ID.', 'algolia' ) . '</p>';
	}

	public function search_api_key_callback() {
		$settings = $this->plugin->get_settings();
		$setting = esc_attr( $settings->get_search_api_key() );
		$disabled_html = $settings->is_search_api_key_in_config() ? ' disabled' : '';

		echo "<input type='text' name='algolia_search_api_key' class='regular-text' value='$setting' $disabled_html/>" .
			'<p class="description" id="home-description">' . __( 'Your Algolia Search-only API key (public).', 'algolia' ) . '</p>';
	}

	public function api_key_callback() {
		$settings = $this->plugin->get_settings();
		$setting = esc_attr( $settings->get_api_key() );
		$disabled_html = $settings->is_api_key_in_config() ? ' disabled' : '';

		echo "<input type='password' name='algolia_api_key' class='regular-text' value='$setting' $disabled_html/>" .
			'<p class="description" id="home-description">' . __( 'Your Algolia ADMIN API key (kept private).', 'algolia' ) . '</p>';
	}

	public function powered_by_enabled_callback() {
		$powered_by_enabled = $this->plugin->get_settings()->is_powered_by_enabled();
		$checked = '';
		if( ! $powered_by_enabled) {
			$checked = ' checked';
		}
		echo "<input type='checkbox' name='algolia_powered_by_enabled' value='no' " . $checked . " />" .
			'<p class="description" id="home-description">' . __( 'This will remove the Algolia logo from the autocomplete and the search page. We require that you keep the Algolia logo if you are using a free Hacker plan.', 'algolia' ) . '</p>';
	}

	public function sanitize_application_id( $value ) {
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				__( 'Application ID should not be empty.', 'algolia' )
			);

		}

		return $value;
	}

	public function sanitize_search_api_key( $value ) {
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				__( 'Search-only API key should not be empty.', 'algolia' )
			);
		}

		return $value;
	}

	public function sanitize_api_key( $value ) {
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				__( 'API key should not be empty', 'algolia' )
			);
		}
		$errors = get_settings_errors( $this->option_group );
		if ( ! empty( $errors ) ) {
			return $value;
		}

		$settings = $this->plugin->get_settings();

		$valid_credentials = true;
		try {
			Algolia_API::assert_valid_credentials( $settings->get_application_id(), $value );
		} catch ( Exception $exception ) {
			$valid_credentials = false;
			$this->plugin->get_logger()->log_error( 'Unable to validate credentials.', $exception );
			add_settings_error(
				$this->option_group,
				'login_exception',
				$exception->getMessage()
			);
		}

		if ( ! $valid_credentials ) {
			add_settings_error(
				$this->option_group,
				'no_connection',
				__(
					'We were unable to authenticate you against the Algolia servers with the provided information. ' .
					'Please ensure that you used an the Admin API key and a valid Application ID.',
					'algolia'
				)
			);
			$settings->set_api_is_reachable( false );
		} else {
			if ( ! Algolia_API::is_valid_search_api_key( $settings->get_application_id(), $settings->get_search_api_key() ) ) {
				add_settings_error(
					$this->option_group,
					'wrong_search_API_key',
					__(
						'It looks like your search API key is wrong. Ensure that the key you entered has only the search capability and nothing else. ' .
						'Also ensure that the key has no limited time validity.',
						'algolia'
					)
				);
				$settings->set_api_is_reachable( false );
			} else {
				add_settings_error(
					$this->option_group,
					'connection_success',
					__( 'We succesfully managed to connect to the Algolia servers with the provided information. Your search API key has also been checked and is OK.', 'algolia' ),
					'updated'
				);
				$settings->set_api_is_reachable( true );
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function sanitize_powered_by_enabled( $value ) {
		return $value === 'no' ? 'no' : 'yes';
	}
	
	/**
	 * Display the page.
	 */
	public function display_page() {
		require_once dirname(__FILE__) . '/partials/form-options.php';
	}

	public function display_errors() {
		settings_errors( $this->option_group );
	}

	public function print_section_settings() {
		echo '<p>' . esc_html__( 'Configure here your Algolia credentials. You can find them in the "API Keys" section of your Algolia dashboard.', 'algolia' ) . '</p>';
		echo '<p>' . esc_html__( 'Once you provide your Algolia Application ID and API key, this plugin will be able to securely communicate with Algolia servers.', 'algolia' ) . ' ' . esc_html__( 'We ensure your information is correct by testing them against the Algolia servers upon save.', 'algolia' ) . '</p>';
		echo '<p>' . sprintf( __( 'No Algolia account yet? <a href="%s">Follow this link</a> to create one for free in a couple of minutes!.', 'algolia' ), 'https://www.algolia.com/users/sign_up?utm_medium=extension&utm_source=WordPress&utm_campaign=admin' ) . '</p>';
	}
}
