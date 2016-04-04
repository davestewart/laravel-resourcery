<?php

return [

	// human-readable method names
	'action' =>
	[
		'index'         => 'index',
		'show'          => 'show',
		'view'          => 'view',
		'create'        => 'create',
		'edit'          => 'edit',
		'store'         => 'store',
		'save'          => 'save',
		'update'        => 'create',
		'delete'        => 'delete',
		'destroy'       => 'destroy',
	],

	// page titles
	'title' =>
	[
		'index'         => 'View :plural',
		'show'          => 'View :singular',
		'create'        => 'Create new :singular',
		'update'        => 'Edit :singular',
		'search'        => 'Search :plural',
		'results'       => 'Search results',
		'no_results'    => 'No results',
	],

	// additional labels
	'label' =>
	[
		'actions'        => 'Actions',
		'relations'      => 'Relations',
	],

	// buttons prompts
	'prompt' =>
	[
		// links / labels
		'show'          => 'View :singular',
		'create'        => 'Create new :singular',
		'update'        => 'Edit :singular',
		'destroy'       => 'Delete :singular',

		// controls
		'submit'        => 'Submit',
		'cancel'        => 'Cancel',
		'back'          => 'Back',
		'select'        => 'Select',
		'choose'        => 'Choose',
	],
    
	// confirmation prompts
    'confirm' =>
    [
		'delete'        => 'Are you sure you want to delete this :singular?',
		'cancel'        => 'Are you sure you want to cancel?',
		'back'          => 'Are you sure you want to want to go back?',
    ],

	// status / flash messages
	'status' =>
	[
		'created'       => 'Successfully created :singular',
		'updated'       => 'Successfully updated :singular',
		'deleted'       => 'Successfully deleted :singular',
		'not_created'   => 'Unable to create :singular',
		'not_updated'   => 'Unable to update :singular',
		'not_deleted'   => 'Unable to delete :singular',
		'invalid'       => 'The form has errors',
		'error'         => 'There was an error',
	],

];