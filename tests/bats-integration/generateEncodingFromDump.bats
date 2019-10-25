#!/bin/env bats

propertyUsages="$BATS_TMPDIR/getPropertyUsages.out"
top4File="$BATS_TEST_DIRNAME/../wikidata-20190923-all.top4.json.bz2"

function setup {
	rm -f "$propertyUsages"
	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/getPropertyUsages" php://stdin "$propertyUsages"
}

function teardown {
	rm -f "$propertyUsages"
}

@test "generateEncodingFromDump" {
	outFile="$(mktemp)"

	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$outFile"
	grep -qP '^Q8:' "$outFile"
	grep -qP '^Q23:' "$outFile"
	grep -qP '^Q24:' "$outFile"
	grep -qP '^Q31:' "$outFile"

	rm -f "$outFile"
}
@test "generateEncodingFromDump: small top-n-file unused" {
	outFile1="$(mktemp)"
	outFile2="$(mktemp)"
	outFile3="$(mktemp)"

	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$outFile1"
	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$outFile2" "$propertyUsages" "5:$outFile3"
	# Should contain just the header.
	[ "$(du "$outFile3" | awk '{print $1}')" -lt 50 ]
	[ "$(wc -l "$outFile3" | awk '{print $1}')" -eq 1 ]
	# Make sure the full output file is not affected
	[ "$(md5sum "$outFile1" | awk '{print $1}')" == "$(md5sum "$outFile2" | awk '{print $1}')" ]

	rm -f "$outFile1" "$outFile2" "$outFile3"
}
@test "generateEncodingFromDump: large top-n-file is used" {
	outFile1="$(mktemp)"
	outFile2="$(mktemp)"

	bzcat "$top4File" | php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" php://stdin "$outFile1" "$propertyUsages" "250:$outFile2"
	# Both the full and the top-n-file should be used
	[ "$(wc -l "$outFile1" | awk '{print $1}')" -gt 1 ]
	[ "$(wc -l "$outFile2" | awk '{print $1}')" -gt 1 ]
	grep -qP '^Q8:' "$outFile1" "$outFile2"
	grep -qP '^Q23:' "$outFile1" "$outFile2"
	grep -qP '^Q24:' "$outFile1" "$outFile2"
	grep -qP '^Q31:' "$outFile1" "$outFile2"

	rm -f "$outFile1" "$outFile2"
}
@test "generateEncodingFromDump: help" {
	run php "$BATS_TEST_DIRNAME/../../bin/generateEncodingFromDump" --help
	echo "$output" | grep -q 'Usage:'
}
