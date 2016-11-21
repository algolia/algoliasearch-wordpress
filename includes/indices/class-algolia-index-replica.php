<?php

class Algolia_Index_Replica
{
	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	/**
	 * @var string
	 */
	private $attribute_name;

	/**
	 * @var string
	 */
	private $order;

	/**
	 * @param string $attribute_name
	 * @param string $order
	 */
	public function __construct( $attribute_name, $order ) {
		$this->attribute_name = (string) $attribute_name;

		if ( self::ORDER_ASC !== $order && self::ORDER_DESC !== $order ) {
			throw new InvalidArgumentException( 'Order should be one of \'asc\' or \'desc\'.' );
		}

		$this->order = $order;
	}

	/**
	 * @param Algolia_Index $index
	 *
	 * @return string
	 */
	public function get_display_name( Algolia_Index $index ) {
		$name = $this->get_replica_index_name( $index );
		$name = (string) apply_filters( 'algolia_replica_display_name', $name, $this->attribute_name, $this->order, $index );

		return $name;
	}

	/**
	 * @param Algolia_Index $index
	 *
	 * @return string
	 */
	public function get_replica_index_name( Algolia_Index $index ) {
		return (string) $index->get_name() . '_' . $this->attribute_name . '_' . $this->order;
	}

	/**
	 * @return array
	 */
	public function get_ranking() {
		return array( $this->order . '(' . $this->attribute_name . ')', 'typo', 'geo', 'words', 'filters', 'proximity', 'attribute', 'exact', 'custom' );
	}
}
