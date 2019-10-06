<?php

namespace Wikibase\NearestNeighbors\FieldsProvider;
use RuntimeException;

/**
 * Get a field mapping from a given PropertyUsageFile (as obtained from getPropertyUsages).
 */
class PropertyUsageFileFieldsProvider implements FieldsProvider {

	/**
	 * @var string
	 */
	private $fileName;

	/**
	 * @var int[]|null
	 */
	private $fields = null;

	/**
	 * @param string $fileName
	 */
	public function __construct( $fileName ) {
		$this->fileName = $fileName;
	}

	/**
	 * @return int[] Guaranteed to be sorted by number of usages.
	 */
	public function getFields() {
		if ( !$this->fields ) {
			$this->readFromFile();
		}

		return $this->fields;
	}

	private function readFromFile() {
		$f = fopen( $this->fileName, 'r' );
		$propertyIds = [];

		// Skip the first (header) line
		fgets( $f );

		$lineNumber = 1;
		while ( ( $line = fgets( $f ) ) !== false ) {
			$lineNumber++;

			if ( strlen( $line ) < 2 ) {
				continue;
			}

			$id = explode( "\t", $line, 2 )[1];
			$id = trim( $id );
			if ( !preg_match( '/^P[1-9]\d{0,9}\z/i', $id ) ) {
				throw new RuntimeException( "PropertyUsageFileFieldsProvider: Invalid line $lineNumber." );
			}

			$propertyIds[] = $id;
		}

		$this->fields = array_map( function( $propertyIdSerialization ) {
			return (int) substr( $propertyIdSerialization, 1 );
		}, $propertyIds );
	}

}
