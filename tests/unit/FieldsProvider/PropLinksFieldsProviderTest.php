<?php

namespace Wikibase\NearestNeighbors\Tests\FieldsProvider;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\FieldsProvider\PropLinksFieldsProvider;

/**
 * @covers Wikibase\NearestNeighbors\FieldsProvider\PropLinksFieldsProvider
 */
class PropLinksFieldsProviderTest extends PHPUnit_Framework_TestCase {

	public function testGetFields() {
		$provider = new PropLinksFieldsProvider(
			'https://www.wikidata.org/w/api.php',
			'Wikidata:Database reports/List of properties/Top100',
			120
		);

		$fields = $provider->getFields();
		foreach ( $fields as $field ) {
			$this->assertInternalType( 'integer', $field );
		}
		// Make sure numbers are consecutive
		$this->assertSame(
			array_values( $fields ),
			$fields
		);

		// This should be very close 100
		$this->assertTrue( abs( count( $fields ) - 100 ) < 2 );
		$this->assertContains( 31, $fields );
	}

}
