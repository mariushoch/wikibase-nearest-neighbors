<?php

namespace Wikibase\NearestNeighbors;
use Wikibase\NearestNeighbors\NearestNeighborFinder;
?>

<html>
	<head>
		<title>Wikibase nearest neighbors (experimental)</title>
		<style type="text/css">
			a {
				text-decoration: none;
			}
		</style>
	</head>
	<body>
		<h1>Wikibase nearest neighbors (experimental)</h1>
		<form action="">
			<label for="entityid">EntityId</label>: <input name="entityid" type="text" /><br />
			<input type="submit" id="submit" value="Get neighbors" /><br />
			<small><b>Note:</b> This query can take up to a minute!</small>
		</form>
<?php

namespace Wikibase\NearestNeighbors;
use Wikibase\NearestNeighbors\NearestNeighborFinder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/webconfig.php';

$entityId = null;
if ( isset( $_GET['entityid'] ) ) {
	$entityId = trim( $_GET['entityid'] );
	if ( !preg_match( '@^\w\d+\z@', $entityId ) ) {
		$entityId = null;
	}
}

if ( $entityId ) {
	$minDistance = -1;

	$finder = new NearestNeighborFinder( $encodedFiles, 'https://www.wikidata.org/wiki/Special:EntityData/$1.json' );
	$displayResults = $finder->getNearestNeighbor( $entityId, $minDistance );

	echo "<table>";
	echo "<tr><th style='border: 1px solid gray;'>Id</th><th style='border: 1px solid gray;'>Distance</th></tr>\n";
	foreach( $displayResults as $res ) {
		echo "<tr><td style='border: 1px solid gray;'>$res[0]</td><td style='border: 1px solid gray;'>$res[1]</td></tr>\n";
	}
	echo "</table>";

}
?>

<div>
	<a href="https://github.com/mariushoch/wikibase-nearest-neighbors">Source code</a>.
</div>
	</body>
</html>