<?php

namespace Wikibase\NearestNeighbors;
use RuntimeException;

/**
 * Service for finding the nearest neighbor to a given entity.
 */
class NearestNeighborFinder {

	/**
	 * @var string[]
	 */
	private $encodedFiles;

	/**
	 * @var string
	 */
	private $specialEntityDataUrl;

	/**
	 * @var string[]|null
	 */
	private $encodedFilesSorted = null;

	public function __construct( array $encodedFiles, $specialEntityDataUrl ) {
		$this->encodedFiles = $encodedFiles;
		$this->specialEntityDataUrl = $specialEntityDataUrl;
	}

	/**
	 * @param string $needleEntityIdSerialization
	 * @param int $minDistance
	 * @return array[] Sorted array of (entity id serialization, distance) pairs
	 */
	public function getNearestNeighbor( $needleEntityIdSerialization, $minDistance ) {
		$entityReader = new EntityReader();

		$needleEntity = file_get_contents(
			str_replace(
				'$1',
				urlencode( trim( $needleEntityIdSerialization ) ),
				$this->specialEntityDataUrl
			)
		);
		if ( $needleEntity === false ) {
			throw new RuntimeException( "Couldn't retrieve needle entity." );
		}
		$needleEntityData = $entityReader->readEntityDataString( $needleEntity );

		$results = [];
		$maxDistance = PHP_INT_MAX;

		foreach ( $this->getEncodedFilesSorted() as $encodedFile ) {
			$results = array_merge(
				$results,
				$this->getResFromFile( $encodedFile, $needleEntityData, $minDistance, $maxDistance )
			);
		}

		$displayResults = [];
		$i = 0;
		foreach ( $results as $id => $row ) {
			$displayResults[$row * 100000 + ++$i] = [ $id, $row ];
		}

		ksort( $displayResults );
		$displayResults = array_splice( $displayResults, 0, 50 );

		return $displayResults;
	}

	private function getEncodedFilesSorted() {
		if ( $this->encodedFilesSorted ) {
			return $this->encodedFilesSorted;
		}

		$i = 0;
		foreach ( $this->encodedFiles as $encodedFile ) {
			$this->encodedFilesSorted[$this->getEncodeLength( $encodedFile ) * 100000 + ++$i ] = $encodedFile;
		}

		// Make sure we start with the longest encodings… try to reduce $maxDistance so far
		// that we don't need to search all files.
		krsort( $this->encodedFilesSorted );

		return $this->encodedFilesSorted;
	}

	private function getEncodeLength( $fileName ) {
		$f = fopen( $fileName, 'rb' );
		if ( !$f ) {
			throw new RuntimeException( "File \"$fileName\" can't be read." );
		}

		$header = fgets( $f );
		if ( !$header ) {
			throw new RuntimeException( "File \"$fileName\" must not be empty." );
		}

		$entityOrder = array_map( 'intval', explode( ',', $header ) );

		fclose( $f );
		return count( $entityOrder );
	}

	private function getResFromFile( $fileName, $needleEntityData, $minDistance, &$maxDistance ) {
		$f = fopen( $fileName, 'rb' );
		if ( !$f ) {
			throw new RuntimeException( "File \"$fileName\" can't be read." );
		}

		$header = fgets( $f );
		if ( !$header ) {
			throw new RuntimeException( "File \"$fileName\" must not be empty." );
		}

		$hammingDistanceCalculator = new IntArrayHammingDistanceCalculator();

		$entityOrder = array_map( 'intval', explode( ',', $header ) );
		// Some property ids might not be present in the current encoding file at all,
		// thus we always have at least this distance.
		$missingFromList = count( array_diff( $needleEntityData[1], $entityOrder ) );

		$propertyIdEncoder = new PropertyIdEncoder( $entityOrder, 'needleEncoder' );
		$needle = $propertyIdEncoder->getEncoded( $needleEntityData[1] )[1];

		$needleChunkInts = $propertyIdEncoder->encodingToIntArray( $needle );
		$needleChunkCount = count( $needleChunkInts );

		$entityNumber = 0;
		$results = [];
		$dataLength = ceil( $propertyIdEncoder->getFieldCount() / 8 );

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

		fclose( $f );
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
