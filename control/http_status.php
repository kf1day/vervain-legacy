<?php namespace control;

class http_status extends core {
	
	function run( $code = null ) {
		if ( 407 < $code || $code <  401 ) $code = 500;
		http_response_code( $code );
		exit;
	}
}
