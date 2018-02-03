<?php namespace model\cache;
use \model\cFileSystem as fs;

class cFileSerial implements \ArrayAccess {

	protected $pt = [];
	protected $ig = false;

	public function __construct() {
		if (  extension_loaded( 'igbinary' ) ) $this->ig = true;
		fs::md( APP_ROOT . '/cache/' . APP_HASH );
		$tmp = fs::rf( APP_ROOT . '/cache/' . APP_HASH . '/__fserial__', $this->ig );
		if ( $tmp !== false ) {
			$tmp = ( $this->ig ) ? igbinary_unserialize( $tmp ) : unserialize( $tmp );
		}
		$this->pt = $tmp;
	}

	public function __destruct() {
		$tmp = ( $this->ig ) ? igbinary_serialize( $this->pt ) : serialize( $this->pt );
		fs::wf( APP_ROOT . '/cache/' . APP_HASH . '/__fserial__', $tmp, $this->ig );
	}

	// interface methods
	public function offsetExists( $offset ) {
		return isset( $this->pt[$offset] );
	}

	public function &offsetGet( $offset ) {
		return $this->pt[$offset];
	}

	public function offsetSet( $offset, $value ) {
		$this->pt[$offset] = $value;
	}

	public function offsetUnset( $offset ) {
		unset( $this->pt[$offset] );
	}

}
