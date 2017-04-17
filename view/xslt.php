<?php namespace view;

class xslt extends core {
	
	protected $stylesheet = '';
	
	public function __construct( $stylesheet ) {

		$this->stylesheet = $stylesheet;
	}

	protected function head() {

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="'.$this->stylesheet.'" ?>';

	}
}