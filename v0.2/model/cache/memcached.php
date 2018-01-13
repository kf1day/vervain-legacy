<?php namespace model\cache;

class memcached implements \ArrayAccess {

	protected $pt = [];
	protected $sv = null;

	public function __construct( $host, $port ) {

		if ( ! extension_loaded( 'memcached' ) ) throw new \Exception( 'Memcached module not loaded' );

		$this->sv = new \Memcached( APP_HASH );
		if ( empty( $this->sv->getServerList() ) ) {
			$this->sv->addServer( $host, $port );
		}
	}

	public function __destruct() {
		foreach( $this->pt as $k => $v ) {
			$this->sv->set( $k, $v );
		}
	}

	// interface methods
	public function offsetExists( $offset ) {
		return isset( $this->pt[$offset] ) || $this->sv->touch( $offset );
	}

	public function &offsetGet( $offset ) {
		if ( empty( $this->pt[$offset] ) ) {
			$this->pt[$offset] = $this->sv->get( $offset );
		}
		return $this->pt[$offset];
	}

	public function offsetSet( $offset, $value ) {
		unset( $this->pt[$offset] );
		$this->sv->set( $offset, $value );
	}

	public function offsetUnset( $offset ) {
		unset( $this->pt[$offset] );
		$this->sv->delete( $offset );
	}

	}