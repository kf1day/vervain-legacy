<?php

final class map {

	private $pt = null;

	public function __construct() {
		if ( ! is_file( APP_SITE . '/sitemap.php' ) ) throw new Exception( 'Sitemap not found' );
		$this->pt = require APP_SITE . '/sitemap.php';
		$this->parse( $this->pt );
	}

	public function routing( &$path ) {

//		header( 'Content-Type: text/plain' ); print_r( $this->pt ); exit;

		$map = $this->pt;

		$index = null;
		$args = [];

		$nice = '/';

		$flag = false;
		$node = strtok( $path, '/' );

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
			foreach ( $map[2] as $map_child ) {
				if ( $map_child[0] === '*' ) {
					$flag = false;
					$nice .= $node . '/';
					$args[] = $node;
					$node = strtok( '/' );
					$map = $map_child;
				} elseif ( $node === $map_child[0] ) {
					$flag = false;
					$nice .= $node . '/';
					$node = strtok( '/' );
					$map = $map_child;
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

		if ( ! $flag && $path !== $nice ) throw new EHttpRedirect( $nice );

		$path = $nice;
		if ( $index === null ) {
			$index = 'index';
		} else {
			$index = ltrim( $index, '_' );
		}
		return [ $map[1], $index, $args ];
	}

	private function parse( &$map, $path = '' ) {
		$map[0] = trim( $map[0], ' /' );
		if ( $map[0] !== '' ) $path .= '/' . $map[0];
		$stack = explode( '/', $map[0] );
		$map[0] = array_pop( $stack );
		if ( $map[0] === '*' && $map[1] !== null ) throw new Exception( 'Sitemap error: Masked location must use no action at <tt>' . $path . '</tt>. Use parent\'s <tt>__call()</tt> method instead' );

		while ( $node = array_pop( $stack ) ) {
			$map = [ $node, null, [ $map ] ];
		}

		if ( empty( $map[2] ) ) {
			if ( $map[1] === null ) throw new Exception( 'Sitemap error: Dead-end detected at <tt>' . $path . '</tt>' );
		} else {
			foreach( $map[2] as &$map_child ) {
				$this->parse( $map_child, $path );
			}
			$this->merge( $map[2] );
		}
	}

	private function merge( &$map_child_array ) {
//		header( 'Content-Type: text/plain' ); print_r( $map_child_array ); exit;
		$uniq = [];
		foreach( $map_child_array as $k => &$map_child ) {
			$id = $map_child[0];
			if ( isset( $uniq[$id] ) ) {
//				header( 'Content-Type: text/plain' ); print_r( $uniq[$id] ); print_r( $map_child ); exit;
				$a = empty( $uniq[$id][2] );
				$b = empty( $map_child[2] );
				if ( !$a && !$b ) {
					$uniq[$id][2] = array_merge( $uniq[$id][2], $map_child[2] );
				} elseif ( $a && !$b ) {
					$uniq[$id][2] = $map_child[2];
				}
				if ( $uniq[$id][1] === null ) $uniq[$id][1] = $map_child[1];
				unset( $map_child_array[$k] );
			} else {
				$uniq[$id] = &$map_child;
			}
		}
		foreach( $uniq as &$map_child ) {
			if ( ! empty( $map_child[2] ) ) $this->merge( $map_child[2] );
		}
	}
}