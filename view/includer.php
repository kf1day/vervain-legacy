<?php namespace view;

class includer extends core {
	
	protected $type = ''
	
	public function __construct( $tpl_path, $type = '' ) {
		parent::__construct( $tpl_path );
		$this->type = ( in_array( $type, [ 'html', 'xslt', 'xml' ] ) ) ? $type : 'html';

	public function display( $vars = null ) {
		switch( $this->type ) {
			case 'html':
				header( 'Content-Type: text/html; charset=utf-8' );
				echo '<!DOCTYPE html>';
				break;

			case 'xslt':
				header( 'Content-Type: application/xml; charset=utf-8' );
				echo '<?xml version="1.0" encoding="utf-8"?>';
/*				echo '<?xml-stylesheet type="text/xsl" href="'.$this->templates[0].'" ?>';	*/
				break;
				
			case 'xml':
				header( 'Content-Type: text/xml; charset=utf-8' );
				echo '<?xml version="1.0" encoding="utf-8"?>';
				break;
		}
		while( $tpl = $this->fetch() ) {
			include $tpl;
		}
		
	}
}