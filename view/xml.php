<?php namespace view;

class xml extends core {
	
	private function head() {

		header( 'Content-Type: text/xml; charset=utf-8' );
	}
}