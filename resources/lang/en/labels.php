<?php

return [

	// ------------------------------------------------------------------------------------------------
	// Default values - these are merged into all resource specific values

		':defaults' =>
		[
			'id'            => 'id',
			'name'          => 'Name',
		],


    // ------------------------------------------------------------------------------------------------
    // Resource-specific values - set these per resource to override 

        // this should be the singular :item name
		'thing' =>
		[
			// naming values
			':item'         => 'thing',
			':items'        => 'things',

			// label replacements
			'name'          => 'Thing Name',
			'email'         => 'Thing Email',
			'age'           => 'Thing Age',

			// placeholder values
		    ':placeholder' =>
		    [
			    'name'      => 'Enter your full name',
		    ],

		    // help values
		    ':help' =>
		    [
			    'name'      => 'Enter your first and last names only'
		    ]
		],

];