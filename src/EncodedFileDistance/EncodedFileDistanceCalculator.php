<?php

namespace Wikibase\NearestNeighbors\EncodedFileDistance;
use RuntimeException;
use Wikibase\NearestNeighbors\IntArrayHammingDistanceCalculator;
use Wikibase\NearestNeighbors\PropertyIdEncoder;

/**
 * Service for getting the entries with minimal distance to a given encoding from an encoded file
 */
class EncodedFileDistanceCalculator {

	public function getCloseEntries(
		$fileName,
		array $needleChunkInts,
		PropertyIdEncoder $propertyIdEncoder,
		$dataLength,
		$minDistance,
		$maxDistance,
		$missingFromList
	) {
		$f = fopen( $fileName, 'rb' );
		// Skip the header line
		fgets( $f );

		$needleChunkCount = count( $needleChunkInts );
		$hammingDistanceCalculator = new IntArrayHammingDistanceCalculator();
		$entityNumber = 0;
		$results = [];

		while ( ( $paddedEntityId = fread( $f, 10 ) ) !== '' ) {
			if ( $maxDistance <= $missingFromList ) {
				// No chance to find a better entity
				fclose( $f );
				return $results;
			}
			$entityNumber++;

			$line = fread( $f, $dataLength );
			$entityData = $propertyIdEncoder->encodingToIntArray( $line );

			if ( $needleChunkCount !== count( $entityData ) ) {
				die( "\"$fileName\": Found invalid data for entity No. $entityNumber\n" );
			}

			$dist = $hammingDistanceCalculator->getDistance( $entityData, $needleChunkInts, $maxDistance - $missingFromList ) + $missingFromList;
			if ( $dist < $maxDistance && $dist > $minDistance ) {
				$entityId = trim( $paddedEntityId );
				$results[$entityId] = $dist;
				if ( count( $results ) > 50 ) {
					$maxDistance = $this->cutOffResults( $results );
				}
			}
		}

		return $results;
	}

	private function cutOffResults( &$results ) {
		$maxValue = 0;
		$maxId = '';
		foreach ( $results as $id => $result ) {
			if ( $result > $maxValue ) {
				$maxValue = $result;
				$maxId = $id;
			}
		}

		unset( $results[$maxId] );
		return $maxValue;
	}
}
