<?php

namespace Wikibase\NearestNeighbors\FieldProviders;

/**
 * Get a property field mapping containing all properties linked from a given page.
 * This uses the MediaWiki API.
 */
class PropLinksFieldProvider implements FieldProvider {

	/**
	 * @var string
	 */
	private $apiUrl;

	/**
	 * @var string
	 */
	private $pageTitle;

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
	 * @param string $pageTitle
	 * @param int $namespaceNumber
	 */
	public function __construct( $apiUrl, $pageTitle, $namespaceNumber ) {
		$this->apiUrl = $apiUrl;
		$this->pageTitle = $pageTitle;
		$this->namespaceNumber = $namespaceNumber;
	}

	/**
	 * @return int[]
	 */
	public function getFields() {
		if ( !$this->fields ) {
			$this->retrievePropertyIds();
		}

		return $this->fields;
	}

	private function retrievePropertyIds() {
		$continue = false;
		$propertyIds = [];
		do {
			$url = $this->apiUrl . '?action=query&prop=links&formatversion=2&format=json&pllimit=max&plnamespace=' . $this->namespaceNumber .
				'&titles=' . urlencode( $this->pageTitle );

			if ( $continue ) {
				$url .= '&plcontinue=' . $continue;
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
				$continue = $data['continue']['plcontinue'];
			} else {
				$continue = false;
			}

			$links = $data['query']['pages'][0]['links'];
			foreach ( $links as $page ) {
				$propertyIds[] = preg_replace( '@.*:@', '', $page['title'] );
			}
		} while ( $continue );

		$this->fields = array_map( function( $propertyIdSerialization ) {
			return (int) substr( $propertyIdSerialization, 1 );
		}, $propertyIds );
	}

}