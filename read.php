<?php

namespace Wikibase\NearestNeighbors;

use Wikibase\NearestNeighbors\PropertyIdEncoder;
use Wikibase\NearestNeighbors\FieldProviders\PropLinksFieldProvider;
use Wikibase\NearestNeighbors\FieldProviders\WikibaseAllPropertiesFieldProvider;

require_once __DIR__ . '/vendor/autoload.php';

// FIXME: Use getopt or something similar…
// TODO: Make sure this is not Wikidata specific
if ( $argc !== 4 || $argc[1] === '--help' || $argc[1] === '-h' ) {
	echo "read.php: Read a Wikidata JSON dump and output minimal encoding of the statements present.\n\n";
	echo "Usage: InputFile OutputFileFullEncoding OutputFileTop100Encoding\n";

	exit( 1 );
}

$entityReader = new EntityReader();
$encoders = [
	new PropertyIdEncoder(
		( new WikibaseAllPropertiesFieldProvider(
			'https://www.wikidata.org/w/api.php',
			120
		) )->getFields(),
		'full'
	),
	new PropertyIdEncoder(
		( new PropLinksFieldProvider(
			'https://www.wikidata.org/w/api.php',
			'Wikidata:Database reports/List of properties/Top100',
			120
		) )->getFields(),
		'top100'
	)
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
		file_put_contents( $argv[2], $buffer['full'], FILE_APPEND );
		file_put_contents( $argv[3], $buffer['top100'], FILE_APPEND );
		$buffer = [];

		echo $i . " done\n";
	}
}

file_put_contents( $argv[2], $buffer['full'], FILE_APPEND );
file_put_contents( $argv[3], $buffer['top100'], FILE_APPEND );