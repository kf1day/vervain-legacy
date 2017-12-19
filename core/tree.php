<?php

class tree {

	private $pt = [ 0 => [ 'parent' => null, 'child' => [], 'data' => null ] ];
						// parent id
						// array of child id'
						// mixed data

	public function add( $id, $rel, $data ) {
		if ( empty( $this->pt[$rel] ) ) $rel = 0;
		$this->pt[$id] = [ 'parent' => $rel, 'child' => [], 'data' => $data ];
		$this->pt[$rel]['child'][$id] = $id;
	}

	public function dive( $id ) {
		if ( empty( $this->pt[$id] ) || count( $this->pt[$id]['child'] ) == 0 ) return false;
		$fff = [];
		foreach ( $this->pt[$id]['child'] as $k => $v ) {
			$fff[$k] = $this->pt[$v]['data'];
		}
		return $fff;
	}

	public function first_child( &$id ) {
		if ( empty( $this->pt[$id] ) || count( $this->pt[$id]['child'] ) == 0 ) return false;
		$id = reset( $this->pt[$id]['child'] );
		return $this->pt[$id]['data'];
	}

	public function pop( &$id ) {
		if ( $id == 0 || empty( $this->pt[$id] ) ) return false;
		$ido = $id;
		$id = $this->pt[$id]['parent'];
		return $this->pt[$ido]['data'];
	}
}
