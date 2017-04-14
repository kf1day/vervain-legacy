<?php

if ( PHP_SAPI === 'cli' ) exit( 'This file should be called by web server'.PHP_EOL );

define( 'APP_ROOT', dirname( __FILE__ ) );

require APP_ROOT.'/core/ctl.php';
require APP_ROOT.'/core/model.php';
require APP_ROOT.'/core/view.php';
require APP_ROOT.'/core/http.php';
require APP_ROOT.'/core/map.php';

http::request();
