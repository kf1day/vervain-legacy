<?php

class http {
	
	private $path = '';
	
	public function __construct() {
		define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
		
		spl_autoload_register( [ $this, 'loader' ] );
		try {
			list( $map, $action, $args ) = $this->follow();
			$index = ( count( $args ) > 0 ) ? array_shift( $args ) : 'index';
			
//			echo '\\action\\'.$action.'->'.$index.'('.print_r( $args, 1 ).')'; exit;
			
			$tmp = new ReflectionClass( '\\action\\'.$action );
			if ( ! $tmp->isSubclassOf( '\\action\\core' ) ) {
				throw new Exception( 'Class in not an ACTION' );
			} elseif( ! $tmp->hasMethod( $index ) ) {
				throw new \HttpNotFoundException( 'Method "'.$index.'" not found!');
			}
			$tmp->getMethod( $index )->invokeArgs( $tmp->newInstance( $map, $this->path ), $args );
		} catch( HttpRedirectException $e ) {
			$e->set_root( $this->path );
			$e->process();
		} catch( HttpClientException $e ) {
			$e->process();
		} catch( Exception $e ) {
			echo $e->GetMessage();
			http_response_code( 500 );
			exit;
		}
	}

	private function follow() {
		if ( ! is_file( APP_SITE.'/sitemap.php' ) ) throw new Exception( 'Sitemap not found' );
		$map = new map();
		$tmp = require APP_SITE.'/sitemap.php';
		$args = [];
		foreach( $tmp as $k => $v ) {
			$map->add( $k, $v[0], [ 'path' => $v[1], 'ctl' => $v[2] ] );
		}
		$id = 0;
		
		$node = $map->firstchild( $id );

		$nice = '/';
		$flag = false;

		//strip trailing slash and explode
		$stack =  explode( '/', $_SERVER['DOCUMENT_URI'] );

		while ( $tmp = next( $stack ) ) {
			$flag = true;
			if ( $child_nodes = $map->dive( $id ) ) {
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
		}

		if ( $tmp ) $args[] = $tmp;
		while ( $tmp = next( $stack ) ) $args[] = $tmp;

		if ( $node['ctl'] === null ) {
			if ( ! $flag ) {
				while( ( $node['ctl'] === null ) && ( $node = $map->firstchild( $id ) ) ) $nice .= $node['path'].'/';
				if ( $node['ctl'] ) {
					throw new HttpRedirectException( $nice );
				}
			}
			throw new HttpNotFoundException();
		} else {
			if ( ! $flag && ( $_SERVER['DOCUMENT_URI'] != $nice ) ) {
				throw new HttpRedirectException( $nice );
			}
		}
		$this->path =  $nice;
		return [ $map, $node['ctl'], $args ];
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


class HttpRedirectException extends Exception {
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
		header( 'Location: '.$scheme.'://'.$_SERVER['HTTP_HOST'].$this->url, true, 302 ); // absolute path required due to HTTP/1.1
		exit;
	}
}

class HttpClientException extends Exception {
	public function process(){}
	protected function action( $code ) {
		new \error\http( $code );
	}
}

class HttpForbiddenException extends HttpClientException {
	public function process() {
		parent::action( 403 );
	}
}

class HttpNotFoundException extends HttpClientException {
	public function process() {
		parent::action( 404 );
	}
}
