<?php

namespace model {
	abstract class core {
		public function get(){}
	}
}

namespace view {
	abstract class core {
		final public function render( $template, $data = null ) {
			if ( !is_string( $template ) || !is_file( APP_SITE.$template ) ) exit ( 'Bad template' );
			$this->head();
			include APP_SITE.$template;
			$this->tail();
		}
		protected function head(){}
		protected function tail(){}
	}
}

namespace control {
	abstract class core {
		protected $map = null;
		final public function __construct( $map ) {
			$this->map = $map;
		}
		abstract public function run();
	}
}
