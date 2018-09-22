<?php namespace model\cache;
use \model\cFileSystem as fs;

class cFileSerial implements iCacher {

	protected $pt = [];
	protected $ig = false;
	protected $changed = false;

	const PATH = APP_ROOT . '/cache/' . APP_HASH;

	public function __construct() {
		if (  extension_loaded( 'igbinary' ) ) $this->ig = true;
		fs::md( static::PATH );
		$tmp = fs::rf( static::PATH . '/__fserial__', $this->ig );
		if ( $tmp !== false ) {
			$tmp = ( $this->ig ) ? igbinary_unserialize( $tmp ) : unserialize( $tmp );
		}
		$this->pt = $tmp;
	}

	public function __destruct() {
		if ( $this->changed )  {
			$tmp = ( $this->ig ) ? igbinary_serialize( $this->pt ) : serialize( $this->pt );
			fs::wf( static::PATH . '/__fserial__', $tmp, $this->ig );
		}
	}

	// interface methods
	public function get( string $key, callable $callback, array $args = [], $version = null ) {
		if ( isset( $this->pt[$key] ) ) {
			return $this->pt[$key];
		} else {
			$fff = call_user_func_array( $callback, $args );
			$this->set( $key, $fff );
			return $fff;
		}

	}

	public function set( string $key, $value, $version = null ) {
		$this->changed = true;
		$this->pt[$key] = $value;
	}
}
