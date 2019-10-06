<?php

namespace Wikibase\NearestNeighbors\Tests\FieldsProvider;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\FieldsProvider\WikibaseAllPropertiesFieldsProvider;

/**
 * @covers Wikibase\NearestNeighbors\FieldsProvider\WikibaseAllPropertiesFieldsProvider
 */
class WikibaseAllPropertiesFieldsProviderTest extends PHPUnit_Framework_TestCase {

	public function testGetFields() {
		$provider = new WikibaseAllPropertiesFieldsProvider(
			'https://www.wikidata.org/w/api.php',
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

		// XXX: This could use the number from the API, I suppose
		$this->assertTrue( count( $fields ) > 4000 );
	}

}
