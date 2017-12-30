<?php

namespace Wikibase\NearestNeighbors;

use Wikibase\NearestNeighbors\FieldProviders\PropLinksFieldProvider;
use Wikibase\NearestNeighbors\FieldProviders\WikibaseAllPropertiesFieldProvider;

require_once __DIR__ . '/vendor/autoload.php';

function flushBuffers( array &$buffer ) {
	global $argv;

	if ( isset( $buffer['full'] ) ) {
		file_put_contents( $argv[2], $buffer['full'], FILE_APPEND );
	}
	if ( isset( $buffer['top100'] ) ) {
		file_put_contents( $argv[3], $buffer['top100'], FILE_APPEND );
	}

	$buffer = [];
}

// FIXME: Use getopt or something similar…
// TODO: Make sure this is not Wikidata specific
if ( $argc !== 4 || $argv[1] === '--help' || $argv[1] === '-h' ) {
	echo "generateEncodingFromDump.php: Read a Wikidata JSON dump and output minimal encoding of the statements present.\n\n";
	echo "Usage: InputFile OutputFileFullEncoding OutputFileTop100Encoding\n";

	exit( 1 );
}

$entityReader = new EntityReader();

$fullFields = ( new WikibaseAllPropertiesFieldProvider(
		'https://www.wikidata.org/w/api.php',
		120
	) )->getFields();
$top100Fields = ( new PropLinksFieldProvider(
		'https://www.wikidata.org/w/api.php',
		'Wikidata:Database reports/List of properties/Top100',
		120
	) )->getFields();

file_put_contents( $argv[2], implode( ',', $fullFields ) . "\n" );
file_put_contents( $argv[3], implode( ',', $top100Fields ). "\n" );

$encoders = [
	new PropertyIdEncoder( $fullFields, 'full' ),
	new PropertyIdEncoder( $top100Fields, 'top100' )
];

$partitioningPropertyIdEncoder = new PartitioningPropertyIdEncoder( $encoders );

$f = fopen( $argv[1], 'r' );
$buffer = [];
$i = 0;
while ( ( $line = fgets( $f ) ) !== false ) {
	$i++;

	if ( strlen( $line ) < 5 ) {
		// Start/ end line
		continue;
	}

	$entity = $entityReader->readLineString( $line );
	$encoded = $partitioningPropertyIdEncoder->getEncoded( $entity[1] );

	if ( !isset( $buffer[$encoded[0]] ) || !is_string( $buffer[$encoded[0]] ) ) {
		$buffer[$encoded[0]] = '';
	}

	$buffer[$encoded[0]] .= $entity[0] . ':' . $encoded[1] . "\n";

	// Only write to target file for every 10000 rows
	if ( $i % 10000 === 0 ) {
		flushBuffers( $buffer );

		echo $i . " done\n";
	}
}

flushBuffers( $buffer );