<?php

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Version;

class Algolia_API
{
	/**
	 * @var Client
	 */
	private $client;
	
	/**
	 * @var Algolia_Settings
	 */
	private $settings;

	/**
	 * @param Algolia_Settings $settings
	 */
	public function __construct( Algolia_Settings $settings ) {
		$this->settings = $settings;
	}
	
	public function is_reachable() {
		if ( ! $this->settings->get_api_is_reachable() ) {
			return false;
		}

		try {
			// Here we check that all requirements for the PHP API Client are met
			// If they are not, instantiating the client will throw exceptions.
			$client = $this->get_client();
		} catch ( Exception $e ) {
			return false;
		}

		return null !== $client;
	}

	/**
	 * @return Client|null
	 */
	public function get_client() {
		global $wp_version;

		$integration_name = (string) apply_filters( 'algolia_ua_integration_name', 'Wordpress' );
		$integration_version = (string) apply_filters( 'algolia_ua_integration_version', ALGOLIA_VERSION );
		
		// Build the UserAgent.
		$ua = '; ' . $integration_name . ' integration (' . $integration_version . ')'
			. '; PHP (' . phpversion() . ')'
			. '; Wordpress (' . $wp_version . ')';
		

		Version::$custom_value = $ua;

		$application_id = $this->settings->get_application_id();
		$api_key = $this->settings->get_api_key();
		$search_api_key = $this->settings->get_search_api_key();
		
		if ( empty( $application_id ) || empty( $api_key ) || empty( $search_api_key ) ) {
			return;
		}

		if ( null === $this->client ) {
			$this->client = new Client( $this->settings->get_application_id(), $this->settings->get_api_key() );
		}

		return $this->client;
	}

	/**
	 * @param string $application_id
	 * @param string $api_key
	 *
	 * @throws Exception
	 */
	public static function assert_valid_credentials( $application_id, $api_key ) {
		$client = new Client( (string) $application_id, (string) $api_key );

		// This checks if the API Key is an Admin API key.
		// Admin API keys have no scopes so we need a separate check here.
		try {
			$client->listUserKeys();

			return;
		} catch ( Exception $exception ) {}
		
		
		// If this call does not succeed, then the application_ID or API_key is/are wrong.
		// This will raise an exception.
		$key = $client->getUserKeyACL( (string) $api_key );

		$required_acls = array(
			'addObject',
			'deleteObject',
			'listIndexes',
			'deleteIndex',
			'settings',
			'editSettings',
		);

		$missing_acls = array();
		foreach ( $required_acls as $required_acl ) {
			if ( ! in_array( $required_acl, $key['acl'] ) ) {
				$missing_acls[] = $required_acl;
			}
		}
		
		if ( ! empty( $missing_acls ) ) {
			throw new Exception( 'Your admin API key is missing the following ACLs: ' . implode(', ', $missing_acls ) );
		}
	}
	
	/**
	 * @param string $application_id
	 * @param string $api_key
	 *
	 * @return bool
	 */
	public static function is_valid_credentials( $application_id, $api_key ) {
		try {
			self::assert_valid_credentials( $application_id, $api_key );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $application_id
	 * @param string $search_api_key
	 *
	 * @return bool
	 */
	public static function is_valid_search_api_key( $application_id, $search_api_key ) {
		$client = new Client( (string) $application_id, (string) $search_api_key );
		try {
			// If this call does not succeed, then the application_ID or API_key is/are wrong.
			$acl = $client->getUserKeyACL( $search_api_key );

			// We expect a search only key for security reasons. Will be used in front.
			if ( array( 'search' ) !== $acl['acl'] ) {
				return false;
			}

			// We do expect a search key without unlimited TTL.
			if ( 0 !== $acl['validity'] ) {
				return false;
			}
		} catch (AlgoliaException $e) {
			return false;
		}

		return true;
	}
}
