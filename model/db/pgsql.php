<?php namespace model\db;

class pgsql extends \app\model implements _sql {

	protected $pt = null;
	protected $rx = null;

	public function __construct( $host, $port, $base, $user, $pass ) {

		$s = "options='--client_encoding=UTF8'";
		if ( $host != '' ) $s .= ' host='.$host;
		if ( $port != '' ) $s .= ' port='.$port;
		if ( $base != '' ) $s .= ' dbname='.$base;
		if ( $user != '' ) $s .= ' user='.$user;
		if ( $pass != '' ) $s .= ' password='.$pass;
		$this->pt = @pg_pconnect( $s );

		if ( ! $this->pt ) {
			throw new \Exception( 'PGSQL connection failed' );
		}
	}

	public function get( string $table, array $fields, $filter = null, $sort = null ) {
		$this->select( $table, $fields, $filter, $sort );
		return $this->fetch_all();
	}

	public function select( string $table, array $fields, $filter = null, $sort = null ) {
		if ( is_array( $fields ) ) {
			$fields = implode( '", "', $fields );
		}
		$q = 'SELECT "'.$fields.'" FROM "'.$table.'"';
		if ( is_array( $filter ) && count( $filter ) > 0 ) {
			$t = [];
			foreach( $filter as $k => $v ) $t[] = '"'.$k.'" = '.$v.'';
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
		$this->rx = @pg_query( $this->pt, $q.';' );
		if ( ! $this->rx ) throw new \Exception( 'DBA query error: '.$q.';' );
		return pg_num_rows( $this->rx );
	}

	public function fetch() {
		if ( $this->rx ) {
			return pg_fetch_row( $this->rx );
		} else {
			return false;
		}
	}

	public function fetch_all() {
		$fff = [];
		if ( $this->rx ) {
			while( $tmp = pg_fetch_row( $this->rx ) ) {
				$fff[] = $tmp;
			}
		} else {
			return false;
		}
		return $fff;
	}

	public function put( string $table, array $fields ) {
		if ( !is_array( $fields ) || count( $fields ) == 0 ) return false;
		$q = 'INSERT INTO `'.$table.'` ('.implode(',', array_keys( $fields ) ).') VALUES("'.implode('", "', $fields ).'")';
		$q = $this->pt->query( $q.';' );
		return ( $q ) ? $this->pt->insert_id : false;
	}

	public function del( string $table, array $case ) {
		if ( !is_array( $case ) || count( $case ) == 0 ) return false;
		$qcase = [];
		foreach( $case as $k => $v ) {
			$qcase[] = '`'.$k.'` = "'.$v.'"';
		}
		$q = 'DELETE FROM `'.$table.'` WHERE '.implode( ' AND ', $qcase );
		$q = $this->pt->query( $q.';' );
		return ( $q ) ? $this->pt->affected_rows : false;
	}

	public function upd( string $table, array $fields, $case = null ) {
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
