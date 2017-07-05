<?php namespace model;

class acl extends core {
	
	protected $dba = null;
	protected $salt = '';
	protected $ldap = [];
	
	protected $name = '';
	protected $gsec = [];
	
	
	public function __construct( $dba, $salt ) {
		$this->dba = $dba;
		$this->salt = $salt;
	}
	
	public function add_ldap_server( $host, $port, $base, $user, $pass ) {
		$this->ldap[] = new db\ldap( $host, $port, $base, $user, $pass );
	}
	
	public function auth_gss() {
		$user = strtolower( $_SERVER['PHP_AUTH_USER'] ?? '' );
		$dn = '';
		foreach ( $this->ldap as $v ) {
			if ( $v->get( null, [ 'displayName', 'objectGUID', 'objectsid' ], [ 'objectClass' => 'user', 'objectCategory' => 'person', 'userPrincipalName' => $user ] ) ) {
				$r = $v->fetch( $dn );
				$this->name = $r[0];
				$this->gsec = [ $r[2][0] => [ $r[2][1] ] ];
				$hash = $this->keyring( $r[1] );
				$uid = $this->usercode( $user );
				
				$v->get( null, [ 'objectsid' ], [ 'objectClass' => 'group', 'objectCategory' => 'group', 'member:1.2.840.113556.1.4.1941:' => $dn ] );
				while ( $g = $v->fetch() ) {
					$this->gsec[ $g[0][0] ][] = $g[0][1];
				}
				$this->dba->set_auth( $hash, $uid, $this->name, bin2hex( json_encode( $this->gsec ) ) );
				return [ 'uid' => $uid, 'hash' => $hash ];
			}
		}
		return false;
	}
	
	protected function keyring( $secret, $compare = null ) {
		$hash = hash( 'sha256', $this->salt.$secret.$_SERVER['REMOTE_ADDR'] );
		if ( $compare ) {
			return ( $hash == $compare );
		} else {
			return $hash;
		}
	}
	
	protected function usercode( $username, $decode = false ) {
		if ( $decode ) {
			return @hex2bin( $username ) ?? false;
		} else {
			return bin2hex( $username );
		}
	}

	protected function userbin( $username, $decode = false ) {
		if ( $decode ) {
			$username = preg_replace( '/[^0-9a-f]/', '', $username );
			return pack( 'H*', $username );
	//		return preg_replace( '/[^A-Za-z0-9@]/', '', $username );
		} else {
			return unpack( 'H*', strtolower( $username ) )[1];
		}
	}

}