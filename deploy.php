<?php

# Put your deploy config file in the same dir as this file
if ( file_exists( dirname( __FILE__ ) . '/deploy-config.php' ) )
	include_once( 'deploy-config.php' );

# Array of the authorized IP addresses who can POST here
if ( !defined( 'AUTHORIZED_IPS' ) )
	define( 'AUTHORIZED_IPS', array( '207.97.227.253', '50.57.128.197', '108.171.174.178' ) );

# A regex matching the ref of the "push". `git pull` will only run if this matches. Default is the master branch.
if ( !defined( 'REF_REGEX' ) )
	define( 'REF_REGEX', '#^refs/heads/master$#' );

# Log location; make sure it exists
if ( !defined( 'LOG' ) )
	define( 'LOG', '../logs/deploy.log' );

# Where is your repo directory? This script will chdir to it. If %s is present, it gets replaced with the repository name
if ( !defined( 'REPO_DIR' ) )
	define( 'REPO_DIR', dirname( __FILE__ ) . "/wp-content/themes/%s/" );

# If set to true, $_POST gets logged
if ( !defined( 'DUMP_POSTDATA' ) )
	define( 'DUMP_POSTDATA', false );

# In your webhook URL to github, you can append ?auth={{ this field }} as a very simple gut-check authentication
if ( !defined( 'AUTH_KEY' ) )
	define( 'AUTH_KEY', '5013c76d5cbb92e4097a70' );


if ( is_writable( LOG ) && $handle = fopen( LOG, 'a') ) {
	# Sweet taste of victory
} else {
	@fclose( $handle );
	die( 'Hmmm, something went wrong with the logging!' );
}

if (
	!isset( $_GET['auth'] )
	|| AUTH_KEY != $_GET['auth']
	|| 'post' != strtolower( $_SERVER['REQUEST_METHOD'] )
	|| !isset( $_POST['payload'] )
	|| !in_array( $_SERVER['REMOTE_ADDR'], AUTHORIZED_IPS )
) {
	fwrite( $handle, "*** ALERT ***\nFailed attempt to access deployment script!\n" . print_r( $_SERVER, 1 ) . print_r( $_REQUEST, 1 ) );
	@fclose( $handle );
	die( "You don't have permission to access this page." );
}

$content = date( 'Y-m-d H:i:s' ) . "\n==============================\n";
if ( DUMP_POSTDATA )
	$content .= print_r( $_POST, 1 ) . "\n\n";

if ( false === fwrite( $handle, $content ) ) {
	echo "Couldn't write to log!\n";
}

$payload = json_decode( $_POST['payload'] );
if ( preg_match( REF_REGEX, $payload->ref ) ) {
	# If we have a commit to master, we can pull on it
	$command = "git pull";
	$output = array( "bash> $command" );
	chdir( sprintf( REPO_DIR, $payload->repository->name ) );
	exec( "$command 2>&1", $output );
	fwrite( $handle, "`$payload->ref` matches, executing:\n$command\n" . implode( "\n", $output ) . "\n" );
} else {
	echo "`$payload->ref` doesn't match the ref criteria\n";
}

fwrite( $handle, "Over and out!\n\n\n" );
@fclose( $handle );

?>
