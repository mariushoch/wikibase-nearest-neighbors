# Wikibase Nearest Neighbors
This can find entities which are structurally very similar.

We calculate the distance between two entities by comparing the statements which are present on both (by looking at their property ids).

## Usage:
* `php generateEncodingFromDump.php InputFile OutputFileFullEncoding [PropertyUsageFile] [N:OutputFileTopN]*`
  * Example: `php generateEncodingFromDump.php php://stdin /tmp/nn-full propertyUsages.txt 100:/tmp/nn-top100 300:/tmp/nn-top300`
  * Note: `InputFile` is assumed to be an already decrompressed dump. You can for example pipe a dump in like `bzip2 -cdk dump.json.bz2 | php generateEncodingFromDump.php php://stdin out`
* `php findNearestNeighbors.php EntityId FileFullEncoding FileTop100Encoding`
  * Example: `php findNearestNeighbors.php Q64 /tmp/nn-*`

A PropertyUsageFile can be obtained by using `bin/getPropertyUsages` (which again takes a decrompressed dump as input).

* Note: `InputFile` is assumed to be an already decrompressed dump. You can for example pipe a dump in like `bzip2 -cdk dump.json.bz2 | php getPropertyUsages.php php://stdin propertyUsages.txt`
