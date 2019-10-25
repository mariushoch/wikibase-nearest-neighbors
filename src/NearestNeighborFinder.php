<?php

namespace Wikibase\NearestNeighbors;
use Wikibase\NearestNeighbors\EncodedFileDistance\EncodedFileDistanceCalculator;
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

			$maxDistanceResults = $results;
			sort( $maxDistanceResults );
			$maxDistance = max( array_splice( $maxDistanceResults, 0, 50 ) );
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

	private function getResFromFile( $fileName, $needleEntityData, $minDistance, $maxDistance ) {
		$f = fopen( $fileName, 'rb' );
		if ( !$f ) {
			throw new RuntimeException( "File \"$fileName\" can't be read." );
		}

		$header = fgets( $f );
		if ( !$header ) {
			throw new RuntimeException( "File \"$fileName\" must not be empty." );
		}
		fclose( $f );

		$entityOrder = array_map( 'intval', explode( ',', $header ) );
		// Some property ids might not be present in the current encoding file at all,
		// thus we always have at least this distance.
		$missingFromList = count( array_diff( $needleEntityData[1], $entityOrder ) );

		$propertyIdEncoder = new PropertyIdEncoder( $entityOrder, 'needleEncoder' );
		$needle = $propertyIdEncoder->getEncoded( $needleEntityData[1] )[1];

		$needleChunkInts = $propertyIdEncoder->encodingToIntArray( $needle );
		$dataLength = ceil( $propertyIdEncoder->getFieldCount() / 8 );

		$encodedFileDistanceCalculator = new EncodedFileDistanceCalculator();
		return $encodedFileDistanceCalculator->getCloseEntries(
			$fileName,
			$needleChunkInts,
			$propertyIdEncoder,
			$dataLength,
			$minDistance,
			$maxDistance,
			$missingFromList
		);
	}

}
