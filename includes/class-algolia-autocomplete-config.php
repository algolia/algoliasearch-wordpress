<?php

class Algolia_Autocomplete_Config
{
	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	/**
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @return array
	 */
	public function get_form_data() {
		$indices = $this->plugin->get_indices( array( 'enabled' => true ) );
		$config = array();

		$existing_config = $this->get_config();
		foreach ( $indices as $index ) {
			/** @var Algolia_Index $index */
			$index_config = $this->extract_index_config( $existing_config, $index->get_id() );
			if ( $index_config ) {
				// If there is an existing configuration, add it.
				$config[] = $index_config;
				continue;
			}

			$default_config = $index->get_default_autocomplete_config();
			$default_config['enabled'] = false;

			$config[] = $default_config;
		}

		usort( $config, function( $a, $b ) {
			return $a['position'] > $b['position'];
		} );
		
		return $config;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public function sanitize_form_data( $data ) {
		
		if ( ! is_array( $data ) ) {
			return array();
		}
		
		$sanitized = array();

		foreach ( $data as $index_id => $config ) {
			$index = $this->plugin->get_index( $index_id );

			if( null === $index || ! $index->is_enabled() ) {
				continue;
			}

			// Remove disabled indices.
			if ( ! isset( $config['enabled'] ) ) {
				continue;
			}

			$sanitized[] = array_merge(
				$index->get_default_autocomplete_config(),
				array(
					'position'        => (int) $config['position'],
					'max_suggestions' => (int) $config['max_suggestions'],
				)
			);
		}

		return $sanitized;
	}

	/**
	 * @param array $config
	 * @param string $index_id
	 *
	 * @return mixed|void
	 */
	private function extract_index_config( array $config, $index_id )
	{
		foreach ( $config as $entry ) {
			if ( $index_id === $entry['index_id'] ) {
				return $entry;
			}
		}

		return;
	}

	/**
	 * @return array
	 */
	public function get_config() {
		$settings = $this->plugin->get_settings();
		$config = $settings->get_autocomplete_config();
		foreach ( $config as $key => &$entry ) {
			if ( ! isset( $entry['index_id'] ) ) {
				unset( $config[ $key ] );
				continue;
			}
			
			$index = $this->plugin->get_index( $entry['index_id'] );
			if ( null === $index || ! $index->is_enabled() ) {
				unset( $config[ $key ] );
				continue;
			}

			$entry['enabled'] = true;
		}

		$config = (array) apply_filters( 'algolia_autocomplete_config', $config );

		// Remove manually disabled indices.
		$config = array_filter( $config, function( $item ) {
			return (bool) $item['enabled'];
		} );

		// Sort the indices.
		usort( $config, function( $a, $b ) {
			return $a['position'] > $b['position'];
		} );

		return $config;
	}
}
