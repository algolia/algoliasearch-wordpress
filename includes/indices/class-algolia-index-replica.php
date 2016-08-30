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
	public function __construct( $attribute_name, $order )
	{
		$this->attribute_name = (string) $attribute_name;

		if ( self::ORDER_ASC !== $order && self::ORDER_DESC !== $order ) {
			throw new InvalidArgumentException( 'Order should be one of \'asc\' or \'desc\'.' );
		}
		$this->order = $order;
	}

	/**
	 * @param $master_index_name
	 *
	 * @return string
	 */
	public function get_index_name( $master_index_name ) {
		return (string) $master_index_name . '_' . $this->attribute_name . '_' . $this->order;
	}
	
	public function get_ranking() {
		return array( $this->order . '(' . $this->attribute_name . ')', 'typo', 'geo', 'words', 'filters', 'proximity', 'attribute', 'exact', 'custom' );
	}
}
