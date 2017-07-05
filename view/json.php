<?php namespace view;

class json extends core {

	public function render( $template, $data = null ) {
		header( 'Content-Type: text/json; charset=utf-8' );
		echo json_encode( $data, JSON_UNESCAPED_UNICODE );
	}
}