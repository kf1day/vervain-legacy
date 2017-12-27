<?php namespace model\db;

class mysql {

	private $pt = null;
	protected $rx = null;


	public function __construct( $host, $port, $base, $user, $pass ) {

		if ( $host && $port ) {
			$host .= ':'.$port;
		} else {
			$host = '';
		}

		$this->pt = new \mysqli( $host, $user, $pass, $base );

		if ( ! $this->pt ) {
			throw new \Exception( 'DBA connection failed' );
		} else {
			$this->pt->query( 'SET NAMES UTF8' );
		}
	}

	public function get( $table, $fields, $filter = false, $sort = false ) {
		if ( is_array( $fields ) ) {
			array_walk( $fields, [ $this, 'escape' ] );
			$fields = implode( '`, `', $fields );
		}
		$q = 'SELECT `'.$fields.'` FROM `'.$table.'`';
		if ( is_array( $filter ) && count( $filter ) > 0 ) {
			$t = [];
			array_walk( $filter, [ $this, 'escape' ] );
			foreach( $filter as $k => $v ) $t[] = '`'.$k.'` = "'.$v.'"';
			$q .= ' WHERE '.implode( ' AND ', $t );
		}
		if ( ( is_array( $sort ) && count( $sort ) > 0 ) || ( $sort && $sort = [ $sort ] ) ) {
			$t = [];
			array_walk( $sort, [ $this, 'escape' ] );
			foreach( $sort as $v ) {
				$v = '`'.$v.'`';
				$v = preg_replace( '/^`\+(.*)`$/', '`$1` ASC', $v );
				$v = preg_replace( '/^`\-(.*)`$/', '`$1` DESC', $v );
				$t[] = $v;
			}
			$q .= ' ORDER BY '.implode( ', ', $t );
		}
		$this->rx = $this->pt->query( $q.';' );
		if ( ! $this->rx ) throw new \Exception( 'DBA query error: '.$q.';' );
		return $this->rx->num_rows;
	}

	public function fetch() {
		if ( $this->rx ) {
			return $this->rx->fetch_row();
		} else {
			return false;
		}
	}

	public function fetch_all() {
		$fff = [];
		if ( $this->rx ) {
			while( $tmp = $this->rx->fetch_row() ) {
				$fff[] = $tmp;
			}
		} else {
			return false;
		}
		return $fff;
	}

	public function raw( $q ) {
		$this->rx = $this->pt->query( $q.';' );
		if ( ! $this->rx ) throw new \Exception( 'DBA query error: '.$q.';' );
		return $this->rx->num_rows ?? 0;
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

	protected function escape( &$item, &$key ) {
		$item = preg_replace( '/[ ;\'"]/', '', $item );
		$key = preg_replace( '/[ ;\'"]/', '', $key );
	}

}
