<?php

if ( PHP_SAPI === 'cli' ) exit( 'CLI instance not implemented yet' . PHP_EOL );

if ( isset( $_SERVER['VERVAIN_DEBUG']  ) ) {
	define( 'OPT_DEBUG', 1 );
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );
} else {
	define( 'OPT_DEBUG', 0 );
}

define( 'APP_ROOT', dirname( __FILE__ ) );
define( 'APP_SITE', rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) );
define( 'APP_HASH', hash( 'md4', $_SERVER['DOCUMENT_ROOT'] ) ); // md4 is the fastest

require APP_ROOT.'/core/app.php';
require APP_ROOT.'/core/map.php';
require APP_ROOT.'/core/http.php';

new instance();
