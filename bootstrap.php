<?php

if ( PHP_SAPI === 'cli' ) exit( 'This file should be called by web server'.PHP_EOL );

if ( isset( $_SERVER['DEBUG'] ) ) {
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );
}

define( 'APP_ROOT', dirname( __FILE__ ) );
define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
define( 'APP_CACHE', APP_ROOT.'/cache/'.hash( 'md4', $_SERVER['DOCUMENT_ROOT'] ) ); // md4 is the fastest

define( 'SP_MAGIC', [
	'__construct',
	'__destruct',
	'__call',
	'__callStatic',
	'__get',
	'__set',
	'__isset',
	'__unset',
	'__sleep',
	'__wakeup',
	'__toString',
	'__invoke',
	'__set_state',
	'__clone',
	'__debugInfo'
	] );

require APP_ROOT.'/core/http.php';
require APP_ROOT.'/core/pub.php';
require APP_ROOT.'/core/mvc.php';

new http();
