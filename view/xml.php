<?php namespace view;

class xml extends core {
	
	protected function head() {

		header( 'Content-Type: text/xml; charset=utf-8' );
	}
}