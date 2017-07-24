<?php namespace view;

class xslt extends core {
	
	protected $stylesheet = '';
	
	public function render( $data = null ) {

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="'.$this->templates[0].'" ?>';
		parent::render( $data );
	}
}