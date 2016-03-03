<?php namespace davestewart\laravel\crud;

use davestewart\laravel\crud\CrudField;

use Illuminate\Support\MessageBag;
use Input;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Validator;

/**
 * Class CrudMeta
 *
 * @property string 	$class		    The namespaced class of the Model
 * @property string[] 	$views		    Property
 * @property int    	$pagination		Property
 * @property string   	$singular		Property
 * @property string 	$plural		    Property
 * @property string 	$titleAttr		Property
 * @property string[] 	$fields		    Property
 * @property string[] 	$controls		Property
 * @property string[] 	$labels		    Property
 * @property string[] 	$messages		Property
 * @property string[] 	$rules		    Property
 * @property string[]   $hidden         Property
 *
 */
class CrudMeta
{

	// -----------------------------------------------------------------------------------------------------------------
	// DATA

		/**
		 * The namespaced class of the Model
		 *
		 * @var string
		 */
		protected $class        = '\App\Models\Item';

		/**
		 * Paths to the view files that will render your model's data (defaults to
		 * the package's catch-all templates)
		 *
		 * @var string[]
		 */
		protected $views =
		[
			'create'			=> 'vendor.crud.create',
			'index'				=> 'vendor.crud.index',
			'show'				=> 'vendor.crud.show',
			'edit'				=> 'vendor.crud.edit',
			'form'				=> 'vendor.crud.form',
			'fields'			=> 'vendor.crud.fields',
		];

		/**
		 * The pagination limit for index pages
		 *
		 * @var int
		 */
		protected $pagination   = 50;


	// -----------------------------------------------------------------------------------------------------------------
	// LANGUAGE

		/**
		 * The singular name of the model
		 *
		 * @var string
		 */
		protected $singular     = 'item';

		/**
		 * The plural name of the model
		 *
		 * @var string
		 */
		protected $plural       = 'items';

		/**
		 * The attribute to use to determine the title of the model
		 *
		 * For example, a User model may want to use the name attribute, but a
		 *
		 * @var string
		 */
		protected $titleAttr    = 'name';


	// -----------------------------------------------------------------------------------------------------------------
	// FORM

		/**
		 * Fields to show for each action:
		 *
		 * Should be an array of space-separated strings (or an array of arrays) of the form $action => $fields:
		 *
		 *  - 'index' => 'username email created_date'
		 *
		 * You may also use the following placeholders to mirror your model's setup:
		 *
		 * - :all           All fields
		 * - :visible       All visible fields
		 * - :fillable      All fillable fields
		 *
		 * @var string[]
		 */
		protected $fields =
		[
			'index'			    => ':visible',
			'create'		    => ':fillable',
			'edit'			    => ':fillable',
			'show'			    => ':visible',
		];

		/**
		 * Preferred controls for fields
		 *
		 * Should be an array of the form $field => $type:
		 *
		 * - 'username' => 'text'
		 * - 'clientId' => 'email'
		 *
		 * Use any available types that your view or view library can render
		 *
		 * If a control (such as a select) requires additional options to render, specify a getter function:
		 *
		 * - 'clientId' => 'select:getClientIds'
		 *
		 * To specify different control types for different views, prefix the control type with the action:
		 *
		 * - 'clientId' => 'create:select edit:radios'
		 *
		 * To include control options, use the special value 'options:callback':
		 *
		 * - 'clientId' => 'create:select edit:radios options:getClientIds'
		 *
		 * Callbacks should be available as a class method, of the format function($model, $action) and should return
		 * an associative array of $value => $label pairs. PHP's array_column() provides a neat way to do this!
		 *
		 * At some point the following syntax to call a getter on the related model may be supported:
		 *
		 * - 'clientId' => 'dropdown:client[id/name]'
		 *
		 * @var string[]
		 */
		protected $controls =
		[

		];

		/**
		 * Optional array of labels for fields
		 *
		 * Should be an array of the form $field => $name:
		 *
		 *  - 'name'        => 'User Name'
		 *  - 'clientId'    => 'Client'
		 *
		 * If not supplied, labels default to the $field name
		 *
		 * @var string[]
		 */
		protected $labels =
		[

		];


	// -----------------------------------------------------------------------------------------------------------------
	// VALIDATION

		/**
		 * Confirmation messages for actions
		 *
		 * Use the placeholder :item to include the item's $singular property
		 *
		 * @var string[]
		 */
		protected $messages =
		[
			'created'           => 'Successfully created :item',
			'updated'           => 'Successfully updated :item',
			'deleted'           => 'Successfully deleted :item',
			'invalid'           => 'Validation failed',
		];

		/**
		 * Validation rules
		 *
		 * Should be an array of the form $field => $rule:
		 *
		 * - 'email'        => 'required|email|unique:users',
		 *
		 * @var string[]
		 */
		protected $rules =
		[

		];

		/**
		 * Fields to keep secret when repopulating the form
		 *
		 * @var string[]
		 */
		protected $hidden =
		[
			'password',
			'password_confirm'
		];

	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct($class = null)
		{
			if($class)
			{
				$this->class = $class;
			}
		}

		public function initialize($fields)
		{
			// variables
			$types = array_keys($fields);

			// expand fields with :placeholders
			foreach($this->fields as $key => $value)
			{
				$type = substr($value, 1);
				if(in_array($type, $types))
				{
					$values = count($fields[$type])
						? $fields[$type]
						: $fields['all'];
					$this->fields[$key] = implode(' ', $values);
				}
			}

			// update hidden fields
			$this->hidden = array_values(array_unique(array_merge($this->hidden, $fields['hidden'])));
		}


	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		public function __get($name)
		{
			if(property_exists($this, $name))
			{
				return $this->$name;
			}
			throw new InvalidParameterException("Property $name does not exist");
		}

