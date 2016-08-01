<?php

class Algolia_Admin_Page_Native_Search
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
	private $section = 'algolia_section_native_search';

	/**
	 * @var string
	 */
	private $option_group = 'algolia_native_search';

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
	}

	public function add_page() {
		add_menu_page(
			'Algolia search',
			__( 'Algolia Search', 'algolia' ),
			'manage_options',
			'algolia',
			array( $this, 'display_page' ),
			'',
			30
		);
		add_submenu_page(
			'algolia',
			__( 'Native Search', 'algolia' ),
			__( 'Native Search', 'algolia' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
	}

	public function add_settings() {
		add_settings_section(
			$this->section,
			__( 'Native Search', 'algolia' ),
			array( $this, 'print_section_settings' ),
			$this->slug
		);

		add_settings_field(
			'algolia_override_native_search',
			__( 'Override native search', 'algolia' ),
			array( $this, 'override_native_search_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'algolia_native_search_index_id',
			__( 'Search index to use', 'algolia' ),
			array( $this, 'native_search_index_id_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'algolia_override_native_search', array( $this, 'sanitize_override_native_search' ) );
		register_setting( $this->option_group, 'algolia_native_search_index_id', array( $this, 'sanitize_native_search_index_id' ) );
	}

	public function override_native_search_callback()
	{
		$indices = $this->plugin->get_indices( array(
			'enabled'  => true,
			'contains' => 'posts',
		) );

		$value = $this->plugin->get_settings()->get_override_native_search();
		$checked = 'yes' === $value ? 'checked ' : '';
		$disabled = empty( $indices ) ? 'disabled ' : '';

		echo "<input type='checkbox' name='algolia_override_native_search' value='yes' $checked $disabled/>";
	}

	public function native_search_index_id_callback()
	{
		$indices = $this->plugin->get_indices( array(
			'enabled'  => true,
			'contains' => 'posts',
		) );

		$disabled = empty( $indices ) ? 'disabled ' : '';

		$options = '';
		if ( !empty( $indices ) ) {
			$index_id = $this->plugin->get_settings()->get_native_search_index_id();
			foreach ( $indices as $index ) {
				$selected = $index_id === $index->get_id() ? ' selected="selected"': '';
				$options .= '<option value="' . esc_attr( $index->get_id() ) . '" ' . $selected . '>' . esc_html( $index->get_admin_name() ) . '</option>';
			}
		}

		echo "<select name=\"algolia_native_search_index_id\" $disabled>" . $options . '</select><br />' .
			'<p class="description" id="home-description">' . __( 'Configure the index used to replace the native search.<br />Wordpress uses "Searchable posts" by default.', 'algolia' ) . '</p>';
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function sanitize_override_native_search( $value ) {

		if ( 'yes' === $value ) {
			add_settings_error(
				$this->option_group,
				'native_search_enabled',
				__( 'WordPress search is now based on Algolia!', 'algolia' ),
				'updated'
			);
		} else {
			add_settings_error(
				$this->option_group,
				'native_search_disabled',
				__( 'You chose to keep the WordPress native search instead of Algolia. If you are using the autocomplete feature of the plugin we highly recommend you turn Algolia search on instead of the WordPress native search.', 'algolia' ),
				'updated'
			);
		}

		return 'yes' === $value ? 'yes' : 'no';
	}

	public function sanitize_native_search_index_id( $value ) {
		$index = $this->plugin->get_index( $value );
		if ( null === $index || ! $index->contains_only( 'posts' ) ) {
			add_settings_error(
				$this->option_group,
				'wrong_index_id',
				__( 'The index you have chosen does not seem to be synced anymore.', 'algolia' )
			);

			return '';
		}

		return $value;
	}

	/**
	 * Display the page.
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/form-options.php';
	}

	/**
	 * Display the errors.
	 */
	public function display_errors() {
		settings_errors( $this->option_group );
	}

	/**
	 * Prints the section text.
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'By enabling this plugin to override the native WordPress search, your search results will be powered by Algolia\'s typo-tolerant & relevant search algorithms.', 'algolia' ) . '</p>';

		$indices = $this->plugin->get_indices( array(
			'enabled'  => true,
			'contains' => 'posts',
		) );

		if ( empty( $indices ) ) {
			echo '<div class="error-message">' .
					__( 'You have no index containing only posts yet. Please index some content on the `Indexing` page.', 'algolia' ) .
					'</div>';
		}
	}
}
