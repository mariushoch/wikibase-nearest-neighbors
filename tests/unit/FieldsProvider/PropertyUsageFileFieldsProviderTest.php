<?php

namespace Wikibase\NearestNeighbors\Tests\FieldsProvider;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\FieldsProvider\PropertyUsageFileFieldsProvider;

/**
 * @covers Wikibase\NearestNeighbors\FieldsProvider\PropertyUsageFileFieldsProvider
 */
class PropertyUsagesFileFieldsProviderTest extends PHPUnit_Framework_TestCase {

	public function testGetFields() {
		$propertyUsage = "foobar\n" .
			"IGNORE THIS\tP23\n" .
			"something something\tP42\n";

		$fileName = tempnam( sys_get_temp_dir(), 'PropertyUsageFileFieldsProviderTest' );
		file_put_contents( $fileName, $propertyUsage );

		$provider = new PropertyUsageFileFieldsProvider( $fileName );

		$this->assertSame( [ 23, 42 ], $provider->getFields() );

		unlink( $fileName );
	}

}
