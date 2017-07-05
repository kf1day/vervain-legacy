<?php namespace view;

class xml extends core {
	
	public function render( $template, $data = null ) {

		header( 'Content-Type: text/xml; charset=utf-8' );
		parent::render( $template, $data );
	}
}