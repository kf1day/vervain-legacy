<?php namespace model\cache;

class fs {
	
	protected $path = '';
	
	public function __construct() {
		$this->path = APP_ROOT.'/cache/'.APP_HASH;
		switch( @filetype( $this->path ) ) {
			case false:
				if ( ! mkdir( $this->path, 0700, true ) ) {
					throw new \Exception( 'Cannot create cache directory: '.$this->path );
				}
			case 'dir':
				return true;
				
			default:
				throw new \Exception( 'Cache path is not a directory: '.$this->path );
		}
	}
	
	public function set( $file, $data ) {
		return file_put_contents( $this->path.'/'.$file, $data );
	}
	
	public function get( $file ) {
		if ( ! is_file( $this->path.'/'.$file ) ) {
			return false;
		}
		return file_get_contents( $this->path.'/'.$file );
	}

	public function getpath() {
		return $this->path;
	}
}

