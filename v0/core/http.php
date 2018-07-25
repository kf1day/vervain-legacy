<?php

final class instance {

	public function __construct() {

		spl_autoload_register( [ $this, 'loader' ] );
		$path = $_SERVER['DOCUMENT_URI'];
		$cls = null;
		try {
			$map = new map();
			list( $action, $method, $args ) = $map->routing( $path );
			$cls = new ReflectionClass( '\\action\\' . $action );
			if ( ! $cls->isSubclassOf( '\\app\\cAction' ) ) {
				throw new Exception( sprintf( 'Class "\\action\\%s" must be instance of "\\app\\cAction"', $action ) );
			} elseif( $cls->hasMethod( $method ) ) {
				if ( OPT_DEBUG ) header( sprintf( 'X-Trace-Instance: \\%s::%s(%s)', $cls->getName(), $method, implode( ', ', $args ) ) );
				$cls->getMethod( $method )->invokeArgs( $cls->newInstance( $map, $path ), $args );
			} else {
				throw new EHttpClient( 404, sprintf( 'Method "\\%s::%s" not found!', $cls->getName(), $method ) );
			}
		} catch( EHttpRedirect $e ) {
			$e->follow();
		} catch( EHttpClient $e ) {
			if ( $cls === null ) {
				$cls = new ReflectionClass( '\\action\\__default' );
				if ( ! $cls->isSubclassOf( '\\app\\cAction' ) ) {
					http_response_code( 500 );
					echo 'Class "\\action\\__default" must be instance of "\\app\\cAction"';
					return;
				}
			}
			$args = [ $e->getCode(), $e->getMessage() ];
			if ( OPT_DEBUG ) header( sprintf( 'X-Trace-Instance: \\%s::__onerror(%s)', $cls->getName(), implode( ', ', $args ) ) );
			$cls->getMethod( '__onerror' )->invokeArgs( $cls->newInstance( $map, $path ), $args );
		} catch( Exception $e ) {
			http_response_code( 500 );
			echo $e->getMessage();
		}
	}

	private function loader( $classname ) {
		$classname = str_replace( '\\', '/', $classname );
		if ( is_file( APP_SITE . '/' . $classname . '.php' ) ) {
			include APP_SITE . '/' . $classname . '.php';
		} elseif ( is_file( APP_ROOT . '/' . $classname . '.php' ) ) {
			include APP_ROOT . '/' . $classname . '.php';
		}
	}
}

class EHttpRedirect extends Exception {
	private $url = null;

	public function __construct( $url = null ) {
		parent::__construct();
		$this->url = $url;
	}

	public function follow() {
		$scheme = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) $scheme = 'https';
		if ( isset( $_SERVER['REQUEST_SCHEME'] ) ) $scheme = $_SERVER['REQUEST_SCHEME'];
		$host = $_SERVER['SERVER_NAME'];
		if ( ! in_array( $scheme . $_SERVER['SERVER_PORT'], [ 'http80', 'https443' ] ) ) $host .= ':' . $_SERVER['SERVER_PORT'];
		header( 'Location: ' . $scheme . '://' . $host . $this->url, true, 302 ); // absolute path required due to RFC
	}
}

class EHttpClient extends Exception {

	const HTTP_STATUS = [
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		409 => 'Conflict',
	];

	public function __construct( $code, $body = null ) {
		if ( ! isset( self::HTTP_STATUS[$code] ) ) $code = 400;
		if ( $body === null ) $body = self::HTTP_STATUS[$code];
		parent::__construct( $body, $code );
	}

}
