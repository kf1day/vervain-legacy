<?php namespace model;

class vurl {

	protected static function build( $head, &$data ) {

		if ( ! is_array( $head ) ) $head = [];
		$data = ( $data && is_array( $data ) ) ? http_build_query( $data ) : '';

		$opts['http']['header'] = '';
		foreach( $head as $k => $v ) {
			$opts['http']['header'] .= $k.': '.$v."\r\n";
		}
		return $opts;
	}

	public static function get( $uri, $head = null, $data = null  ) {
		$opts = self::build( $head, $data );

		$opts['http']['method'] = 'GET';

		if ( $data !== '' ) $uri .= '?'.$data;

		$context = stream_context_create( $opts );
		return file_get_contents( $uri, false, $context );
	}

	public static function post( $uri, $head = null, $data = null  ) {

		$opts = self::build( $head, $data );

		$opts['http']['method'] = 'POST';
		$opts['http']['content'] = $data;
		$opts['http']['header'] .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";


		$context = stream_context_create( $opts );
		return file_get_contents( $uri, false, $context );
	}

/*	public static function json( $uri, $method, $head = null, $data = null ) {
		$resp = self::query( $uri, $method, $head, $data );
		return json_decode( $resp, true );
	}*/
}
