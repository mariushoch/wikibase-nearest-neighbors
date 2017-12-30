<?php

namespace Wikibase\NearestNeighbors;

require_once __DIR__ . '/vendor/autoload.php';

function getResFromFile( $fileName, $needleEntityData, $minDistance ) {
	$f = fopen( $fileName, 'r' );
	$header = fgets( $f );

	if ( !$header ) {
		echo "File \"$fileName\" must not be empty.\n";
		exit( 1 );
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

	$processedEntities = 0;
	$lineNumber = 1;
	$cutOff = PHP_INT_MAX;
	$results = [];

	while ( ( $line = fgets( $f ) ) !== false ) {
		$processedEntities++;
		$lineNumber++;

		$colonPos = strpos( $line, ':' );
		$entityId = substr( $line, 0, $colonPos );
		$line = substr( $line, $colonPos + 1 );

		$entityData = $propertyIdEncoder->encodingToIntArray( $line );
		// The byte strings might also contain new lines, thus read more lines if needed.
		while ( $needleChunkCount > count( $entityData ) ) {
			$line .= fgets( $f );
			$lineNumber++;
			$entityData = $propertyIdEncoder->encodingToIntArray( $line );
		}

		if ( $needleChunkCount !== count( $entityData ) ) {
			die( "Found invalid data on line $lineNumber\n" );
		}

		$dist = $hammingDistanceCalculator->getDistance( $entityData, $needleChunkInts ) + $missingFromList;
		if ( $dist <= $cutOff && $dist > $minDistance ) {
			$results[$entityId] = $dist;
		}

		$cutOff = cutOffResults( $results );
	}

	return $results;
}

function cutOffResults( &$results ) {
	if ( count( $results ) <= 50 ) {
		return PHP_INT_MAX;
	}

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

// FIXME: Use getopt or something similar…
// TODO: Make sure this is not Wikidata specific
if ( $argc < 3 || $argv[1] === '--help' || $argv[1] === '-h' ) {
	echo "generateEncodingFromDump.php: Read a Wikidata JSON dump and output minimal encoding of the statements present.\n\n";
	echo "Usage: EntityId EncodingInputFile [EncodingInputFile]* [--min-distance minDistance]\n";

	exit( 1 );
}
$entityReader = new EntityReader();

$needleEntity = file_get_contents( 'https://www.wikidata.org/wiki/Special:EntityData/' . $argv[1] . '.json' );
$needleEntityData = $entityReader->readEntityDataString( $needleEntity );

$encodedFiles = [];
$remainingArgs = array_slice( $argv, 2 );
while ( $remainingArgs && $remainingArgs[0] !== '--min-distance' ) {
	$encodedFiles[] = array_shift( $remainingArgs );
}

if ( isset( $remainingArgs[1] ) ) {
	$minDistance = intval( $remainingArgs[1] );
} else {
	$minDistance = -1;
}

$results = [];

foreach ( $encodedFiles as $encodedFile ) {
	$results = array_merge(
		$results,
		getResFromFile( $encodedFile, $needleEntityData, $minDistance )
	);
}

$displayResults = [];
$i = 0;
foreach ( $results as $id => $row ) {
	$displayResults[$row * 100000 + ++$i] = [ $id, $row ];
}

ksort( $displayResults );
$displayResults = array_splice( $displayResults, 0, 50 );

echo "Id\tDistance\n";
foreach( $displayResults as $res ) {
	echo "$res[0]\t$res[1]\n";
}
