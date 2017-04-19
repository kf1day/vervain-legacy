<?php

class HttpRedirectException extends Exception {

	public $url = null;

	public function __construct( $url = null ) {
		$this->url = $url;
	}
	
}

class HttpNotFoundException extends Exception {
}