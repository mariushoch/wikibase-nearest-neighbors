#!/bin/env bats

encodingFile="$BATS_TMPDIR/findNearestNeighbors-test"
top4File="$BATS_TEST_DIRNAME/../wikidata-20190923-all.top4.json.bz2"

function teardown {
	rm -f "$encodingFile"
}

@test "findNearestNeighbors: help" {
	run php "$BATS_TEST_DIRNAME/../../bin/findNearestNeighbors" --help
	echo "$output" | grep -q 'Usage:'
}
@test "findNearestNeighbors: Q31" {
	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$encodingFile"

	run php "$BATS_TEST_DIRNAME/../../bin/findNearestNeighbors" Q31 "$encodingFile"
	echo "$output" | head -n1 | grep -Pq '^Id\W+Distance$'
	# Q31 should be the first result
	echo "$output" | head -n2 | tail -n1 | grep -Pq '^Q31\W+\d+$'
	# Output should have 5 lines (header + one line for each entity in the encoded file)
	[ "$(echo "$output" | wc -l)" -eq 5 ]
}
@test "findNearestNeighbors: Two encoding files" {
	bzcat "$top4File" | grep -vP '^{"type":"item","id":"Q23"' | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$encodingFile"
	bzcat "$top4File" | grep -P '^([^{]|{"type":"item","id":"Q23")' | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$encodingFile"-1

	run php "$BATS_TEST_DIRNAME/../../bin/findNearestNeighbors" Q31 "$encodingFile" "$encodingFile"-1
	echo "$output" | head -n1 | grep -Pq '^Id\W+Distance$'
	# Q31 should be the first result
	echo "$output" | head -n2 | tail -n1 | grep -Pq '^Q31\W+\d+$'
	# Output should have 5 lines (header + one line for each entity in the encoded files)
	[ "$(echo "$output" | wc -l)" -eq 5 ]

	rm -rf "$encodingFile"-1
}
@test "findNearestNeighbors: --min-distance" {
	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$encodingFile"

	run php "$BATS_TEST_DIRNAME/../../bin/findNearestNeighbors" Q8 "$encodingFile" --min-distance 25
	echo "$output" | head -n1 | grep -Pq '^Id\W+Distance$'
	# Q8 should not be in the results
	! echo "$output" | grep -Pq '^Q8\W+\d+$'
	# Q23 should still be in the results
	echo "$output" | grep -Pq '^Q23\W+\d+$'
}
