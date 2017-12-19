<?php

final class http {

	private $path = '';

	public function __construct() {

		spl_autoload_register( [ $this, 'loader' ] );
		try {
			list( $tree, $action, $args ) = $this->follow();
			$index = ( count( $args ) > 0 ) ? $args[0] : 'index';
			if ( in_array( $index, SP_MAGIC ) ) $index = 'index';

			$cls = new ReflectionClass( '\\action\\'.$action );
			if ( ! $cls->isSubclassOf( '\\action\\core' ) ) {
				throw new Exception( 'Class in not an ACTION' );
			} elseif( $cls->hasMethod( $index ) ) {
				array_shift( $args );
				$cls->getMethod( $index )->invokeArgs( $cls->newInstance( $tree, $this->path ), $args );
			} elseif( $cls->hasMethod( '__call' ) ) {
				$cls->getMethod( '__call' )->invokeArgs( $cls->newInstance( $tree, $this->path ), $args );
			} else {
				throw new EHttpClient( 404, null, 'Method "\\action\\'.$action.'->'.$index.'" not found!');
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

	private function follow() {
		if ( ! is_file( APP_SITE.'/sitemap.php' ) ) throw new Exception( 'Sitemap not found' );
		$tree = new tree();
		$tmp = require APP_SITE.'/sitemap.php';
		$args = [];
		foreach( $tmp as $k => $v ) {
			$tree->add( $k, $v[0], [ 'path' => $v[1], 'ctl' => $v[2] ] );
		}
		$id = 0;
		$node = $tree->first_child( $id );


		$nice = '/';
		$flag = false;

		//strip trailing slash and explode
		$tmp = strtok( $_SERVER['DOCUMENT_URI'], '/' );
		while ( $tmp ) {
			$flag = true;
			if ( $child_nodes = $tree->dive( $id ) ) {
				foreach ( $child_nodes as $cid => $cnode ) {
					if ( $tmp == $cnode['path'] ) {
						$nice .= $tmp.'/';
						$node = $cnode;
						$id = $cid;
						$flag = false;
						break;
					}
				}
			}

			if ( $flag ) break;
			$tmp = strtok( '/' );
		}

		while ( $tmp ) {
			$args[] = $tmp;
			$tmp = strtok( '/' );
		}

		if ( $node['ctl'] === null ) {
			if ( ! $flag ) {
				while( ( $node['ctl'] === null ) && ( $node = $tree->first_child( $id ) ) ) $nice .= $node['path'].'/';
				if ( $node['ctl'] ) {
					throw new EHttpRedirect( $nice );
				}
			}
			throw new EHttpClient( 404 );
		} else {
			if ( ! $flag && ( $_SERVER['DOCUMENT_URI'] != $nice ) ) {
				throw new EHttpRedirect( $nice );
			}
		}
		$this->path =  $nice;
		return [ $tree, $node['ctl'], $args ];
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


class EHttpRedirect extends \Exception {
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
		$host = $_SERVER['SERVER_NAME'] . ( $_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT'] );
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
