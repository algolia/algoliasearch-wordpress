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
		
		// Todo: We should do this on a option value that tracks down the connection success on every credentials change.
		
		return null !== $this->get_client();
	}

	/**
	 * @return Client|null
	 */
	public function get_client() {
		global $wp_version;

		// Build the UserAgent.
		$ua = ' WordPress ' . ALGOLIA_VERSION
			. '; PHP ' . phpversion()
			. '; WordPress ' . $wp_version;

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
	 * @param $application_id
	 * @param $api_key
	 * 
	 * @throws AlgoliaException
	 */
	public static function assert_valid_credentials( $application_id, $api_key ) {
		$client = new Client( (string) $application_id, (string) $api_key );
		// If this call does not succeed, then the application_ID or API_key is/are wrong.
		// This will raise an exception.
		$client->listUserKeys();
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
		} catch (AlgoliaException $e) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $application_id
	 * @param string $api_key
	 * @param string $search_api_key
	 *
	 * @return bool
	 */
	public static function is_valid_search_api_key( $application_id, $api_key, $search_api_key ) {
		$client = new Client( (string) $application_id, (string) $api_key );
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
