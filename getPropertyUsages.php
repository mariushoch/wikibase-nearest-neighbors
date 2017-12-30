<?php

namespace Wikibase\NearestNeighbors;

require_once __DIR__ . '/vendor/autoload.php';

if ( $argc < 2 || $argv[1] === '--help' || $argv[1] === '-h' ) {
	echo "getPropertyUsages.php: Read a JSON dump and output a list of all property ids and on how many entities these are used in statements.\n\n";
	echo "Usage: InputFile [OutputFile]\n";

	exit( 1 );
}

$entityReader = new EntityReader();

$f = fopen( $argv[1], 'r' );
$i = 0;
while ( ( $line = fgets( $f ) ) !== false ) {
	$i++;

	if ( strlen( $line ) < 5 ) {
		// Start/ end line
		continue;
	}

	$propertyIds = $entityReader->readLineString( $line )[1];

	foreach ( $propertyIds as $id ) {
		if ( isset( $propertyCounts[$id] ) ) {
			$propertyCounts[$id]++;
		} else {
			$propertyCounts[$id] = 1;
		}
	}
	
	if ( $i % 10000 === 0 ) {
		file_put_contents( 'php://stderr', "Processed $i entities.\n", FILE_APPEND );
	}
}

$propertyCounts = array_flip( $propertyCounts );
krsort( $propertyCounts );

$out = "Count\tId\n";
foreach ( $propertyCounts as $count => $id ) {
	$out .= "$count\tP$id\n";
}

if ( isset( $argv[2] ) ) {
	file_put_contents( $argv[2], $out );
} else {
	echo $out;
}