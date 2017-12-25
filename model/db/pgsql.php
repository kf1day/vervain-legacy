<?php namespace model\db;

class pgsql extends \app\model implements _sql {

	protected $pt = null;
	protected $rx = null;

	public function __construct( $host, $port, $base, $user, $pass ) {
		
		if ( ! extension_loaded( 'pgsql' ) ) throw new \Exception( 'PGSQL module not loaded' );
		
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

		if ( is_array( $filter ) && ! empty( $filter ) ) {
			$q = pg_select( $this->pt, $table, $filter, PGSQL_DML_STRING );
			$q = rtrim( $q, ';' );
			$t = explode( '*', $q, 2 );
			$q = sprintf( '%s "%s" %s', $t[0], implode( '", "', $fields ), $t[1] );
		} else {
			$q = sprintf( 'SELECT "%s" FROM "%s"', implode( '", "', $fields ), $table );
		}

		if ( ( is_array( $sort ) && ! empty( $sort ) ) ) {

			foreach( $sort as &$v ) {
				$t = ltrim( $v, '+-' );
				if ( $v[0] === '+' ) {
					$v = '"' . $t . '" ASC';
				} elseif ( $v[0] === '-' ) {
					$v = '"' . $t . '" DESC';
				} else {
					$v = '"' . $t . '"';
				}
			}
			$q .= ' ORDER BY '.implode( ', ', $sort );
		}
		$this->rx = @pg_query( $this->pt, $q.';' );
		if ( ! $this->rx ) throw new \Exception( 'PGSQL query error: ' . pg_last_error() );
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
		if ( $this->rx ) {
			$fff = [];
			while( $t = pg_fetch_row( $this->rx ) ) {
				$fff[] = $t;
			}
			pg_free_result( $this->rx );
			$this->rx = null;
			return $fff;
		} else {
			return false;
		}
	}

	public function put( string $table, array $fields ) {
		$q = pg_insert( $this->pt, $table, $fields, PGSQL_DML_STRING );
		if ( $q !== false ) $q = pg_query( $this->pt, $q );
		
		if ( ! $q ) throw new \Exception( 'PGSQL query error: ' . pg_last_error() );
		$t = pg_affected_rows( $q );
		pg_free_result( $q );
		return $t;
	}

	public function del( string $table, array $filter ) {
		$q = pg_delete( $this->pt, $table, $filter, PGSQL_DML_STRING );
		if ( $q !== false ) $q = pg_query( $this->pt, $q );
		
		if ( ! $q ) throw new \Exception( 'PGSQL query error: ' . pg_last_error() );
		$t = pg_affected_rows( $q );
		pg_free_result( $q );
		return $t;
	}

	public function upd( string $table, array $fields, array $filter ) {
		$q = pg_update( $this->pt, $table, $fields, $filter, PGSQL_DML_STRING );
		if ( $q !== false ) $q = pg_query( $this->pt, $q );
		
		if ( ! $q ) throw new \Exception( 'PGSQL query error: ' . pg_last_error() );
		$t = pg_affected_rows( $q );
		pg_free_result( $q );
		return $t;
	}

}
