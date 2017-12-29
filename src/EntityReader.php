<?php

namespace Wikibase\NearestNeighbors;

use RuntimeException;

/**
 * Reads entities (as found in JSON dumps) and yields the ids of the statements on them.
 */
class EntityReader {

	/**
	 * @param string $str
	 *
	 * @return int[] Array of the (numerical) property ids of the statements
	 */
	public function readLineString( $str ) {
		$entity = json_decode( substr( $str, 0, -2 ), true );
		if ( !is_array( $entity ) ) {
			throw new RuntimeException( "Couldn't decode entity: " . json_last_error_msg() );
		}

		if ( !isset( $entity['claims'] ) ) {
			return [];
		}

		return $this->getNumericalPropertyIds( $entity );
	}

	/**
	 * @param array $entity
	 *
	 * @return int[]
	 */
	private function getNumericalPropertyIds( array $entity ) {
		$propertyIds = array_keys( $entity['claims'] );

		return array_map( function( $propertyIdSerialization ) {
			return (int) substr( $propertyIdSerialization, 1 );
		}, $propertyIds );
	}

}