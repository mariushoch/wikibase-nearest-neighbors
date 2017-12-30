<?php

namespace Wikibase\NearestNeighbors;
use InvalidArgumentException;

/**
 * Encodes a list of numeric property ids as a (minimal) byte string.
 * This chooses the minimal possible encoder, among a list of known encodings.
 */
class PartitioningPropertyIdEncoder {

	/**
	 * @var PropertyIdEncoder[]
	 */
	private $encoders;

	/**
	 * @param PropertyIdEncoder[] $encoders Note: All given encoders must have differing field counts
	 */
	public function __construct( array $encoders ) {
		if ( count( $encoders ) === 0 ) {
			throw new InvalidArgumentException( '$encoders must not be empty' );
		}

		foreach ( $encoders as $encoder ) {
			$this->encoders[$encoder->getFieldCount()] = $encoder;
		}
		ksort( $this->encoders );
	}

	/**
	 * Get a minimal byte string that encodes that the given $numericPropertyIds are present.
	 * A bit at position n is set to 1 iff, the nth property id in $this->fields is set.
	 *
	 * @param int[] $numericPropertyIds
	 *
	 * @return string[] First value denotes the encoder used, second key is the resulting byte string
	 */
	public function getEncoded( array $numericPropertyIds ) {;
		foreach ( $this->encoders as $encoder ) {
			$encoded = $encoder->getEncoded( $numericPropertyIds );
			if ( $encoded[0] ) {
				return [
					$encoder->getName(),
					$encoded[1]
				];
			}
		}

		// This can (rarely) happen, for example if a deleted property is used
		return [
			end( $this->encoders )->getName(),
			$encoded[1]
		];
	}

}