<?php namespace error;

class http {
	
	public function __construct( $code ) {
		if ( 407 < $code || $code <  401 ) $code = 500;
		echo $code;
		http_response_code( $code );
		exit;
	}
}
