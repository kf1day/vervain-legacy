<?php namespace model\db;

interface _sql {

	public function get( string $table, array $fields, $filter = null, $sort = null );
	public function put( string $table, array $fields );
	public function upd( string $table, array $fields, array $filter );
	public function del( string $table, array $filter );
	public function select( string $table, array $fields, $filter = null, $sort = null );
	public function fetch();
	public function fetch_all();

}