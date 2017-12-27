<?php namespace view;

class flow extends core {

	public function display( $vars = null ) {
		$patt = false;
		$otag = '<!--';
		if ( is_array( $vars ) && count( $vars ) > 0 ) {
			$patt = [];
			foreach ( $vars as $k => $v ) $patt[ '{$'.$k.'}' ] = $v;
		}
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!DOCTYPE html>';
		while ( $tpl = $this->fetch() ) {
			$h = fopen( $tpl, 'r' );
			if ( $h ) {
				while ( ( $line = fgets( $h ) ) !== false ) {
					$line = trim( $line, "\n\r\0" );
					if ( $patt ) $line = strtr( $line, $patt );
					$tok = explode( $otag, $line, 2 );
					while ( count( $tok ) == 2 ) {
						if ( $otag == '<!--' ) {
							$otag = '-->';
							echo $tok[0];
						} else {
							$otag = '<!--';
						}
						$tok = explode( $otag, $line = $tok[1], 2 );
					}
					if ( $otag == '<!--' ) echo $line;
					
				}
			}
			fclose( $h );
		}
	}
}