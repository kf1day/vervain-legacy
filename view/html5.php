<?php namespace view;

class html5 extends core {

	public function render( $data = null ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!DOCTYPE html>';
		parent::render( $data );
	}
}