<?php

namespace Wikibase\NearestNeighbors;

require_once __DIR__ . '/vendor/autoload.php';

$entityReader = new EntityReader();

if ( $argc != 3 ) {
	die( "This script takes two arguments: Input file and output file!\n" );
}

$f = fopen( $argv[1], 'r' );
$buffer = '';
$i = 0;
while ( ( $line = fgets( $f ) ) !== false ) {
	$i++;

	if ( strlen( $line ) < 5 ) {
		// Start/ end line
		continue;
	}

	$numericPropertyIds = $entityReader->readLineString( $line );
	
}
