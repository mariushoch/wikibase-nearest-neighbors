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
				'',
				[],
				[]
			],
			'One byte only #0' => [
				chr( 1 << 5 ),
				[ 1, 2, 3, 4 ],
				[ 3 ]
			],
			'One byte only #1' => [
				chr( 1 << 3 ),
				[ 1, 2, 2, 2, 3, 2, 2, 2 ],
				[ 3 ]
			],
			'Long field list, nothing set' => [
				"\0\0\0\0\0\0\0\0\0\0\0\0\0",
				$fields,
				[]
			],
			'Long field list, a few set' => [
					chr( 1 << 3 ) .
					"\0" .
					chr( 1 << 0 ) .
					"\0" .
					chr( 1 << 5 ) .
					"\0\0\0" .
					chr( 3 << 4 ) .
					"\0" .
					chr( 1 << 7 ) .
					"\0\0",
				$fields,
				[ 1, 2, 3, 4, 5, 6 ]
			],
		];
	}

	/**
	 * @dataProvider provideGetEncoded
	 */
	public function testGetEncoded( $expected, array $fields, array $numericPropertyIds ) {
		$encoder = new PropertyIdEncoder( $fields );

		$this->assertSame( $expected, $encoder->getEncoded( $numericPropertyIds ) );
	}

}