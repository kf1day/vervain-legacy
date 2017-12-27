<?php

class tree {

	private $pt = [ 0 => [ 0, false, [], false ] ];
						// 0: id
						// 1: pointer to parent
						// 2: array pointer to child
						// 3: mixed data

	public function add( $id, $rel, $node ) {
		if ( !isset( $this->pt[$rel] ) ) $rel = 0;
		$this->pt[$id] = [ $id, &$this->pt[$rel], [], $node ];
		$this->pt[$rel][2][] = &$this->pt[$id];
	}

	public function dive( $id ) {
		if ( !isset( $this->pt[$id] ) ) return false;
		$fff = false;
		foreach ( $this->pt[$id][2] as $v ) {
			$fff[$v[0]] = $v[3];
		}
		return $fff;
	}

	public function firstchild( &$id ) {
		if ( !isset( $this->pt[$id] ) || !isset( $this->pt[$id][2][0] ) ) return false;
		$id = $this->pt[$id][2][0][0]; // some node -> array children -> first node -> id
		return $this->pt[$id][3];
	}

	public function pop( &$id ) {
		if ( $id == 0 || !isset( $this->pt[$id] ) ) return false;
		$ido = $id;
		$id = $this->pt[$id][1][0]; // some node -> parent -> id
		return $this->pt[$ido][3];
	}
}
