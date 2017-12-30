<?php

namespace Wikibase\NearestNeighbors\Tests;

use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\IntArrayHammingDistanceCalculator;

/**
 * @covers Wikibase\NearestNeighbors\IntArrayHammingDistanceCalculator
 */
class IntArrayHammingDistanceCalculatorTest extends PHPUnit_Framework_TestCase {

	public function provideGetDistance() {

		return [
			'empty input' => [
				0,
				[],
				[]
			],
			'short input' => [
				2,
				[ 0 ],
				[ 3 ]
			],
			'short input' => [
				4,
				[ 0, 3 ],
				[ 3, 0 ]
			],
		];
	}

	/**
	 * @dataProvider provideGetDistance
	 */
	public function testGetDistance( $expected, array $a, array $b ) {
		$calc = new IntArrayHammingDistanceCalculator();

		$this->assertSame(
			$expected,
			$calc->getDistance( $a, $b )
		);
	}

}