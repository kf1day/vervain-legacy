<?php

namespace model {
	abstract class core {
		public function get(){}
	}
}

namespace view {
	abstract class core {
		protected $template_list = [];
		
		public function load_template( $template ) {
			if ( is_string( $template ) && is_file( APP_SITE.$template ) ) {
				$this->template_list[] = $template;
				return true;
			} elseif( is_array( $template ) ) {
				foreach( $template as $v ) {
					$this->load_template( $v );
				}
				return true;
			}
			return false;
		}
		public function render( $data = null ) {
			foreach( $this->template_list as $v ) {
				include APP_SITE.$v;
			}
		}
	}
}

namespace action {
	abstract class core {
		protected $map = null;
		protected $path = null;
		public function __construct( $map, $path ) {
			$this->map = $map;
			$this->path = $path;
		}
		public function index() {
			echo 'default $class->index()';
		}
	}
}
