<?php

class Algolia_Utils
{
	/**
	 * Retrieve term parents with separator.
	 *
	 * @param int $id Term ID.
	 * @param string $taxonomy
	 * @param string $separator Optional, default is '/'. How to separate terms.
	 * @param bool $nicename Optional, default is false. Whether to use nice name for display.
	 * @param array $visited Optional. Already linked to terms to prevent duplicates.
	 * @return string|WP_Error A list of terms parents on success, WP_Error on failure.
	 */
	public static function get_term_parents( $id, $taxonomy, $separator = '/', $nicename = false, $visited = array() ) {
		$chain = '';
		$parent = get_term( $id, $taxonomy );
		if ( is_wp_error( $parent ) )
			return $parent;

		if ( $nicename )
			$name = $parent->slug;
		else
			$name = $parent->name;

		if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
			$visited[] = $parent->parent;
			$chain .= self::get_term_parents( $parent->parent, $taxonomy, $separator, $nicename, $visited );
		}

		$chain .= $name.$separator;

		return $chain;
	}

	/**
	 * Returns an array like:
	 * array(
	 *    'lvl0' => ['Sales', 'Marketing'],
	 *    'lvl1' => ['Sales > Strategies', 'Marketing > Tips & Tricks']
	 *    ...
	 * );.
	 *
	 * This is useful when building hierarchical menus.
	 * @see https://community.algolia.com/instantsearch.js/documentation/#hierarchicalmenu
	 *
	 * @param array  $terms
	 * @param string $taxonomy
	 * @param string $separator
	 *
	 * @return array
	 */
	public static function get_taxonomy_tree( array $terms, $taxonomy, $separator = ' > ' ) {
		$termIds = wp_list_pluck( $terms, 'term_id' );

		$parents = array();
		foreach ( $termIds as $termId ) {
			$path = self::get_term_parents( $termId, $taxonomy, $separator );
			$parents[] = rtrim( $path, $separator );
		}

		$terms = array();
		foreach ( $parents as $parent ) {
			$levels = explode( $separator, $parent );

			$previousLvl = '';
			foreach ( $levels as $index => $level ) {
				$terms[ 'lvl' . $index ][] = $previousLvl . $level;
				$previousLvl .= $level . $separator;

				// Make sure we have not duplicate.
				// The call to `array_values` ensures that we do not end up with an object in JSON.
				$terms[ 'lvl' . $index ] = array_values( array_unique( $terms[ 'lvl' . $index ] ) );
			}
		}

		return $terms;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
	public static function get_post_images( $post_id ) {
		$images = array();
		$post_thumbnail_id = get_post_thumbnail_id( (int) $post_id );
		$sizes = get_intermediate_image_sizes();
		if ( $post_thumbnail_id ) {
			foreach ( $sizes as $size ) {
				$info = wp_get_attachment_image_src( $post_thumbnail_id, $size );
				if ( ! $info ) {
					continue;
				}

				$images[ $size ] = array(
					'url'         => $info[0],
					'width'       => $info[1],
					'height'      => $info[2],
				);
			}
		}

		return (array) apply_filters( 'algolia_get_post_images', $images );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_loopback_request_args( array $args = array() ) {
		$request_args = array(
			'timeout'   	=> 1,
			'blocking'  	=> false,
			'sslverify' 	=> apply_filters( 'https_local_ssl_verify', true ),
			'headers'   	=> array(
				'cookie' => self::get_current_cookies_for_loopback_request(),
			),
		);

		$request_args = array_merge( $request_args, $args );

		return (array) apply_filters( 'algolia_loopback_request_args', $request_args );
	}

	/**
	 * @return string
	 */
	public static function get_current_cookies_for_loopback_request() {
		if ( ! is_array( $_COOKIE ) ) {
			return '';
		}

		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			// Only accept string Cookie entries.
			if ( ! is_string( $value ) ) {
				continue;
			}

			// Do not allow non WordPress Cookie entries.
			if ( strpos( $name, 'wordpress_' ) !== 0 ) {
				continue;
			}
			$cookies[] = "$name=" . urlencode( $value );
		}

		return implode( '; ', $cookies );
	}

	/**
	 * @return string
	 */
	public static function get_loopback_request_url() {
		$scheme = ( defined( 'ALGOLIA_LOOPBACK_HTTP' ) && ALGOLIA_LOOPBACK_HTTP === true ) ? 'http' : 'admin' ;
		
		return admin_url( 'admin-post.php', $scheme );
	}
}
