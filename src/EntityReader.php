<?php

namespace Wikibase\NearestNeighbors;

use RuntimeException;

/**
 * Reads entity JSON and gets all relevant information from there (ids, statement property id).
 */
class EntityReader {

	/**
	 * @param string $str
	 *
	 * @return array First value is the serialized entity id, second value is an array of the (numerical) property ids of the statements
	 */
	public function readLineString( $str ) {
		if ( substr( $str, -2, 1 ) === ',' ) {
			$str = substr( $str, 0, -2 );
		} else {
			$str = substr( $str, 0, -1 );
		}

		$entity = json_decode( $str, true );
		if ( !is_array( $entity ) ) {
			throw new RuntimeException( "Couldn't decode entity: " . json_last_error_msg() );
		}

		return $this->getIdAndClaimsFromEntityArray( $entity );
	}

	/**
	 * @param string $str
	 *
	 * @return array First value is the serialized entity id, second value is an array of the (numerical) property ids of the statements
	 */
	public function readEntityDataString( $str ) {
		$entity = json_decode( $str, true );
		if ( !is_array( $entity ) ) {
			throw new RuntimeException( "Couldn't decode entity: " . json_last_error_msg() );
		}
		if ( !isset( $entity['entities'] ) ) {
			throw new RuntimeException( "Couldn't decode entity: Got empty JSON" );
		}
		$entity = end( $entity['entities'] );

		return $this->getIdAndClaimsFromEntityArray( $entity );
	}

	/**
	 * @param array $entity Assumed to at least contain an "id" key
	 * @return array
	 */
	private function getIdAndClaimsFromEntityArray( array $entity ) {
		if ( !isset( $entity['claims'] ) ) {
			return [ $entity['id'], [] ];
		}

		return [ $entity['id'], $this->getNumericalPropertyIds( $entity ) ];
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