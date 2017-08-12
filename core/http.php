<?php

class http {
	
	private $path = '';
	
	public function __construct() {
		define( 'APP_SITE', $_SERVER['DOCUMENT_ROOT'] );
		define( 'APP_CACHE', APP_ROOT.'/cache/'.hash( 'md4', $_SERVER['DOCUMENT_ROOT'] ) );
		
		spl_autoload_register( [ $this, 'loader' ] );
		try {
			list( $map, $action, $args ) = $this->follow();
			$index = ( count( $args ) > 0 ) ? $args[0] : 'index';
			if ( in_array( $index, SP_MAGIC ) ) $index = 'index';
			
//			echo '\\action\\'.$action.'->'.$index.'('.print_r( $args, 1 ).')'; exit;
			
			$cls = new ReflectionClass( '\\action\\'.$action );
			if ( ! $cls->isSubclassOf( '\\action\\core' ) ) {
				throw new Exception( 'Class in not an ACTION' );
			} elseif( $cls->hasMethod( $index ) ) {
				array_shift( $args );
				$cls->getMethod( $index )->invokeArgs( $cls->newInstance( $map, $this->path ), $args );
			} elseif( $cls->hasMethod( '__call' ) ) {
				$cls->getMethod( '__call' )->invokeArgs( $cls->newInstance( $map, $this->path ), $args );
			} else {
				throw new \ENotfound( 'Method "'.$index.'" not found!');
			}
		} catch( ERedirect $e ) {
			$e->set_root( $this->path );
			$e->process();
		} catch( EClient $e ) {
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
		$tmp = strtok( $_SERVER['DOCUMENT_URI'], '/' );

		while ( $tmp ) {
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
			$tmp = strtok( '/' );
		}

		while ( $tmp ) {
			$args[] = $tmp;
			$tmp = strtok( '/' );
		}

		if ( $node['ctl'] === null ) {
			if ( ! $flag ) {
				while( ( $node['ctl'] === null ) && ( $node = $map->firstchild( $id ) ) ) $nice .= $node['path'].'/';
				if ( $node['ctl'] ) {
					throw new ERedirect( $nice );
				}
			}
			throw new ENotfound();
		} else {
			if ( ! $flag && ( $_SERVER['DOCUMENT_URI'] != $nice ) ) {
				throw new ERedirect( $nice );
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


class ERedirect extends Exception {
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

class EClient extends Exception {
	public function process(){
		self::action( 400 );
	}
	protected function action( $code ) {
		new \error\http( $code );
	}
}

class EForbidden extends EClient {
	public function process() {
		parent::action( 403 );
	}
}

class ENotfound extends EClient {
	public function process() {
		parent::action( 404 );
	}
}
