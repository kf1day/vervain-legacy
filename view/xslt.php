<?php namespace view;

class xslt extends core {
	
	private $stylesheet = '';
	
	public function __construct( $stylesheet ) {

		$this->stylesheet = $stylesheet;
	}

	private function head() {

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="'.$this->stylesheet.'" ?>';

	}
}