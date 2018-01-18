<?php namespace model\acl;

class helper_msad extends \model\db\ldap implements _helper {

	public function acl_get( string $uid ) {
		$filter = [
			'objectClass' => 'user',
			'objectCategory' => 'person',
			'userPrincipalName' => $uid,
		];
		if ( $this->select( '', [ 'dn', 'displayName', 'objectGUID' ], $filter ) !== 1 ) return false;
		$t = $this->fetch();

		$this->select( '', [ 'dn' ], [ 'objectClass' => 'group', 'objectCategory' => 'group', 'member:1.2.840.113556.1.4.1941:' => $t[0] ] );

		$groups = [];
		while ( $r = $this->fetch() ) {
			$groups[] = $r[0];
		}


		return [ $t[1], $t[2], $groups ];

	}
}
