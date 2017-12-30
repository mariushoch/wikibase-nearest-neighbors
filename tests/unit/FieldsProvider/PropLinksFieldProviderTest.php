<?php

namespace Wikibase\NearestNeighbors\Tests\FieldProviders;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\FieldProviders\PropLinksFieldProvider;

/**
 * @covers Wikibase\NearestNeighbors\FieldProviders\PropLinksFieldProvider
 */
class PropLinksFieldProviderTest extends PHPUnit_Framework_TestCase {

	public function testGetFields() {
		$provider = new PropLinksFieldProvider(
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