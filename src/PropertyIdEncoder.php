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
	 * @var string
	 */
	private $name;

	/**
	 * @var string|null
	 */
	private $appendByteCache = null;

	/**
	 * @param int $fields
	 * @param string $name
	 */
	public function __construct( array $fields, $name ) {
		$this->fields = $fields;
		$this->fieldCount = count( $fields );
		$this->fieldChunks = array_chunk( $fields, PHP_INT_SIZE * 8 );
		$this->name = $name;
	}

	/**
	 * Get a minimal byte string that encodes that the given $numericPropertyIds are present.
	 * A bit at position n is set to 1 iff, the nth property id in $this->fields is set.
	 *
	 * @param int[] $numericPropertyIds
	 *
	 * @return array First value denotes whether all properties could be encoded, second value is the byte string
	 */
	public function getEncoded( array $numericPropertyIds ) {
		$numericPropertyIds = array_combine( $numericPropertyIds, $numericPropertyIds );

		$encoded = [];
		$covered = 0;
		foreach ( $this->fieldChunks as $fieldChunk ) {
			$int = 0;
			$i = PHP_INT_SIZE * 8;

			foreach ( $fieldChunk as $field ) {
				$i--;
				if ( isset( $numericPropertyIds[$field] ) ) {
					$covered++;
					$int = $int | 1 << $i;
				}
			}

			$encoded[] = $int;
		}

		$byteString = call_user_func_array(
			'pack',
			array_merge( [ 'J*' ], $encoded )
		);

		// Trim encoded string as much as possible
		return [
			$covered === count( $numericPropertyIds ),
			substr( $byteString, 0, ceil( $this->fieldCount / 8 ) )
		];
	}

	/**
	 * Reads encoded bytes (as obtained via "getEncoded") and converts them to
	 * an integer array (that can be used for hamming distance computation).
	 *
	 * @param str $encodedBytes
	 * @return int[]
	 */
	public function encodingToIntArray( $encodedBytes ) {
		if ( $this->appendByteCache === null ) {
			$missingBytes = ( PHP_INT_SIZE * 8 - ( $this->fieldCount % ( PHP_INT_SIZE * 8 ) ) ) % ( PHP_INT_SIZE * 8 );
			$this->appendByteCache = str_repeat( "\0", floor( $missingBytes / 8 ) );
		}

		return unpack( 'J*', $encodedBytes . $this->appendByteCache );
	}

	/**
	 * @return int
	 */
	public function getFieldCount() {
		return $this->fieldCount;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

}