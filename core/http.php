<?php

class http {
	
	private $path = '';
	private $control = 'index';
	private $args = [];
	
	public function __construct() {
		define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
		
		spl_autoload_register( [ $this, 'loader' ] );
		try {
			$map = $this->follow();
			$tmp = new ReflectionClass( '\\control\\'.$this->control );
			$tmp->getMethod( 'run' )->invokeArgs( $tmp->newInstance( $map ), $this->args );
		} catch( HttpRedirectException $e ) {
			$e->set_root( $this->path );
			$e->process();
		} catch( HttpForbiddenException $e ) {
			$e->process();
		} catch( HttpNotFoundException $e ) {
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
		foreach( $tmp as $k => $v ) {
			$map->add( $k, $v[0], [ 'path' => $v[1], 'ctl' => $v[2] ] );
		}
		$id = 0;
		
		$node = $map->firstchild( $id );

		$nice = '/';
		$flag = false;

		//strip trailing slash and explode
		$stack =  explode( '/', $_SERVER['REQUEST_URI'] );

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

		if ( $tmp ) $this->args[] = $tmp;
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
			if ( ! $flag && ( $_SERVER['REQUEST_URI'] != $nice ) ) {
				throw new HttpRedirectException( $nice );
			}
		}
		$this->control = $node['ctl'];
		$this->path =  $nice;
		return $map;
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

class HttpForbiddenException extends Exception {
	public function process() {
		( new \control\http_status( null ) )->run( 403 );
	}
}

class HttpNotFoundException extends Exception {
	public function process() {
		( new \control\http_status( null ) )->run( 404 );
	}
}
