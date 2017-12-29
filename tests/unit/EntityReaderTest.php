<?php

namespace Wikibase\NearestNeighbors\Tests;

use RuntimeException;
use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\EntityReader;

/**
 * Reads entities (as found in JSON dumps) and yields the ids of the statements on them.
 */
class EntityReaderTest extends PHPUnit_Framework_TestCase {

	public function provideReadLineString() {
		$str = '{' .
			'"ignore": "stuff",' .
			'"labels":{"en":{"language":"en","value":"wobba"}},' .
			'"descriptions":{"en":{"language":"en","value":"something something"}},' .
			'"aliases":{"ru":[{"language":"ru","value":"blah"}]},' .
			'"claims":{"P31":"aha", "P42":{"Ignore":{"this": ["please"]}}}' .
			'}';

		return [
			'Line with trailing comma' => [
				[ 31, 42 ],
				$str . ",\n"
			],
			'Line without trailing comma' => [
				[ 31, 42 ],
				$str . "\n"
			],
		];
	}

	/**
	 * @dataProvider provideReadLineString
	 */
	public function testReadLineString( $expected, $line ) {
		$entityReader = new EntityReader();

		$this->assertSame(
			[ 31, 42 ],
			$entityReader->readLineString( $line )
		);
	}

	public function testReadLineString_noClaims() {
		$entityReader = new EntityReader();

		$str = '{' .
			'"ignore": "stuff",' .
			'"labels":{"en":{"language":"en","value":"wobba"}},' .
			'"descriptions":{"en":{"language":"en","value":"something something"}},' .
			'"aliases":{"ru":[{"language":"ru","value":"blah"}]}' .
			"},\n";

		$this->assertSame(
			[],
			$entityReader->readLineString( $str )
		);
	}

	public function testReadLineString_syntaxError() {
		$entityReader = new EntityReader();

		$this->setExpectedException( RuntimeException::class );
		$entityReader->readLineString( "crap" );
	}

}