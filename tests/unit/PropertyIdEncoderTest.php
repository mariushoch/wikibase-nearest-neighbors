<?php

namespace Wikibase\NearestNeighbors\Tests;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\PropertyIdEncoder;

/**
 * @covers Wikibase\NearestNeighbors\PropertyIdEncoder
 */
class PropertyIdEncoderTest extends PHPUnit_Framework_TestCase {

	public function provideGetEncoded() {
		$fields = [];
		for ( $i = 0; $i < 100; $i++ ) {
			$fields[] = mt_rand( 100, 400 );
		}
		// 1st byte
		$fields[4] = 1;
		// 3rd byte
		$fields[23] = 2;
		// 5th byte
		$fields[34] = 3;
		// 9th byte
		$fields[66] = 4;
		$fields[67] = 5;
		// 11th byte
		$fields[80] = 6;

		return [
			'trivial test case' => [
				[ true, '' ],
				[],
				[]
			],
			'One byte only #0' => [
				[ true, chr( 1 << 5 ) ],
				[ 1, 2, 3, 4 ],
				[ 3 ]
			],
			'One byte only #1' => [
				[ true, chr( 1 << 3 ) ],
				[ 1, 2, 2, 2, 3, 2, 2, 2 ],
				[ 3 ]
			],
			'Not everything encoded' => [
				[ false, chr( 1 << 3 ) ],
				[ 1, 2, 2, 2, 3, 2, 2, 2 ],
				[ 3, 92 ]
			],
			'Long field list, nothing set' => [
				[ true, "\0\0\0\0\0\0\0\0\0\0\0\0\0" ],
				$fields,
				[]
			],
			'Long field list, a few set' => [
				[
					true,
					chr( 1 << 3 ) .
					"\0" .
					chr( 1 << 0 ) .
					"\0" .
					chr( 1 << 5 ) .
					"\0\0\0" .
					chr( 3 << 4 ) .
					"\0" .
					chr( 1 << 7 ) .
					"\0\0"
				],
				$fields,
				[ 1, 2, 3, 4, 5, 6 ]
			],
		];
	}

	/**
	 * @dataProvider provideGetEncoded
	 */
	public function testGetEncoded( $expected, array $fields, array $numericPropertyIds ) {
		$encoder = new PropertyIdEncoder( $fields, '34c3' );

		$this->assertSame( $expected, $encoder->getEncoded( $numericPropertyIds ) );
	}

	public function testGetFieldCount() {
		$encoder = new PropertyIdEncoder( [1, 1, 1, 1], '34c3' );

		$this->assertSame( 4, $encoder->getFieldCount() );
	}

	public function testGetName() {
		$encoder = new PropertyIdEncoder( [], '34c3' );

		$this->assertSame( '34c3', $encoder->getName() );
	}
	
}