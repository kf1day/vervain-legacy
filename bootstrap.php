<?php

if ( PHP_SAPI === 'cli' ) exit( 'This file should be called by web server'.PHP_EOL );

if ( isset( $_SERVER['DEBUG'] ) ) {
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );
}

define( 'APP_ROOT', dirname( __FILE__ ) );

require APP_ROOT.'/core/http.php';
require APP_ROOT.'/core/map.php';
require APP_ROOT.'/core/mvc.php';

new http();
