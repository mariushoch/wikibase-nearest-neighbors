<?php

namespace Wikibase\NearestNeighbors\Tests\FieldProviders;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\FieldProviders\PropertyUsageFileFieldProvider;

/**
 * @covers Wikibase\NearestNeighbors\FieldProviders\PropertyUsageFileFieldProvider
 */
class PropertyUsageFileFieldProviderTest extends PHPUnit_Framework_TestCase {

	public function testGetFields() {
		$propertyUsage = "foobar\n" .
			"IGNORE THIS\tP23\n" .
			"something something\tP42\n";

		$fileName = tempnam( sys_get_temp_dir(), 'PropertyUsageFileFieldProviderTest' );
		file_put_contents( $fileName, $propertyUsage );

		$provider = new PropertyUsageFileFieldProvider( $fileName );

		$this->assertSame( [ 23, 42 ], $provider->getFields() );

		unlink( $fileName );
	}

}