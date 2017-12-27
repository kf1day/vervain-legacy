<?php namespace model;

class htquery {

	private static function query( $uri, $method, $head = null, $data = null  ) {

		$head = ( $head && is_array( $head ) ) ? $head : [];
		$data = ( $data && is_array( $data ) ) ? http_build_query( $data ) : '';

		$opts['http']['method'] = $method;

		if ( $data ) {
			if ( $method == 'POST' ) {
				$head['Content-Type'] = 'application/x-www-form-urlencoded';
				$opts['http']['content'] = $data;
			} elseif ( $method == 'GET' ) {
				$uri .= '?'.$data;
			}
		}
		$opts['http']['header'] = '';
		if ( is_array( $head ) ) {
			foreach( $head as $k => $v ) {
				$opts['http']['header'] .= $k.': '.$v."\r\n";
			}
		}

//		print_r( $opts ); exit;
		$context = stream_context_create( $opts );
		return file_get_contents( $uri, false, $context );
	}

	public static function json( $uri, $method, $head = null, $data = null ) {
		$resp = self::query( $uri, $method, $head, $data );
		return json_decode( $resp, true );
	}
}
