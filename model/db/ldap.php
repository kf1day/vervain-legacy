<?php namespace model\db;

class ldap extends \app\model {

	protected $pt = false;
	protected $rx = false;
	protected $rz = [];
	protected $root = '';

	public function __construct( $host, $port, $base, $user, $pass ) {

		$port = ( $port ) ? ':'.$port : '';
		$this->pt = ldap_connect( 'ldap://'.$host.$port.'/' );
		ldap_set_option( $this->pt, LDAP_OPT_PROTOCOL_VERSION, 3 );
		ldap_set_option( $this->pt, LDAP_OPT_REFERRALS, 0 );

		if ( ! @ldap_bind( $this->pt, $user, $pass ) ) {
				throw new \Exception( 'DBA connection failed' );
		}
		$this->root = $base;
	}

	public function get( $table, $fields, $filter, $order = null ) {
		$root = $table ?? $this->root;
		$filt = '';
		foreach ( $filter as $k => $v ) {
			$filt .= '('.$k.'='.$v.')';
		}
		if ( count( $filter > 1 ) ) $filt = '(&'.$filt.')';
		if ( $s = ldap_search( $this->pt, $root, $filt, $fields ) ) {
			$this->rx = ldap_first_entry( $this->pt, $s );
			$this->rz = $fields;
			return ldap_count_entries( $this->pt, $s );
		}
		$this->rz = [];
		return false;
	}

	public function fetch( &$dn = null ) {
		if ( ! $this->rx ) return false;
		$ret = [];
		foreach( $this->rz as $k => $v ) {
			$tmp =  ldap_get_values( $this->pt, $this->rx, $v );
			$tmp = $tmp[0] ?? null;
			if ( $tmp ) {
				if ( strtolower( $v ) == 'objectsid' ) {
					$ret[$k] = $this->bin2sid( $tmp );
					continue;
				}
				if ( ! mb_check_encoding( $tmp ) ) {
					$tmp = bin2hex( $tmp );
				}
			}
			$ret[$k] = $tmp;
		}
		$dn = ldap_get_dn( $this->pt, $this->rx );
		$this->rx = ldap_next_entry( $this->pt, $this->rx );
		return $ret;
	}


	public function fetch_all() {
		$ret = [];
		while ( $tmp = $this->fetch() ) {
			$ret[] = $tmp;
		}
		return $ret;
	}


	protected function bin2sid ( $b ) {
		$s = 'S-';
		$r = '';
		$h = str_split( bin2hex( $b ), 2 );
		$s .= hexdec( $h[0] ).'-'.hexdec( $h[2].$h[3].$h[4].$h[5].$h[6].$h[7] );
		for ( $i = 0; $i < hexdec( $h[1] ); $i++ ) {
			$v = 8 + ( 4 * $i );
			if ( $i < 4 ) {
				$s .= "-".hexdec( $h[$v+3].$h[$v+2].$h[$v+1].$h[$v] );
			} else {
				$r .= hexdec( $h[$v+3].$h[$v+2].$h[$v+1].$h[$v] );
			}
		}
		return [ $s, $r ];
	}


}