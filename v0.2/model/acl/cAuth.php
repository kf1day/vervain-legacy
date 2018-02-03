<?php namespace model\acl;

class cAuth extends \app\cModel {

	protected $cw = null;		// cacher
	protected $pt = null;		// array of helpers: instanceof iHelper
	protected $ui = [];			// user info



	public function __construct( $cacher, iHelper $helper ) {
		$this->cw = $cacher;
		$this->pt = $helper;
	}

	public function auth_server( bool $ignore_cache = false ) {
		if ( $u = $_SERVER['PHP_AUTH_USER'] ?? false ) {
			$u = strtolower( $u );
			$uid = $this->usrcode( $u );
			if ( ! $ignore_cache && $t = $this->cache['acl'][$uid] ?? false ) {
				$this->ui = [ 'uid' => $uid, 'name' => $t[0], 'secret' => $t[1], 'groups' => $t[2] ];
				return;
			} elseif ( $t = $this->pt->acl_get( $u ) ) {
				$t = $this->pt->acl_get( $u );
				$this->ui = [ 'uid' => $uid, 'name' => $t->name, 'secret' => $t->secret, 'groups' => $t->groups ];
				$this->cache['acl'][$uid] = [ $t->name, $t->secret, $t->groups ];
				return;
			}
			throw new \EHttpClient( 403 );
		}
		throw new Exception( 'Failed to fetch authorized user' );
	}

	public function auth_cookie( bool $ignore_cache = false ) {
		if ( $uid = $_COOKIE['UID'] ?? false and $hash = $_COOKIE['HASH'] ?? false ) {
			if ( ! $ignore_cache && $t = $this->cache['acl'][$uid] ?? false and $this->keyring( $t[1], $hash ) ) { // have cache + cache is valid
				$this->ui = [ 'uid' => $uid, 'name' => $t[0], 'secret' => $t[1], 'groups' => $t[2] ];
				return;
			} elseif( $u = $this->usrcode( $uid, 1 ) and $t = $this->pt->acl_get( $u ) and $this->keyring( $t->secret, $hash ) ) {// search ldap
				$u = $this->usrcode( $uid, 1 );
				$this->ui = [ 'uid' => $uid, 'name' => $t->name, 'secret' => $t->secret, 'groups' => $t->groups ];
				$this->cache['acl'][$uid] = [ $t->name, $t->secret, $t->groups ];
				return;
			}
		}
		throw new \EHttpClient( 403 );
	}

	public function get() {
		$t = func_get_args();
		$fff = [];
		foreach( $t as $v ) {
			if ( isset( $this->ui[$v] ) ) $fff[$v] = $this->ui[$v];
		}
		if ( func_num_args() === 1 ) $fff = empty( $fff ) ? null : reset( $fff );
		return $fff;
	}

	public function get_token() {
		return [ $this->ui['uid'], $this->keyring( $this->ui['secret'] ) ];
	}

	protected function keyring( $secret, $compare = null ) {
		$hash = hash( 'sha256', APP_HASH . $secret . $_SERVER['REMOTE_ADDR'] );
		if ( $compare !== null ) {
			return ( $hash === $compare );
		} else {
			return $hash;
		}
	}

	protected function usrcode( $uid, $decode = false ) {
		if ( $decode ) {
			return @hex2bin( $uid ) ?? false;
		} else {
			return bin2hex( $uid );
		}
	}

}
