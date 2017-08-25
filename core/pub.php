<?php

class map {

	private $pt = [ 0 => [ 0, false, [], false ] ];
						// 0: id
						// 1: pointer to parent
						// 2: array pointer to child
						// 3: mixed data

	public function add( $id, $rel, $node ) {
		if ( !isset( $this->pt[$rel] ) ) $rel = 0;
		$this->pt[$id] = [ $id, &$this->pt[$rel], [], $node ];
		$this->pt[$rel][2][] = &$this->pt[$id];
	}

	public function dive( $id ) {
		if ( !isset( $this->pt[$id] ) ) return false;
		$fff = false;
		foreach ( $this->pt[$id][2] as $v ) { 
			$fff[$v[0]] = $v[3];
		}
		return $fff;
	}

	public function firstchild( &$id ) {
		if ( !isset( $this->pt[$id] ) || !isset( $this->pt[$id][2][0] ) ) return false;
		$id = $this->pt[$id][2][0][0]; // some node -> array children -> first node -> id
		return $this->pt[$id][3];
	}

	public function pop( &$id ) {
		if ( $id == 0 || !isset( $this->pt[$id] ) ) return false;
		$ido = $id;
		$id = $this->pt[$id][1][0]; // some node -> parent -> id
		return $this->pt[$ido][3];
	}
}


class cache {
	
	protected $path = '';
	
	public function __construct( $module_name ) {
		$this->path = APP_CACHE.'/'.$module_name;
		switch( @filetype( $this->path ) ) {
			case false:
				if ( ! mkdir( $this->path, 0700, true ) ) {
					throw new Exception( 'Cannot create cache directory: '.$this->path );
				}
			case 'dir':
				return true;
				
			default:
				throw new Exception( 'Cache path is not a directory: '.$this->path );
		}
	}
	
	public function store( $file, $data ) {
		return file_put_contents( $this->path.'/'.$file, serialize( $data ) );
	}
	
	public function fetch( $file ) {
		if ( ! is_file( $this->path.'/'.$file ) ) {
			return false;
		}
		$data = file_get_contents( $this->path.'/'.$file );
		if ( $data && $data = @unserialize( $data ) ) {
			return $data;
		} else {
			return false;
		}
	}

	public function getpath() {
		return $this->path;
	}
}

