<?php namespace view;

abstract class core {

	final public function render( $template, $data ) {
		if ( !is_string( $template ) || !is_file( APP_SITE.$template ) ) exit ( 'Bad template' );
		$this->head();
		include APP_SITE.$template;
		$this->tail();
	}
	
	protected function head(){}
	protected function tail(){}
	

}
