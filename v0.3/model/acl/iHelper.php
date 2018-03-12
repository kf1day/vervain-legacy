<?php namespace model\acl;


class rHelper {
	public $name = '';
	public $secret = '';
	public $groups = [];
}

interface iHelper {

	public function acl_get( string $uid ): rHelper; // [ name, secret, groups ]

}
