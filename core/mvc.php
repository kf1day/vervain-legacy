<?php

namespace model {
	abstract class core {
		public function get(){}
	}
}

namespace view {
	abstract class core {
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
		
		public function load( $template ) {
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
}

namespace action {
	abstract class core {
		protected $tree = null;
		protected $path = null;
		public function __construct( $tree, $path ) {
			$this->tree = $tree;
			$this->path = $path;
		}
		public function index() {
			echo 'default $class->index()';
		}
	}
}
