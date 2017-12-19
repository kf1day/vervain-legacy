<?php namespace error;

class http {

	public function __construct( $code, $body = '' ) {
		if ( 407 < $code || $code <  401 ) $code = 500;
		header( 'Content-Type: text/plain' );
		http_response_code( $code );
		debug_print_backtrace();
		echo $body;
		exit;
	}
}
