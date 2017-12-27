<?php namespace model\cache;

class mmap {

	protected $pt = null;

	public function __construct( $host, $port ) {
		$this->pt = new \Memcached( APP_HASH );
		if ( count( $this->pt->getServerList() ) === 0 ) {
			$this->pt->addServer( $host, $port );
/*		} else {
			echo 'memcached connection reused';*/
		}
	}

	public function set( $key, $val ) {
		$this->pt->set( $key, $val );
	}

	public function get( $key ) {
		return $this->pt->get( $key );
	}

	public function stack( $key, $val, $index = null ) {
		$tmp = $this->pt->get( $key );
		if ( $tmp === false || is_array( $tmp ) ) {
			if ( $index ) {
				$tmp[$index] = $val;
			} else {
				$tmp[] = $val;
			}
			$this->pt->set( $key, $tmp );
			return true;
		} else {
			return false;
		}
	}

/*	public function fetch( $key, &$index = null ) {
		if ( is_array( $this->vars[$key] ) ) {
			$ret = current( $this->vars[$key] );
			if ( $ret !== false ) next( $this->vars[$key] );
			return $ret;
		} else {
			return false;
		}*/
	}