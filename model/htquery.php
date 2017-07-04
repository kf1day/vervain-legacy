<?php namespace model;

class htquery {
	
	private static function query( $uri, $method, $headers  ) {
		$headers_str = '';
		foreach( $headers as $k => $v ) {
			$headers_str .= $k.': '.$v."\r\n";
		}
		$options['http']['method'] = $method;
		if ( $headers_str !== '' ) $options['http']['header'] = $headers_str;
		
		$context = stream_context_create( $options );
		return file_get_contents( $uri, false, $context );
	}
	
	public static function json( $uri, $method, $headers = null ) {
		$data = self::query( $uri, $method, $headers );
		return json_decode( $data, true );
	}
}
