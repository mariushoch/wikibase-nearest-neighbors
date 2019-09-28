<?php

namespace Wikibase\NearestNeighbors\FieldProviders;

/**
 * Provides a list of numeric property ids suitable for encoding property
 * usages against.
 */
interface FieldProvider {

	/**
	 * @return int[] Consecutive 0-based list of numeric property ids.
	 */
	public function getFields();

}