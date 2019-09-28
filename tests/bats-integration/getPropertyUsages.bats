#!/bin/env bats

@test "getPropertyUsages" {
	tmpFile="$BATS_TMPDIR/getPropertyUsages.out"

	bzcat "$BATS_TEST_DIRNAME/../wikidata-20190923-all.top1000.json.bz2" | php "$BATS_TEST_DIRNAME/../../bin/getPropertyUsages" php://stdin "$tmpFile"
	grep -q "^960	P31$" "$tmpFile"
	grep -q "^176	P21$" "$tmpFile"

	# Make sure output to stdout equals output to file
	[ "$(bzcat "$BATS_TEST_DIRNAME/../wikidata-20190923-all.top1000.json.bz2" | php "$BATS_TEST_DIRNAME/../../bin/getPropertyUsages" php://stdin | md5sum)" == "$(cat "$tmpFile" | md5sum)" ]

	rm "$tmpFile"
}
@test "getPropertyUsages: help" {
	run php "$BATS_TEST_DIRNAME/../../bin/getPropertyUsages" --help
	echo "$output" | grep -q 'Usage:'
}
