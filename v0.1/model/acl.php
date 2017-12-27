<?php namespace model;

class acl extends core {

	protected $ldap = [];
	protected $user = '';
	protected $gsec = [];


	public function __construct( $cacher ) {
		$this->cache = $cacher;
	}

	public function ldap_add_server( $host, $port, $base, $user, $pass ) {
		$this->ldap[] = [ $host, $port, $base, $user, $pass ];
	}

	public function login_gss() {
		$upn = strtolower( $_SERVER['PHP_AUTH_USER'] ?? '' );
		$data = $this->tokens_get_ldap( $upn );
		if ( $data ) {
			$uid = $this->usercode( $upn );
			$this->tokens_set_cache( $uid, $data['secret'], $data['name'], $data['gsec'] );
			return [ 'uid' => $uid, 'hash' => $this->keyring( $data['secret'] ) ];
		} else {
			return false;
		}
	}

	public function auth_cookie() {
		$uid = $_COOKIE['UID'] ?? false;
		$upn = '';
		$data = [];
		$hash = $_COOKIE['HASH'] ?? false;
		if ( $uid && $hash ) {
			if ( ( $data = $this->tokens_get_cache( $uid ) ) && $this->keyring( $data['secret'], $hash ) ) { // have cache + cache is valid
			$this->user = $data['name'];
				$this->gsec = $data['gsec'];
				return true;
			} elseif( ( $upn = $this->usercode( $uid, 1 ) ) && $data = $this->tokens_get_ldap( $upn ) && $this->keyring( $data['secret'], $hash ) ) {// search ldap
				$this->user = $data['name'];
				$this->gsec = $data['gsec'];
				$this->tokens_set_cache( $uid, $data['secret'], $data['name'], $data['gsec'] );
				return true;
			}
		}
		throw new \EForbidden();
	}

	public function get_name() {
		return $this->user;
	}

	public function get_gsec() {
		$ret = [];
		foreach ( $this->gsec as $dom => $v ) {
			foreach ( $v as $num ) {
				$ret[] = ( $num ) ? $dom.'-'.$num : $dom;
			}

		}
		return implode( '|', $ret );
	}






	protected function keyring( $secret, $compare = null ) {
		$hash = hash( 'sha256', APP_HASH.$secret.$_SERVER['REMOTE_ADDR'] );
		if ( $compare ) {
			return ( $hash == $compare );
		} else {
			return $hash;
		}
	}

	protected function usercode( $upn_uid, $decode = false ) {
		if ( $decode ) {
			return @hex2bin( $upn_uid ) ?? false;
		} else {
			return bin2hex( $upn_uid );
		}
	}

	protected function tokens_set_cache( $uid, $secret, $name, $gsec ) {
		$this->cache->set( $uid, [ $secret, $name, $gsec ] );
	}

	protected function tokens_get_cache( $uid ) {
		$ret = false;
//		foreach ( $this->memcache as &$v ) {}
//			if ( is_array( $v ) ) $v = new db\ldap( $v[0], $v[1], $v[2], $v[3], $v[4] );
		$data = $this->cache->get( $uid );
		if ( $data ) {
			return [ 'secret' => $data[0], 'name' => $data[1], 'gsec' => $data[2] ];
		} else {
			return false;
		}
	}

	protected function tokens_get_ldap( $upn ) {
		$dn = '';
		foreach ( $this->ldap as &$v ) {
			if ( is_array( $v ) ) $v = new db\ldap( $v[0], $v[1], $v[2], $v[3], $v[4] );
			if ( ! $v ) continue;
			if ( $v->get( null, [ 'displayName', 'objectGUID', 'objectsid' ], [ 'objectClass' => 'user', 'objectCategory' => 'person', 'userPrincipalName' => $upn ] ) ) {
				$r = $v->fetch( $dn );
				$gsec = [ $r[2][0] => [ $r[2][1] ] ];
				$v->get( null, [ 'objectsid' ], [ 'objectClass' => 'group', 'objectCategory' => 'group', 'member:1.2.840.113556.1.4.1941:' => $dn ] );
				while ( $g = $v->fetch() ) {
					$gsec[ $g[0][0] ][] = $g[0][1];
				}
				return [ 'secret' => $r[1], 'name' => $r[0], 'gsec' => $gsec ];
			}
		}
		return false;
	}


}