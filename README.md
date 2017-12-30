# Wikibase Nearest Neighbors
## This will find entities which are structurally very similar to a given entity.
## We calculate the distance between two entities by comparing the statements which are present on both (by looking at their property ids).

## Usage:
* `php generateEncodingFromDump.php InputFile FileFullEncoding FileTop100Encoding`
  * Note: `InputFile` is assumed to be an already decrompressed dump. You can for example pipe a dump in like `bzip2 -cdk dump.json.bz2 | php generateEncodingFromDump.php php://stdin full top100`
* `php findNearestNeighbors.php EntityId FileFullEncoding FileTop100Encoding`
