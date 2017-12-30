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
	 * @param int[] $a
	 * @param int[] $b
	 *
	 * @return int
	 */
	public function getDistance( array $a, array $b ) {
		$dist = 0;
		$count = count( $a );
		for ( $i = 0; $i < $count; $i++ ) {
			$val = $a[$i] ^ $b[$i];

			for (; $val; $dist++ ) {
				$val &= $val < 0 ? PHP_INT_MAX : $val - 1;
			}
		}

		return $dist;
	}

}