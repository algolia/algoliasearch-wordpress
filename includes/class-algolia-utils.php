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

		if ( get_post_type( $post_id ) === 'attachment') {
		    $post_thumbnail_id = (int) $post_id;
        } else {
		    $post_thumbnail_id = get_post_thumbnail_id( (int) $post_id );
        }

		if ( $post_thumbnail_id ) {
            $sizes = (array) apply_filters( 'algolia_post_images_sizes', array( 'thumbnail' ) );
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

	public static function prepare_content( $content ) {
        $content = self::remove_content_noise( $content );

	    return strip_tags( $content );
    }

    public static function remove_content_noise( $content ) {
	    $noise_patterns = array(
            // strip out comments.
            "'<!--(.*?)-->'is",
            // strip out cdata.
            "'<!\[CDATA\[(.*?)\]\]>'is",
            // Per sourceforge http://sourceforge.net/tracker/?func=detail&aid=2949097&group_id=218559&atid=1044037
            // Script tags removal now preceeds style tag removal.
            // strip out <script> tags
            "'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is",
            "'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is",
            // strip out <style> tags.
            "'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is",
            "'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is",
            // strip out preformatted tags.
            "'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is",
            // strip out <pre> tags.
            "'<\s*pre[^>]*[^/]>(.*?)<\s*/\s*pre\s*>'is",
            "'<\s*pre\s*>(.*?)<\s*/\s*pre\s*>'is",
        );

        // If there is ET builder (Divi), remove shortcodes.
        if ( function_exists( 'et_pb_is_pagebuilder_used' ) ) {
            $noise_patterns[] = '/\[\/?et_pb.*?\]/';
        }

        $noise_patterns = (array) apply_filters( 'algolia_strip_patterns', $noise_patterns );

	    foreach ( $noise_patterns as $pattern ) {
	        $content = preg_replace( $pattern, '', $content );
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return array
     */
    public static function explode_content( $content ) {
        $max_size = 2000;
        if ( defined( 'ALGOLIA_CONTENT_MAX_SIZE' ) ) {
            $max_size = (int) ALGOLIA_CONTENT_MAX_SIZE;
        }

        $parts = array();
        $prefix = '';
        while ( true ) {
            $content = trim( (string) $content );
            if ( strlen( $content ) <= $max_size ) {
                $parts[] = $prefix . $content;

                break;
            }

            $offset = -( strlen( $content ) - $max_size );
            $cutAtPosition = strrpos( $content, ' ', $offset);

            if ( false === $cutAtPosition ) {
                $cutAtPosition = $max_size;
            }
            $parts[] =  $prefix . substr( $content, 0, $cutAtPosition );
            $content =  substr( $content, $cutAtPosition );

            $prefix = '… ';
        }

        return $parts;
    }
}
