<?php

namespace Wikibase\NearestNeighbors\Tests;

use RuntimeException;
use PHPUnit_Framework_TestCase;
use Wikibase\NearestNeighbors\EntityReader;

/**
 * @covers Wikibase\NearestNeighbors\EntityReader
 */
class EntityReaderTest extends PHPUnit_Framework_TestCase {

	public function provideReadLineString() {
		$str = '{' .
			'"id":"Q2013",' .
			'"ignore": "stuff",' .
			'"labels":{"en":{"language":"en","value":"wobba"}},' .
			'"descriptions":{"en":{"language":"en","value":"something something"}},' .
			'"aliases":{"ru":[{"language":"ru","value":"blah"}]},' .
			'"claims":{"P31":"aha", "P42":{"Ignore":{"this": ["please"]}}}' .
			'}';

		return [
			'Line with trailing comma' => [
				$str . ",\n"
			],
			'Line without trailing comma' => [
				$str . "\n"
			],
		];
	}

	/**
	 * @dataProvider provideReadLineString
	 */
	public function testReadLineString( $line ) {
		$entityReader = new EntityReader();

		$this->assertSame(
			[ 'Q2013', [ 31, 42 ] ],
			$entityReader->readLineString( $line )
		);
	}

	public function testReadLineString_noClaims() {
		$entityReader = new EntityReader();

		$str = '{' .
			'"id":"crazy",' .
			'"ignore": "stuff",' .
			'"labels":{"en":{"language":"en","value":"wobba"}},' .
			'"descriptions":{"en":{"language":"en","value":"something something"}},' .
			'"aliases":{"ru":[{"language":"ru","value":"blah"}]}' .
			"},\n";

		$this->assertSame(
			[ 'crazy', [] ],
			$entityReader->readLineString( $str )
		);
	}

	public function testReadLineString_syntaxError() {
		$entityReader = new EntityReader();

		$this->setExpectedException( RuntimeException::class );
		$entityReader->readLineString( "crap" );
	}

}