		public function __set($name, $value)
		{
			if( ! property_exists($this, $name) )
			{
				throw new InvalidParameterException("Property $name does not exist");
			}
			$this->$name = $value;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// METHODS

		public function validate($input)
		{
			return Validator::make($input, $this->rules);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// GETTERS

		/**
		 * Gets any related tables used in the $fields attribute for the specified action
		 *
		 * @param   string  $action     The action to get related tables for
		 * @return  array               An array of related tables
		 */
		public function getRelated($action)
		{
			preg_match_all('/(\w+)\.(?:\w+)/', $this->fields[$action], $matches);
			return $matches ? $matches[1] : null;
		}

		/**
		 * Gets confirmation messages according to the task type
		 *
		 * @param   string  $type       The type of message to return; must be in the $messages attribute
		 * @return  string              The populated message
		 */
		public function getMessage($type)
		{
			return str_replace(':item', $this->singular, $this->messages[$type]);
		}

		/**
		 * Returns the view path for the specified action
		 *
		 * @param   string  $action     The action to get view for
		 * @return  string              The view path
		 */
		public function getView($action)
		{
			return $this->views[$action];
		}


		/**
		 * Returns the view path for the specified action
		 *
		 * @param   object  $model      The action to get view for
		 * @return  string              The view path
		 */
		public function getTitle($model)
		{
			return $model
				? property_exists($model, 'title')
					? $model->title
					: $model->{$this->titleAttr}
				: null;
		}

		/**
		 * Returns the view path for the specified action
		 *
		 * @param   string  $name       The action to get view for
		 * @return  string              The view path
		 */
		public function getLabel($name)
		{
			return array_key_exists($name, $this->labels)
				? $this->labels[$name]
				: ucwords(str_replace('_', ' ', $name));
		}

		/**
		 * Gets all form fields as an array of CrudField elements
		 *
		 * @param   string      $action     The resource action
		 * @param   mixed       $data       The model
		 * @param   MessageBag  $errors     Any validation errors
		 * @return  array
		 */
		public function getFields($action, $data = null, $errors = null)
		{
			// get fields
			$names      = $this->fields[$action];
			$fields     = [];

			// grab names
			if(is_string($names))
			{
				preg_match_all('/[\.\w]+/', $names, $matches);
				$names = $matches[0];
			}

			// loop over fields and build meta
			foreach( (array) $names as $index => $name)
			{
				$fields[$name] = $this->getField($action, $name, $data, $errors);
			}

			// return
			return $fields;
		}

	/**
	 * Gets all form fields as an array of CrudField elements
	 *
	 * @param   string      $action     The resource action
	 * @param   string      $name       The name of the field to build
	 * @param   mixed       $data       The model
	 * @param   MessageBag  $errors     Any validation errors
	 * @return  CrudField
	 * @throws  \Exception
	 */
	public function getField($action, $name, $data, $errors = null)
		{
			/** @var CrudField */
			$field                  = \App::make('CrudField');

			// meta
			$field->name            = $name;
			$field->label           = $this->getLabel($name);

			// value
			if($action !== 'index')
			{
				$field->value       = in_array($name, $this->hidden)
										? ''
										: $this->getProperty($data, $name);
			}

			// control
			if($action === 'create' || $action === 'edit')
			{
				// control
				$control                = $this->getControl($action, $name);
				$field->type            = $control->type;
				if($control->callback)
				{
					if( ! method_exists($this, $control->callback) )
					{
						throw new \Exception("Callback '{$control->callback}' does not exist on " . __CLASS__);
					}
					$field->options     = $this->{$control->callback}($data);
				}

				// errors
				if($errors && $errors->has($name))
				{
					$field->error       = $errors->first($name);
				}

				// old values
				$old                    = Input::old($name);
				$field->old             = $old ? $old : $field->value;

				// attributes
				$field->rules           = $this->rules[$name];
			}

			// return
			return $field;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Gets control properties for an action/field
		 *
		 * @param   string    $action   The current action
		 * @param   string    $name     The name of the control to find
		 *
		 * @return  object
		 */
		protected function getControl($action, $name)
		{
			// variables
			$control    = (object) [];
			$input      = $this->controls[$name];

			// single control type
			if(strstr($input, ':') === FALSE)
			{
				$control->type = $input;
			}

			// compound control type
			else
			{
				// grab controls
				preg_match_all('/(\w+):(\w+)/', $input, $matches);
				$controls       = array_combine($matches[1], $matches[2]);

				// single entry
				if(count($controls) === 1)
				{
					$type       = $matches[1][0];
					$callback   = $matches[2][0];
				}
				else
				{
					$type       = $controls[$action];
					$callback   = $controls['options'];
				}

				// assign
				$control->type    = $type;
				$control->callback= $callback;
			}

			// return
			return $control;
		}

		protected function getProperty($model, $path)
		{
			if(is_string($path))
			{
				if(strstr($path, '.') === FALSE)
				{
					return $model->$path;
				}
				$path = explode('.', $path);
			}
			return array_reduce($path, function($obj, $name){ return $obj->$name; }, $model);
		}


}