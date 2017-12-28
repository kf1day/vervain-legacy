<?php

final class http {

	public function __construct() {

		spl_autoload_register( [ $this, 'loader' ] );
		$path = '';
		try {
			list( $action, $index, $args ) = $this->routing( $path );
			$cls = new ReflectionClass( '\\action\\' . $action );
			if ( ! $cls->isSubclassOf( '\\app\\action' ) ) {
				throw new Exception( 'Class in not an ACTION' );
			} elseif( $cls->hasMethod( $index ) ) {
				header( sprintf( 'X-Action: \\action\\%s::%s(%s)', $action, $index, implode( ', ', $args ) ) );;
				$cls->getMethod( $index )->invokeArgs( $cls->newInstance( $path ), $args );
			} elseif( $cls->hasMethod( '__call' ) ) {
				array_unshift ( $args, $index );
				header( sprintf( 'X-Action: \\action\\%s::__call(%s)', $action, implode( ', ', $args ) ) );;
				$cls->getMethod( '__call' )->invokeArgs( $cls->newInstance( $path ), $args );
			} else {
				throw new EHttpClient( 404, null, 'Method "\\action\\' . $action . '->' . $index . '" not found!');
			}
		} catch( EHttpRedirect $e ) {
			$e->set_root( $this->path );
			$e->process();
		} catch( EHttpClient $e ) {
			$e->process();
		} catch( Exception $e ) {
			echo $e->GetMessage();
			http_response_code( 500 );
		}
	}

	private function routing( &$path ) {
		if ( ! is_file( APP_SITE . '/sitemap.php' ) ) throw new Exception( 'Sitemap not found' );
		$map = require APP_SITE . '/sitemap.php';
		$this->map_parse( $map );

		$index = null;
		$args = [];

		$nice = '/';

		$flag = false;
		$node = strtok( $_SERVER['DOCUMENT_URI'], '/' );

		if ( $map[0] !== '' ) {
			$nice .= $map[0] . '/';
			if( $node === $map[0] ) {
				$node = strtok( '/' );
			} else {
				$flag = true;
			}
		}

		while ( $node ) {
			$flag = true;
			if ( empty( $map[2] ) ) break;
			foreach ( $map[2] as $map_nextlevel ) {
				if ( $map_nextlevel[0] === '*' ) {
					$flag = false;
					$nice .= $node . '/';
					$args[] = $node;
					$node = strtok( '/' );
					$map = $map_nextlevel;
				} elseif ( $node === $map_nextlevel[0] ) {
					$flag = false;
					$nice .= $node . '/';
					$node = strtok( '/' );
					$map = $map_nextlevel;
					break;
				}
			}
			if ( $flag ) break;
		}

		if ( $map[1] === null ) {
			if ( $flag ) throw new EHttpClient( 404 );
			while( $map[1] === null ) {
				$map = reset( $map[2] );
				$nice .= $map[0] . '/';
			}
			throw new EHttpRedirect( $nice );
		} else {
			while( $node ) {
				if ( $index === null ) {
					$index = $node;
				} else {
					$args[] = $node;
				}
				$node = strtok( '/' );
			}
		}

		if ( ! $flag && $_SERVER['DOCUMENT_URI'] !== $nice ) throw new EHttpRedirect( $nice );

		$path = $nice;
		if ( $index === null ) {
			$index = 'index';
		} else {
			$index = ltrim( $index, '_' );
		}
		return [ $map[1], $index, $args ];
	}

	private function map_parse( &$map, $path = '' ) {
		$map[0] = trim( $map[0], ' /' );
		if ( $map[0] !== '' ) $path .= '/' . $map[0];

		$stack = explode( '/', $map[0] );
		$map[0] = array_pop( $stack );
		if ( $map[0] === '*' && $map[1] !== null ) throw new Exception( 'Sitemap error: Masked location must use no action at <tt>' . $path . '</tt>. Use parent\'s <tt>__call()</tt> method instead' );
		if ( empty( $map[2] ) ) {
			if ( $map[1] === null ) throw new Exception( 'Sitemap error: Dead-end detected at <tt>' . $path . '</tt>' );
		} else {
			$new = [];
			foreach( $map[2] as &$map_nextlevel ) {
				$this->map_parse( $map_nextlevel, $path );
				$id = $map_nextlevel[0];
				if ( empty( $new[$id] ) ) {
					$new[$id] = $map_nextlevel;
				} else {
					$new[$id][2] = array_merge( $new[$id][2], $map_nextlevel[2] );
				}
			}
			$map[2] = $new;
		}
		while ( $node = array_pop( $stack ) ) {
			$map = [ $node, null, [ $map[0] => $map ] ];
		}
	}

	private function loader( $classname ) {
		$classname = str_replace( '\\', '/', $classname );
		if ( is_file( APP_SITE.'/'.$classname.'.php' ) ) {
			include APP_SITE.'/'.$classname.'.php';
		} elseif ( is_file( APP_ROOT.'/'.$classname.'.php' ) ) {
			include APP_ROOT.'/'.$classname.'.php';
		}
	}
}


class EHttpRedirect extends Exception {
	private $url = null;

	public function __construct( $url = null ) {
		parent::__construct();
		$this->url = $url;
	}
	public function set_root( $root_uri ) {
		$this->uri = preg_replace( '/^\~/', $root_uri, $this->url );
	}
	public function process() {
		$scheme = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) $scheme = 'https';
		if ( isset( $_SERVER['REQUEST_SCHEME'] ) ) $scheme = $_SERVER['REQUEST_SCHEME'];
		$host = $_SERVER['SERVER_NAME'];
		if ( ! in_array( $scheme . $_SERVER['SERVER_PORT'], [ 'http80', 'https443' ] ) ) $host .= ':' . $_SERVER['SERVER_PORT'];
		header( 'Location: ' . $scheme . '://' . $host . $this->url, true, 302 ); // absolute path required due to RFC
		exit;
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

	protected $code = 400;
	protected $headers = null;
	protected $body = '';

	public function __construct( $code, $headers = null, $body = '' ) {
		$this->code = ( self::HTTP_STATUS[$code] ?? false ) ? $code : 400;
		$this->headers = ( is_array( $headers ) ) ? $headers : [];
		$this->body = $body;
		parent::__construct( self::HTTP_STATUS[$this->code] );
	}


	public function process(){
		foreach ( $this->headers as $k => $v ) {
			header( $k.': '.$v );
		}
		new \error\http( $this->code, $this->body );
//		http_response_code( $this->code );
//		echo $this->body;
	}
}
