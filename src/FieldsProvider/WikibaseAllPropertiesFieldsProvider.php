<?php

namespace Wikibase\NearestNeighbors\FieldsProvider;
use RuntimeException;

/**
 * Get a field mapping containing *all properties* that exist on a MediaWiki instance.
 */
class WikibaseAllPropertiesFieldsProvider implements FieldsProvider {

	/**
	 * @var string
	 */
	private $apiUrl;

	/**
	 * @var int
	 */
	private $namespaceNumber;

	/**
	 * @var int[]|null
	 */
	private $fields = null;

	/**
	 * @param string $apiUrl
	 * @param int $namespaceNumber
	 */
	public function __construct( $apiUrl, $namespaceNumber ) {
		$this->apiUrl = $apiUrl;
		$this->namespaceNumber = $namespaceNumber;
	}

	/**
	 * @return int[]
	 */
	public function getFields() {
		if ( !$this->fields ) {
			$this->retrieveAllPropertyIds();
		}

		return $this->fields;
	}

	private function retrieveAllPropertyIds() {
		$continue = false;
		$propertyIds = [];
		do {
			$url = $this->apiUrl . '?action=query&list=allpages&aplimit=max&formatversion=2&format=json&apnamespace=' . $this->namespaceNumber;
			if ( $continue ) {
				$url .= '&apcontinue=' . $continue;
			}
			// TODO: Error handling
			$json = file_get_contents( $url );
			$data = json_decode( $json, true );

			if ( !is_array( $data ) ) {
				throw new RuntimeException( "Couldn't decode api response: " . json_last_error_msg() );
			}

			if ( !isset( $data['query'] ) ) {
				throw new RuntimeException( 'Unexpected API response' );
			}

			if ( isset( $data['continue'] ) ) {
				$continue = $data['continue']['apcontinue'];
			} else {
				$continue = false;
			}

			$allPages = $data['query']['allpages'];
			foreach ( $allPages as $page ) {
				$propertyIds[] = preg_replace( '@.*:@', '', $page['title'] );
			}
		} while ( $continue );

		$this->fields = array_map( function( $propertyIdSerialization ) {
			return (int) substr( $propertyIdSerialization, 1 );
		}, $propertyIds );
	}

}
