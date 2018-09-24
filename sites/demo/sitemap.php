<?php

// [action][@method][/arg1][/arg2]...[/argN]

return [ '', null, [
	[ 'mercury', 'class_mercury' ],
	[ 'venus', 'class_venus@overview' ],
	[ 'earth', 'class_earth', [
		[ 'countries', 'class_countries', [
			[ '*/flag', '@flag/small' ],
			[ '*/leader', '' ],
			[ 'list', '@list' ],
		]],
	]],
	[ 'mars', 'class_mars/missions' ],
]];
