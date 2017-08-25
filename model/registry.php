<?php namespace model;

class registry extends core {
	
	protected $vars = [];
	protected $cache = null;
	
	public function __construct() {
		$this->cache = new \cache( 'registry' );
		$this->vars = $this->cache->fetch( 'vars' );
		if ( ! is_array( $this->vars ) ) $this->vars = [];
	}
	
	public function __destruct() {
		$this->cache->store( 'vars', $this->vars );
	}
	
	public function &__get( $key ) {
		if ( isset( $this->vars[$key] ) ) {
			return $this->vars[$key];
		} elseif ( ! isset( $this->vars['default'] ) ) {
			$this->vars['default'] = [];
		}
		return $this->vars['default'];
	}
	
	public function __set( $key, $val ) {
		$this->vars[$key] = $val;
	}


/*	public function offsetSet( $key, $val ) {
		echo $key;
		if ( is_null( $key ) ) {
			$this->vars[] = $val;
		} else {
			$this->vars[$key] = $val;
		}
	}

	public function offsetExists( $key ) {
		echo $key;
		return isset( $this->vars[$key] );
	}

	public function offsetUnset( $key ) {
		echo $key;
		unset( $this->vars[$key] );
	}

	public function offsetGet( $key ) {
		echo $key;
		return $this->vars[$key] ?? null;
	}
	
*/
	
}