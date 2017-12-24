<?php

if ( PHP_SAPI === 'cli' ) exit( 'This file should be called by web server'.PHP_EOL );

if ( isset( $_SERVER['DEBUG'] ) ) {
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );
}

define( 'APP_ROOT', dirname( __FILE__ ) );
define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
define( 'APP_HASH', hash( 'md4', $_SERVER['DOCUMENT_ROOT'] ) ); // md4 is the fastest

require APP_ROOT.'/core/app.php';
require APP_ROOT.'/core/http.php';

new http();
