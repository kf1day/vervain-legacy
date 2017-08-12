<?php namespace view;

require '/var/www/fenom/src/Fenom.php';

class fenom extends core {
	
	protected $rdr = false;
	
	public function __construct( $tpl_path, $options ) {
		parent::__construct( $tpl_path );
		if ( ! is_dir( APP_CACHE.'/fenom' ) ) mkdir( APP_CACHE.'/fenom', 0700, true );
		\Fenom::registerAutoload();
		$this->rdr = \Fenom::factory( $this->tpl_path, APP_CACHE.'/fenom', $options );
	}
	
	public function display( $vars = null ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!DOCTYPE html>';
		foreach ( $this->tpl_list as $tpl ) {
			$this->rdr->display( $tpl, $vars );
		}
	}
}