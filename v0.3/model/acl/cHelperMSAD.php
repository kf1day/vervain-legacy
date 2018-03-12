<?php namespace model\acl;

class cHelperMSAD extends \model\db\cLDAP implements iHelper {

	public function acl_get( string $uid ): rHelper {
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

		$ret = new rHelper;
		list( $ret->name, $ret->secret, $ret->groups ) = [ $t[1], $t[2], $groups ];
		return $ret;

	}
}
