<?php

/**
 * This script runs a series of tests of pfpro_functions. This script will
 * produce a warning and exit if an assertion fails. Modify the PARTNER, i
 * VENDOR, USER, and PWD parameters to match your environment.
 *
 * @package pfpro_functions
 */

$params["PARTNER"] = NULL;
$params["VENDOR"] = NULL;
$params["USER"] = NULL;
$params["PWD"] = NULL;


require_once 'pfpro_functions.php';

assert_options( ASSERT_ACTIVE, TRUE );
assert_options( ASSERT_WARNING, TRUE );
assert_options( ASSERT_BAIL, TRUE );

// Make sure the PARTNER, VENDOR, USER, and PWD parameters are set.
assert( 'isset( $params["PARTNER"] ) and isset( $params["VENDOR"] ) and isset( $params["USER"] ) and isset( $params["PWD"] )' );

// Try the empty pfpro_init() function.
assert( 'pfpro_init() === NULL' );

// Try the empty pfpro_cleanup() function.
assert( 'pfpro_cleanup() === NULL' );

// Check the output of the pfpro_version() function.
assert( 'pfpro_version() === "HTTPS Interface"' );

$resp = pfpro_process( array());
// Check for authentication failure.
assert( '$resp["RESULT"] !== "1"' );
// Check for an "Invalid vendor account" message when submitting an empty transaction.
assert( '$resp["RESULT"] === "26" and $resp["RESPMSG"] === "Invalid vendor account"' );

// Check for an "Invalid tender" message when submitting an incomplete transaction.
$resp = pfpro_process( $params );
assert( '$resp["RESULT"] === "2" and $resp["RESPMSG"] === "Invalid tender"' );

$params["TRXTYPE"] = "R";
$params["TENDER"] = "C";
$params["ACTION"] = "A";
$params["AMT"] = 0.01;
$params["ACCT"] = 5555555555554444;
$params["EXPDATE"] = date( "my", strtotime( "+6 months" ));
$params["PROFILENAME"] = "testing";
$params["PAYPERIOD"] = "MONT";
$params["START"] = date( "mdY", strtotime( "+2 months" ));

// Check for an "Approved" message when submitting a complete transaction.
$resp = pfpro_process( $params );
assert( '$resp["RESULT"] === "0" and $resp["RESPMSG"] === "Approved"' );

echo "All Tests Passed\n";
exit( 0 );
