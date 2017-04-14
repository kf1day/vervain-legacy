<?php namespace model\db;

class pgsql {

	private $pt = null;
	private $f = [ 'query' => '', 'fetch' => '' ];


	public function __construct( $host, $port, $base, $user, $pass ) {

		$s = "options='--client_encoding=UTF8'";
		if ( $host != '' ) $s .= ' host='.$host;
		if ( $port != '' ) $s .= ' port='.$port;
		if ( $base != '' ) $s .= ' dbname='.$base;
		if ( $user != '' ) $s .= ' user='.$user;
		if ( $pass != '' ) $s .= ' password='.$pass;
		$this->pt = @pg_connect( $s );

		if ( ! $this->pt ) {
			throw new Exception( 'DBA connection failed' );
		}
	}

	public function get( $table, $fields, $filter = false, $sort = false ) {
		$mt = false;
		$fff = [];
		if ( is_array( $fields ) ) {
			$mt = true;
			$fields = implode( '", "', $fields );
		}
		$q = 'SELECT "'.$fields.'" FROM "'.$table.'"';
		if ( is_array( $filter ) && count( $filter ) > 0 ) {
			$t = [];
			foreach( $filter as $k => $v ) $t[] = '"'.$k.'" = "'.$v.'"';
			$q .= ' WHERE '.implode( ' AND ', $t );
		}
		if ( ( is_array( $sort ) && count( $sort ) > 0 ) || ( $sort && $sort = [ $sort ] ) ) {
			$t = [];
			foreach( $sort as $v ) {
				$v = '"'.$v.'"';
				$v = preg_replace( '/^"\+(.*)"$/', '"$1" ASC', $v );
				$v = preg_replace( '/^"\-(.*)"$/', '"$1" DESC', $v );
				$t[] = $v;
			}
			$q .= ' ORDER BY '.implode( ', ', $t );
		}
		$res = @pg_query( $q.';' );
		if ( ! $res ) exit( 'Query error: '.$q );
		while ( $r = pg_fetch_row( $res ) ) {
			$fff[] = ( $mt ) ? $r : $r[0];
		}
		return $fff;
	}

	public function raw( $query ) {
		$fff = [];
		$q = 'SELECT '.$query;
		$q = $this->pt->query( $q.';' );
		while ( $r = $q->fetch_row() ) {
			$fff[] = $r;
		}
		return $fff;
	}

	public function put( $table, $fields ) {
		if ( !is_array( $fields ) || count( $fields ) == 0 ) return false;
		$q = 'INSERT INTO `'.$table.'` ('.implode(',', array_keys( $fields ) ).') VALUES("'.implode('", "', $fields ).'")';
		$q = $this->pt->query( $q.';' );
		return ( $q ) ? $this->pt->insert_id : false;
	}

	public function del( $table, $case ) {
		if ( !is_array( $case ) || count( $case ) == 0 ) return false;
		$qcase = [];
		foreach( $case as $k => $v ) {
			$qcase[] = '`'.$k.'` = "'.$v.'"';
		}
		$q = 'DELETE FROM `'.$table.'` WHERE '.implode( ' AND ', $qcase );
		$q = $this->pt->query( $q.';' );
		return ( $q ) ? $this->pt->affected_rows : false;
	}

	public function upd( $table, $fields, $case ) {
		if ( !is_array( $fields ) || count( $fields ) == 0 ) return false;
		if ( !is_array( $case ) || count( $case ) == 0 ) return false;
		$q = [];
		foreach( $fields as $k => $v ) {
			if ( !is_numeric( $v ) ) $v = '"'.$v.'"';
			$qfields[] = '`'.$k.'` = '.$v;
		}
		foreach( $case as $k => $v ) {
			$qcase[] = '`'.$k.'` = "'.$v.'"';
		}
		$q = 'UPDATE `'.$table.'` SET '.implode( ', ', $qfields).' WHERE '.implode( ' AND ', $qcase );
		$q = $this->pt->query( $q.';' );
		return ( $q ) ? $this->pt->affected_rows : false;
	}

}
