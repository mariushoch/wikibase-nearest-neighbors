<?php

namespace Wikibase\NearestNeighbors;

use Wikibase\NearestNeighbors\FieldProviders\PropertyUsageFileFieldProvider;
use Wikibase\NearestNeighbors\FieldProviders\WikibaseAllPropertiesFieldProvider;

require_once __DIR__ . '/vendor/autoload.php';

function flushBuffers( array &$buffers ) {
	global $encodersToFile;

	foreach ( $buffers as $name => $buffer ) {
		file_put_contents( $encodersToFile[$name], $buffer, FILE_APPEND );
	}

	$buffers = [];
}

// FIXME: Use getopt or something similar…
if ( $argc < 3 || $argv[1] === '--help' || $argv[1] === '-h' ) {
	echo "generateEncodingFromDump.php: Read a JSON dump and output minimal encoding of the statements present.\n\n";
	echo "Usage: InputFile OutputFileFullEncoding [PropertyUsageFile] [N:OutputFileTopN]*\n";

	exit( 1 );
}

$entityReader = new EntityReader();

$fullFields = ( new WikibaseAllPropertiesFieldProvider(
		'https://www.wikidata.org/w/api.php',
		120
	) )->getFields();

file_put_contents( $argv[2], implode( ',', $fullFields ) . "\n" );

$encoders = [
	new PropertyIdEncoder( $fullFields, 'full' )
];
$encodersToFile = [ 'full' => $argv[2] ];

$remainingArgs = array_slice( $argv, 3 );
if ( $remainingArgs ) {
	$propertyUsageFile = array_shift( $remainingArgs );
	$propertyUsageProvider = new PropertyUsageFileFieldProvider( $propertyUsageFile );
}

while ( $remainingArgs ) {
	$parts = explode( ':', array_shift( $remainingArgs ), 2 );
	if ( count( $parts ) < 2 ) {
		echo "Invalid output file. Please see --help.\n";
		exit( 1 );
	}

	$topCount = intval( $parts[0] );
	$fileName = $parts[1];

	$fields = array_slice( $propertyUsageProvider->getFields(), 0, $topCount );
	$encoders[] = new PropertyIdEncoder( $fields, strval( $topCount ) );
	$encodersToFile[$topCount] = $fileName;

	file_put_contents( $fileName, implode( ',', $fields ). "\n" );
}

$partitioningPropertyIdEncoder = new PartitioningPropertyIdEncoder( $encoders );

$f = fopen( $argv[1], 'r' );
$buffers = [];
$i = 0;
while ( ( $line = fgets( $f ) ) !== false ) {
	$i++;

	if ( strlen( $line ) < 5 ) {
		// Start/ end line
		continue;
	}

	$entity = $entityReader->readLineString( $line );
	$encoded = $partitioningPropertyIdEncoder->getEncoded( $entity[1] );

	if ( !isset( $buffers[$encoded[0]] ) || !is_string( $buffers[$encoded[0]] ) ) {
		$buffers[$encoded[0]] = '';
	}

	$buffers[$encoded[0]] .= $entity[0] . ':' . $encoded[1] . "\n";

	// Only write to target file for every 10000 rows
	if ( $i % 10000 === 0 ) {
		flushBuffers( $buffers );

		file_put_contents( 'php://stderr', "Processed $i entities.\n", FILE_APPEND );
	}
}

flushBuffers( $buffers );