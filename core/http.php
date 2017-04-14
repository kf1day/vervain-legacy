<?php

class http {

	static public function request() {

		define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
		if ( ! is_file( APP_SITE.'/sitemap.php' ) ) exit( 'Sitemap not found' );
		
		$map = new map();
		$tmp = require APP_SITE.'/sitemap.php';
		foreach( $tmp as $k => $v ) {
			$map->add( $k, $v[0], [ 'path' => $v[1], 'ctl' => $v[2], 'acl' => $v[3] ] );
		}

		$id = 0;
		
		$node = $map->firstchild( $id );
		$args = [];

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
//						array_shift( $stack );
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
					self::redirect( $nice );
				}
			}
			self::notfound();
		} else {
			if ( ! $flag && ( $_SERVER['REQUEST_URI'] != $nice ) ) {
				self::redirect( $nice );
			}
			// invokation:
			spl_autoload_register( [ 'self', 'loader' ] );
			$tmp = new ReflectionClass( '\\ctl\\'.$node['ctl'] );
			$tmp->getMethod( 'run' )->invokeArgs( $tmp->newInstance(), $args );

		}
	}

	static public function notfound( $e = null ) {
		http_response_code( 404 );
		exit( $e );
	}

	static public function redirect( $a ) {
		header( 'Location: '.( $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'].$a, true, 302 ); // absolute path required due to HTTP/1.1
		exit;
	}
	
	static private function loader( $classname ) {
		$classname = str_replace( '\\', '/', $classname );
//		echo APP_SITE.'/'.$classname.'.php';
		if ( is_file( APP_SITE.'/'.$classname.'.php' ) ) {
			include APP_SITE.'/'.$classname.'.php';
		} elseif ( is_file( APP_ROOT.'/'.$classname.'.php' ) ) {
			include APP_ROOT.'/'.$classname.'.php';
		}
	}
}