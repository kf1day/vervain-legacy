<?php

/*
	ID => [
		Parent ( 1: '/' ),
		Path,
		Action: empty assumed folder
		Option
	]
*/

return [

	1 => [ 0, 'root', null, '*' ], // 0000root.php
	2 => [ 1, 'warehouse', null, '*' ],
	3 => [ 2, 'checkin', 'checkin', '*' ],
	4 => [ 2, 'checkout', 'checkout', '*' ],
	5 => [ 1, 'store', 'store', '*' ],
	6 => [ 5, 'admin', 'admin', '*' ],

];