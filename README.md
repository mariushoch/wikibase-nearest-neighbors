# Wikibase Nearest Neighbors
## This will find items which are very similar to a given item.

## Usage:
* `php generateEncodingFromDump.php InputFile FileFullEncoding FileTop100Encoding`
  * Note: `InputFile` is assumed to be a already decrompressed dump. You can for example pipe a dump in from `bzip2` and use `bzip2 -cdk dump.json.bz2 | php generateEncodingFromDump.php php://stdin full top100`
* `php findNearestNeighbors.php EntityId FileFullEncoding FileTop100Encoding`