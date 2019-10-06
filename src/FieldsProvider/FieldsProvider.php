<?php

namespace Wikibase\NearestNeighbors\FieldsProvider;

/**
 * Provides a list of numeric property ids suitable for encoding property
 * usages against.
 */
interface FieldsProvider {

	/**
	 * @return int[] Ordered list of numeric property ids.
	 */
	public function getFields();

}
