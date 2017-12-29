<?php

namespace Wikibase\NearestNeighbors;

/**
 * Encodes a list of numeric property ids as a (minimal) byte string, according to a given list of fields.
 *
 * A bit at position n is set to 1 iff, the nth property id in $fields is set.
 */
class PropertyIdEncoder {

	/**
	 * @var int[]
	 */
	private $fields;

	/**
	 * @var int
	 */
	private $fieldCount;

	/**
	 * @var int[][]
	 */
	private $fieldChunks;

	/**
	 * @param int $fields
	 */
	public function __construct( array $fields ) {
		$this->fields = $fields;
		$this->fieldCount = count( $fields );
		$this->fieldChunks = array_chunk( $fields, PHP_INT_SIZE * 8 );
	}

	/**
	 * Get a minimal byte string that encodes that the given $numericPropertyIds are present.
	 * A bit at position n is set to 1 iff, the nth property id in $this->fields is set.
	 *
	 * @param int[] $numericPropertyIds
	 *
	 * @return string
	 */
	public function getEncoded( array $numericPropertyIds ) {
		$numericPropertyIds = array_combine( $numericPropertyIds, $numericPropertyIds );

		$encoded = [];
		// TODO: This could probably be done way smarter…
		foreach ( $this->fieldChunks as $fieldChunk ) {
			$int = 0;
			$i = PHP_INT_SIZE * 8;

			foreach ( $fieldChunk as $field ) {
				$i--;
				if ( isset( $numericPropertyIds[$field] ) ) {
					$int = $int | 1 << $i;
				}
			}

			$encoded[] = $int;
		}

		$byteString = call_user_func_array(
			'pack',
			array_merge( [ 'J*' ], $encoded )
		);

		// Shorten encoded string as much as possible
		return substr( $byteString, 0, ceil( $this->fieldCount / 8 ) );
	}

}