<?php

class Algolia_Admin_Page_Autocomplete
{
	/**
	 * @var string
	 */
	private $slug = 'algolia';

	/**
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * @var string
	 */
	private $section = 'algolia_section_autocomplete';

	/**
	 * @var string
	 */
	private $option_group = 'algolia_autocomplete';
	
	/**
	 * @var Algolia_Settings
	 */
	private $settings;
	
	/**
	 * @var Algolia_Autocomplete_Config
	 */
	private $autocomplete_config;

	/**
	 * @param Algolia_Settings            $settings
	 * @param Algolia_Autocomplete_Config $autocomplete_config
	 */
	public function __construct( Algolia_Settings $settings, Algolia_Autocomplete_Config $autocomplete_config ) {
		$this->settings = $settings;
		$this->autocomplete_config = $autocomplete_config;
		
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_notices', array( $this, 'display_errors' ) );

		// todo: listen for de-index to remove from autocomplete.
	}

	public function add_page() {
		add_menu_page(
			'Algolia Search',
			__( 'Algolia Search', 'algolia' ),
			'manage_options',
			'algolia',
			array( $this, 'display_page' ),
			''
		);
		add_submenu_page(
			'algolia',
			__( 'Autocomplete', 'algolia' ),
			__( 'Autocomplete', 'algolia' ),
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
			'algolia_autocomplete_enabled',
			__( 'Enable autocomplete', 'algolia' ),
			array( $this, 'autocomplete_enabled_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'algolia_autocomplete_config',
			__( 'Configuration', 'algolia' ),
			array( $this, 'autocomplete_config_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'algolia_autocomplete_enabled', array( $this, 'sanitize_autocomplete_enabled' ) );
		register_setting( $this->option_group, 'algolia_autocomplete_config', array( $this, 'sanitize_autocomplete_config' ) );
	}

	/**
	 * 
	 */
	public function autocomplete_enabled_callback() {
		$value = $this->settings->get_autocomplete_enabled();
		$indices = $this->autocomplete_config->get_form_data();
		$checked = 'yes' === $value ? 'checked ' : '';
		$disabled = empty( $indices ) ? 'disabled ' : '';

		echo "<input type='checkbox' name='algolia_autocomplete_enabled' value='yes' $checked $disabled/>";
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function sanitize_autocomplete_enabled( $value ) {

		add_settings_error(
			$this->option_group,
			'autocomplete_enabled',
			__( 'Autocomplete configuration has been saved.', 'algolia' ),
			'updated'
		);

		return 'yes' === $value ? 'yes' : 'no';
	}

	public function autocomplete_config_callback() {
		$indices = $this->autocomplete_config->get_form_data();

		require_once dirname( __FILE__ ) . '/partials/page-autocomplete-config.php';
	}

	public function sanitize_autocomplete_config( $values ) {
		return $this->autocomplete_config->sanitize_form_data( $values );
	}

	/**
	 * Display the page.
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/page-autocomplete.php';
	}

	/**
	 * Display the errors.
	 */
	public function display_errors() {
		settings_errors( $this->option_group );
		
		if ( defined( 'ALGOLIA_HIDE_HELP_NOTICES' ) && ALGOLIA_HIDE_HELP_NOTICES ) {
			return;
		} 

		$is_enabled = 'yes' === $this->settings->get_autocomplete_enabled();
		$indices = $this->autocomplete_config->get_config();

		if ( true === $is_enabled && empty( $indices ) ) {
			echo '<div class="error notice">
					  <p>' . esc_html__( 'You have enabled the Algolia Autocomplete feature but did not choose any index to search in.', 'algolia' ) . '</p>
					  <p>' . sprintf( __( 'Please select one or multiple indices on the <a href="%s">Algolia: Autocomplete configuration page</a>.', 'algolia' ), admin_url( 'admin.php?page=' . $this->slug ) ) . '</p>
				  </div>';
		}
	}

	/**
	 * Prints the section text.
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'The autocomplete feature adds a find-as-you-type dropdown menu to your search bar(s).', 'algolia' ) . '</p>';
	}
}
