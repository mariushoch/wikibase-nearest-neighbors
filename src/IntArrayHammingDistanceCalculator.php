<?php

namespace Wikibase\NearestNeighbors;

/**
 * Utility class for calculating the hamming distance between two int arrays.
 */
class IntArrayHammingDistanceCalculator {

	/**
	 * This compares the integers in both arrays pairwise (a[0] and b[0], a[1] and b[1], …).
	 * Both arrays are assumed to have the same size.
	 *
	 * @param int[] $a Needs to be one-based
	 * @param int[] $b Needs to be one-based
	 * @param int $giveUp Give up after the distance exceeds this value. In that case, the distance will be reported as $giveUp + n
	 *
	 * @return int
	 */
	public function getDistance( array $a, array $b, $giveUp = PHP_INT_MAX ) {
		$dist = 0;
		$count = count( $a );

		for ( $i = 1; $i <= $count && $dist < $giveUp; $i++ ) {
			$dist += gmp_popcount( $a[$i] ^ $b[$i] );
		}

		return $dist;
	}

}