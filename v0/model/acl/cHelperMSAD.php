<?php namespace model\acl;

class cHelperMSAD implements iHelper {

	protected $pt = null;
	protected $args = [];


	public function __construct( $host, $port, $base, $user, $pass ) {
		$this->args = [ $host, $port, $base, $user, $pass ];
	}

	public function get_user( string $uid ): rHelper {
		if ( $this->pt === null ) $this->pt = new \model\db\cLDAP( $this->args[0], $this->args[1], $this->args[2], $this->args[3], $this->args[4] );
		$filter = [
			'objectClass' => 'user',
			'objectCategory' => 'person',
			'userPrincipalName' => $uid,
		];
		if ( $this->pt->select( '', [ 'dn', 'displayName', 'objectGUID' ], $filter ) !== 1 ) return false;
		$t = $this->pt->fetch();

		$this->pt->select( '', [ 'dn', 'objectGUID', 'sAMAccountName' ], [ 'objectClass' => 'group', 'objectCategory' => 'group', 'member:1.2.840.113556.1.4.1941:' => $t[0] ] );

		$groups = [];
		while ( $r = $this->pt->fetch() ) {
			$groups[] = $r;
		}

		$ret = new rHelper;
		list( $ret->name, $ret->secret, $ret->groups ) = [ $t[1], $t[2], $groups ];
		return $ret;

	}
}
