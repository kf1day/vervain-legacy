<?php namespace model\acl;


class rHelper {
	public $name = '';
	public $secret = '';
	public $groups = [];
}

interface iHelper {

	public function get_user( string $uid ): rHelper; // [ name, secret, groups ]

}
