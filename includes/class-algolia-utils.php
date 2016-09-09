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
}
