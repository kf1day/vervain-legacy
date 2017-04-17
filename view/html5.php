<?php namespace view;

class html5 extends core {

	protected function head() {

		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!DOCTYPE html>';
	}
}