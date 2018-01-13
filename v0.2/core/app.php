<?php namespace app;

abstract class model {
	/* Is there any common methods 4 all models? May be this class is just useless */
}


abstract class view {
	protected $tpl_path = '';
	protected $tpl_list = [];
	protected $tpl_next = null;

	public function __construct( $tpl_path = '' ) {
		$this->tpl_path = ( $tpl_path == '' ) ? APP_SITE : APP_SITE.'/'.trim( $tpl_path, '/' );
		if ( ! is_dir( $this->tpl_path ) ) throw new \Exception( 'Template directory is unreadable: '.$this->tpl_path );
	}

	final public function __invoke( $vars = null ) {
		$this->display( $vars );
	}

	public function load() {
		$template = func_get_args();
		foreach( $template as $tmp ) {
			if ( is_string( $tmp ) ) {
				if ( is_file( $this->tpl_path.'/'.$tmp ) ) {
					$this->tpl_list[] = $tmp;
				} else {
					throw new \Exception( 'Template is not found: '.$this->tpl_path.'/'.$tmp );
				}
			}
		}
	}

	protected function fetch() {
		if ( $this->tpl_next === false ) {
			return false;
		} elseif ( $this->tpl_next === null ) {
			$tmp = reset( $this->tpl_list );
		} else {
			$tmp = next( $this->tpl_list );
		}
		if ( $this->tpl_next = $tmp ) {
			return $this->tpl_path.'/'.$tmp;
		}
		return false;
	}

	abstract public function display( $vars = null );
}

abstract class action {
	protected $map;
	protected $path = '';
	final public function __construct( \map $map, string $path ) {
		$this->map = $map;
		$this->path = $path;
	}
	public function index() {
		echo 'default $class->index()';
	}
}
