<?php

/**
 * Push and re-index records into Algolia indices.
 */
class Algolia_CLI extends \WP_CLI_Command {

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	public function __construct() {
		$this->plugin = Algolia_Plugin::get_instance();
	}

	/**
	 * Push all records to Algolia for a given index.
	 *
	 * ## OPTIONS
	 *
	 * [<indexName>]
	 * : The id of the index without the prefix.
	 *
	 * [--clear]
	 * : Clear all existing records prior to pushing the records.
	 *
	 * [--all]
	 * : Re-indexes all the enabled indices.
	 *
	 * ## EXAMPLES
	 *
	 *     wp algolia re-index
	 *
	 * @alias re-index
	 */
	public function reindex( $args, $assoc_args ) {
		if ( ! $this->plugin->get_api()->is_reachable() ) {
			WP_CLI::error( 'The configuration for this website does not allow to contact the Algolia API.' );
		}

		$index_id = isset( $args[0] ) ? $args[0] : null;
		$clear    = WP_CLI\Utils\get_flag_value( $assoc_args, 'clear' );
		$all      = WP_CLI\Utils\get_flag_value( $assoc_args, 'all' );

		if ( ! $index_id && ! $all ) {
			WP_CLI::error( 'You need to either provide an index name or specify the --all argument to re-index all enabled indices.' );
		}

		if ( $index_id && $all ) {
			WP_CLI::error( 'You can not give both an index name and the --all parameter.' );
		}

		if ( $all ) {
			$indices = $this->plugin->get_indices(
				array(
					'enabled' => true,
				)
			);
		} else {
			$index = $this->plugin->get_index( $index_id );
			if ( ! $index ) {
				WP_CLI::error( sprintf( 'Index with id "%s" does not exist. Make sure you don\'t include the prefix.', $index_id ) );
			}
			$indices = array( $index );
		}

		foreach ( $indices as $index ) {
			$this->do_reindex( $index, $clear );
		}
	}

	private function do_reindex( Algolia_Index $index, $clear ) {

		if ( $clear ) {
			/* translators: the placeholder will contain the name of the index. */
			WP_CLI::log( sprintf( __( 'About to clear index %s...', 'algolia' ), $index->get_name() ) );
			$index->clear();
			/* translators: the placeholder will contain the name of the index. */
			WP_CLI::success( sprintf( __( 'Correctly cleared index "%s".', 'algolia' ), $index->get_name() ) );
		}

		$total_pages = $index->get_re_index_max_num_pages();

		if ( 0 === $total_pages ) {
			$index->re_index( 1 );
			WP_CLI::success( sprintf( 'Index %s was created but no entries were sent.', $index->get_name() ) );

			return;
		}

		$progress = WP_CLI\Utils\make_progress_bar( sprintf( 'Processing %s pages of results.', $total_pages ), $total_pages );

		$page = 1;
		do {
			$index->re_index( $page++ );
			$progress->tick();
		} while ( $page <= $total_pages );

		$progress->finish();

		WP_CLI::success( sprintf( 'Indexed "%s" pages of results inside index "%s"', $total_pages, $index->get_name() ) );
	}
}
