<?php

namespace Wikibase\NearestNeighbors;
use Wikibase\NearestNeighbors\NearestNeighborFinder;

require_once __DIR__ . '/vendor/autoload.php';

// FIXME: Use getopt or something similar…
// TODO: Make sure this is not Wikidata specific
if ( $argc < 3 || $argv[1] === '--help' || $argv[1] === '-h' ) {
	echo "generateEncodingFromDump.php: Read a Wikidata JSON dump and output minimal encoding of the statements present.\n\n";
	echo "Usage: EntityId EncodingInputFile [EncodingInputFile]* [--min-distance minDistance]\n";

	exit( 1 );
}

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

$finder = new NearestNeighborFinder( $encodedFiles, 'https://www.wikidata.org/wiki/Special:EntityData/$1.json' );
$displayResults = $finder->getNearestNeighbor( $argv[1], $minDistance );

echo "Id\tDistance\n";
foreach( $displayResults as $res ) {
	echo "$res[0]\t$res[1]\n";
}
