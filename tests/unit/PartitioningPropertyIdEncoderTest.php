<?php

namespace Wikibase\NearestNeighbors\Tests;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\PropertyIdEncoder;
use Wikibase\NearestNeighbors\PartitioningPropertyIdEncoder;

/**
 * @covers Wikibase\NearestNeighbors\PartitioningPropertyIdEncoder
 */
class PartitioningPropertyIdEncoderTest extends PHPUnit_Framework_TestCase {

	public function provideGetEncoded() {
		$tinyEncoder = new PropertyIdEncoder(
			[ 1, 2, 3, 4 ],
			'tiny'
		);
		$mediumEncoder = new PropertyIdEncoder(
			[ 1, 2, 3, 4, 5, 6 ],
			'medium'
		);
		$largeEncoder = new PropertyIdEncoder(
			[ 1, 2, 3, 4, 5, 6, 7, 8, 9 ],
			'large'
		);
		$allEncoders = [ $tinyEncoder, $mediumEncoder, $largeEncoder ];

		return [
			'empty input' => [
				[
					'tiny',
					chr( 0 )
				],
				$allEncoders,
				[]
			],
			'small input' => [
				[
					'tiny',
					chr( 1 << 6 )
				],
				$allEncoders,
				[ 2 ]
			],
			'medium input' => [
				[
					'medium',
					chr( 1 << 3 )
				],
				$allEncoders,
				[ 5 ]
			],
			'(partly) unhandled input' => [
				[
					'large',
					chr( 1 << 6 ) . chr( 0 )
				],
				$allEncoders,
				[ 2, PHP_INT_MAX ]
			]
		];
	}

	/**
	 * @dataProvider provideGetEncoded
	 */
	public function testGetEncoded( array $expected, array $encoders, array $numericPropertyIds ) {
		$partitioningEncoder = new PartitioningPropertyIdEncoder( $encoders );

		$this->assertSame(
			$expected,
			$partitioningEncoder->getEncoded( $numericPropertyIds )
		);
	}

}