<?php

/**
 * Note all messages which are retrieved by the main LangService methods can be converted to "Sentence case" by passing
 * the key value with capital first letter:
 *
 *  - $lang->action->view
 *  - $lang->action->View
 *
 * This allows the storage of words as lowercase, to be used as variables, or transformed to be used
 * as prompts or labels
 */
return [

	// page titles
	'title' =>
	[
		'index'         => 'View :Items',
		'show'          => 'View :item',
		'create'        => 'Create :Item',
		'edit'          => 'Edit :Item',
		'search'        => 'Search :Items',
		'results'       => ':Item search results',
		'no_results'    => 'No results',
	],

	// human-readable prompts
	'prompt' =>
	[
		'all'           => 'View all :Items',
		'show'          => 'View :Item',
		'create'        => 'Create :Item',
		'edit'          => 'Edit :Item',
		'delete'        => 'Delete :Item',
	],

	// imperative actions
    'action' =>
	[
		// button
		'create'        => 'Create',
		'view'          => 'View',
		'edit'          => 'Edit',
		'delete'        => 'Delete',

		// form
		'submit'        => 'Submit',
		'cancel'        => 'Cancel',
		'back'          => 'Back',
		'restart'       => 'Restart',

		// ui element
		'select'        => 'Select',
		'choose'        => 'Choose',
	],
    
	// confirmation prompts
    'confirm' =>
    [
		'delete'        => 'Are you sure you want to delete this :item?',
		'deletes'       => 'Are you sure you want to delete these :items?',
		'cancel'        => 'Are you sure you want to cancel?',
		'back'          => 'Are you sure you want to go back?',
		'restart'       => 'Are you sure you want to start again?',
    ],

	// status / flash messages
	'status' =>
	[
		'created'       => 'Successfully created :item',
		'updated'       => 'Successfully updated :item',
		'deleted'       => 'Successfully deleted :item',
		'not_created'   => 'Unable to create :item',
		'not_updated'   => 'Unable to update :item',
		'not_deleted'   => 'Unable to delete :item',
		'invalid'       => 'The form has errors',
		'error'         => 'There was an error',
	],

	// miscellaneous text
	'text' =>
	[
		'field'          => 'Field',
		'value'          => 'Value',
		'actions'        => 'Actions',
		'relations'      => 'Relations',
	],

];