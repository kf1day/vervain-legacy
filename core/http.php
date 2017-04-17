<?php

class http {
	
	public function __construct() {
		define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
		if ( ! is_file( APP_SITE.'/sitemap.php' ) ) exit( 'Sitemap not found' );
		
		$map = new map();
		$args = [];
		$control = $this->follow( $map, $args );
		spl_autoload_register( [ $this, 'loader' ] );
		try {
			$tmp = new ReflectionClass( '\\control\\'.$control );
			$tmp->getMethod( 'run' )->invokeArgs( $tmp->newInstance( $map ), $args );
		} catch( HttpRedirectException $e ) {
			$this->redirect( $e->getMessage() );
		} catch( Exception $e ) {
			var_dump( $e );
			exit;
		}
	}

	private function follow( &$map, &$args ) {
		$tmp = require APP_SITE.'/sitemap.php';
		foreach( $tmp as $k => $v ) {
			$map->add( $k, $v[0], [ 'path' => $v[1], 'ctl' => $v[2], 'acl' => $v[3] ] );
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

		if ( $tmp ) $args[] = $tmp;
		while ( $tmp = next( $stack ) ) $args[] = $tmp;

		if ( $node['ctl'] === null ) {
			if ( ! $flag ) {
				while( ( $node['ctl'] === null ) && ( $node = $map->firstchild( $id ) ) ) $nice .= $node['path'].'/';
				if ( $node['ctl'] ) {
					$this->redirect( $nice );
				}
			}
			$this->notfound();
		} else {
			if ( ! $flag && ( $_SERVER['REQUEST_URI'] != $nice ) ) {
				$this->redirect( $nice );
			}
		}
		return $node['ctl'];
	}

	public function notfound( $e = null ) {
		http_response_code( 404 );
		exit( $e );
	}

	public function redirect( $a ) {
		header( 'Location: '.( $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'].$a, true, 302 ); // absolute path required due to HTTP/1.1
		exit;
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