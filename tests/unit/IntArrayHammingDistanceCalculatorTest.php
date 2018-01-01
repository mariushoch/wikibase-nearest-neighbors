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
				[ 1 => 0 ],
				[ 1 => 3 ]
			],
			'short input' => [
				4,
				[ 1 => 0, 2 => 3 ],
				[ 1 => 3, 0 ]
			],
			'give up' => [
				2,
				[ 1 => 0, 2 => 3 ],
				[ 1 => 3, 2 => 0 ],
				1
			],
		];
	}

	/**
	 * @dataProvider provideGetDistance
	 */
	public function testGetDistance( $expected, array $a, array $b, $giveUp = PHP_INT_MAX ) {
		$calc = new IntArrayHammingDistanceCalculator();

		$this->assertSame(
			$expected,
			$calc->getDistance( $a, $b, $giveUp )
		);
	}

}