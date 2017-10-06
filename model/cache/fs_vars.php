<?php namespace model\cache;

class fs_vars extends fs {
	
	protected $vars = [];
	
	public function __construct() {
		parent::__construct();
		$tmp = parent::get( '__fs_vars_serialized' );
		if ( $tmp !== false && $tmp = @unserialize( $tmp ) ) {
			$this->vars = $tmp;
		}
	}
	
	public function __destruct() {
		parent::set( '__fs_vars_serialized', serialize( $this->vars ) );
	}
	
	public function set( $key, $val ) {
		$this->vars[$key] = $val;
	}
	
	public function get( $key ) {
		if ( isset( $this->vars[$key] ) ) {
			return $this->vars[$key];
		} else {
			return false;
		}
	}

	public function stack( $key, $val, $index = null ) {
		if ( $index ) {
			$this->vars[$key][$index] = $val;
		} else {
			$this->vars[$key][] = $val;
		}
	}

	public function fetch( $key, &$index = null ) {
		if ( is_array( $this->vars[$key] ) ) {
			$ret = current( $this->vars[$key] );
			if ( $ret !== false ) next( $this->vars[$key] );
			return $ret;
		} else {
			return false;
		}
	}
	
}