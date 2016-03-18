<?php namespace davestewart\laravel\crud;

use ArrayObject;
use davestewart\laravel\crud\errors\InvalidPropertyException;
use Illuminate\Support\MessageBag;
use Input;
use Validator;

/**
 * Class CrudMeta
 *
 * @property string 	$class		    The namespaced class of the Model
 * @property string[] 	$views		    Property
 * @property mixed[]   	index	    	Property
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
		 * Paths to the view files that will render your model's data
		 *
		 * Defaults to the package's catch-all templates
		 *
		 * @var string[]
		 */
		protected $views =
		[
			'create'            => 'vendor.crud.create',
			'index'             => 'vendor.crud.index',
			'show'              => 'vendor.crud.show',
			'edit'              => 'vendor.crud.edit',
			'form'              => 'vendor.crud.partials.form',
			'fields'            => 'vendor.crud.partials.fields',
			'field'             => 'vendor.crud.partials.field',
			'actions'           => 'vendor.crud.partials.actions',
			'related'           => 'vendor.crud.partials.related',
		];

		/**
		 * Behaviour for index pages
		 *
		 * @var int
		 */
		protected $index =
		[
			'orderBy'           => 'id',
			'orderDir'          => 'desc',
			'perPage'           => 50,
		];

		/**
		 * The attribute to use to determine the title of the model
		 *
		 * For example, a User model may want to use the name attribute, but a
		 *
		 * @var string
		 */
		protected $titleAttr    = 'name';


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
			'invalid'           => 'The form has errors',
		];


	// -----------------------------------------------------------------------------------------------------------------
	// FORM

		/**
		 * Fields to show for each action:
		 *
		 * Should be an array of space-separated strings (or an array of arrays) of the form $action => $fields:
		 *
		 *  - 'index' => 'username email created_date'
		 *
		 * If you want to render any values that require some interim functionality, specify a getter function:
		 *
		 * - 'index' => 'posts:getPostCount'
		 *
		 * Callbacks should be available as a class method, of the format function($model, $action) and should return
		 * an associative array of $value => $label pairs. PHP's array_column() provides a neat way to do this!
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
		protected $labels = [ ];

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
		protected $controls = [ ];

		/**
		 * Controls for create action only
		 *
		 * Values will be merged with the original controls array
		 *
		 * @var null|array
		 */
		protected $controls_create = null;

		/**
		 * Controls for edit action only
		 *
		 * Values will be merged with the original controls array
		 *
		 * @var null|array
		 */
		protected $controls_edit = null;


	// -----------------------------------------------------------------------------------------------------------------
	// VALIDATION

		/**
		 * Validation rules
		 *
		 * Should be an array of the form $field => $rule:
		 *
		 * - 'email'        => 'required|email|unique:users',
		 *
		 * @var string[]
		 */
		protected $rules = [ ];

		protected $rules_store = null;

		protected $rules_update = null;

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


	// ------------------------------------------------------------------------------------------------
	// CACHED

		/**
		 * A cached version of all merged / combined controls arrays
		 *
		 * @var array
		 */
		protected $_controls    = null;

		/**
		 * A cached version of all merged / combined rules arrays
		 *
		 * @var array
		 */
		protected $_rules       = null;


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

			// expand fields with :placeholders for :all, :fillable, :hidden, etc
			foreach($this->fields as $key => $value)
			{
				$type = str_replace(':', '', $value);
				if(in_array($type, $types))
				{
					$values = count($fields[$type])
						? $fields[$type]
						: $fields['all'];
					$this->fields[$key] = implode(' ', $values);
				}
			}

			//pr($this->fields);

			// update hidden fields
			if(is_array($fields['hidden']))
			{
				$this->hidden = array_values(array_unique(array_merge($this->hidden, $fields['hidden'])));
			}
		}


	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		/**
		 * Gets a property on the CrudMeta instance
		 *
		 * @param $name
		 * @return mixed|null
		 * @throws InvalidPropertyException
		 */
		public function get($name)
		{
			if(strstr($name, '.') !== false)
			{
				$parts  = explode('.', $name);
				$prop   = array_shift($parts);
				$path   = implode('.', $parts);
				if(array_has($this->$prop, $path))
				{
					return array_get($this->$prop, $path);
				}
			}
			else if(property_exists($this, $name))
			{
				return $this->$name;
			}
			throw new InvalidPropertyException($name, get_called_class());
		}

		/**
		 * Sets or merges values into an array property on the CrudMeta instance
		 *
		 * @param $name
		 * @param $value
		 * @return $this
		 * @throws InvalidPropertyException
		 */
		public function set($name, $value)
		{
			if(strstr($name, '.') !== false)
			{
				$parts  = explode('.', $name);
				$prop   = array_shift($parts);
				$path   = implode('.', $parts);
				if(property_exists($this, $prop))
				{
					array_set($this->$prop, $path, $value);
				}
				else
				{
					throw new InvalidPropertyException($name, get_called_class());
				}
			}
			else if(property_exists($this, $name))
			{
				if(is_array($this->$name))
				{
					$this->$name = array_merge($this->$name, $value);
				}
				else
				{
					$this->$name = $value;
				}
			}
			else
			{
				throw new InvalidPropertyException($name, get_called_class());
			}
			return $this;
		}

		public function __get($name)
		{
			return $this->get($name);
		}

		public function __set($name, $value)
		{
			$this->set($name, $value);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// METHODS

		public function validate($input, $action = null)
		{
			return Validator::make($input, $this->getRules($action));
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
		 * Returns the title for the entity
		 *
		 * The method searches first for a `title` property on the model, then looks for a same-named property
		 * as the `titleAttr` property on the Meta
		 *
		 * @param   object  $model      The model to use to determine the title
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
		 * Returns the label for a field
		 *
		 * The method first checks the `labels` array, then defaults to the ucwords version of the field name
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
		 * Gets all form fields for a named view as an array of CrudField instances
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
			$fields     = new FieldList();

			// grab names
			if(is_string($names))
			{
				preg_match_all('/[:\.\w]+/', $names, $matches);
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
		 * Return a single field as a CrudField instance
		 *
		 * @param   string      $action     The resource action
		 * @param   string      $name       The name of the field to build
		 * @param   mixed       $data       The source data, such as a model
		 * @param   MessageBag  $errors     Any validation errors
		 * @return  CrudField
		 * @throws  \Exception
		 */
		public function getField($action, $name, $data, $errors = null)
		{
			/** @var CrudField */
			$field                  = \App::make('CrudField');


			// ------------------------------------------------------------------------------------------------
			// basic information

				// initial check to see if the field has a callback, i.e. "posts:getPostCount"
				if(strstr($name, ':') !== false)
				{
					$parts      = explode(':', $name);
					$name       = $parts[0];
					$callback   = $parts[1];
				}

				// field name & label
				$field->name            = $name;
				$field->label           = $this->getLabel($name);

				// if we have a callback, call it
				if(isset($callback) && method_exists($this, $callback))
				{
					$field->value   = function($model) use ($callback) { return $this->$callback($model); };
				}

				// if we're on the index route, there's no point assigning values as they will be resolved per-model in the for loop
				else if($action == 'index')
				{
					// do nothing
				}

				// otherwise, attempt to resolve a value, unless hidden
				else
				{
					$field->value   = ! in_array($name, $this->hidden)
										? $this->getProperty($data, $name)
										: null;
				}


			// ------------------------------------------------------------------------------------------------
			// controls, for create or edit routes only

				if($action === 'create' || $action === 'edit')
				{
					// control
					$control                = $this->getControlProps($name, $action);
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
					//$field->rules           = $this->getRule($name, $action);
					$field->view            = $this->views['field'];
				}

			// return
			return $field;
		}

		/**
		 * Gets the list of fields for a certain action
		 *
		 * Combines or returns rules from $rules_store and $rules_update
		 *
		 * @param null $action
		 * @return mixed|null|\string[]
		 */
		public function getRules($action = null)
		{
			// variables
			$method = 'rules_' . $action;
			$rules  = $this->rules;
			$extra  = property_exists($this, $method) ? $this->$method : null;

			// debug
			//pr($method, $rules, $extra);

			// merge
			if(is_array($extra))
			{
				if(count($rules))
				{
					$validator = Validator::make([], $rules);
					foreach($extra as $key => $value)
					{
						$validator->mergeRules($key, $value);
					}
					return $validator->getRules();
				}
				return $extra;
			}

			// return base
			return $rules;
		}

		/**
		 * Gets the
		 *
		 * @param      $name
		 * @param null $action
		 * @return string
		 */
		public function getRule($name, $action = null)
		{
			return $this->getRules($action)[$name];
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Gets the list of controls for a certain action
		 *
		 * @param null $action
		 * @return mixed|null|\string[]
		 */
		protected function getControls($action = null)
		{
			// return cached controls if they exist
			$controls = array_get($this->_controls, $action);
			if($controls)
			{
				return $controls;
			}

			// variables
			$method     = 'controls_' . $action;
			$controls   = $this->controls;
			$extra      = property_exists($this, $method) ? $this->$method : null;

			// merge or replace additional controls
			if(is_array($extra))
			{
				$controls = count($controls)
					? array_merge($controls, $extra)
					: $extra;
			}

			// cache results
			array_set($this->_controls, $action, $controls);

			// return results
			return $controls;
		}

		/**
		 * Gets control properties for an action/field
		 *
		 * @param   string  $name   The name of the control to find
		 * @param   string  $action The current action
		 * @return  object
		 */
		protected function getControlProps($name, $action)
		{
			// variables
			$controls   = $this->getControls($action);
			$data       = $controls[$name];

			// single control type
			if(strstr($data, ':') === FALSE)
			{
				return new ControlProps($data);
			}

			// compound control type
			else
			{
				// grab controls
				preg_match_all('/(\w+):(\w+)/', $data, $matches);
				$controls = array_combine($matches[1], $matches[2]);

				// single entry
				if(count($controls) === 1)
				{
					return new ControlProps($matches[1][0], $matches[2][0]);
				}
				else
				{
					return new ControlProps($controls[$action], $controls['options']);
				}
			}
		}

		/**
		 * Utility function to resolve object properties, including from dot.notation paths
		 *
		 * @param   mixed   $model  The model to get the property form
		 * @param   string  $prop   The property or path to resolve
		 * @return  mixed           The resolved value
		 */
		protected function getProperty($model, $prop)
		{
			if(is_string($prop))
			{
				if(strstr($prop, '.') === FALSE)
				{
					return $model->$prop;
				}
				$prop = explode('.', $prop);
			}
			return array_reduce($prop, function($obj, $name){ return $obj->$name; }, $model);
		}


}

class FieldList extends ArrayObject
{
	public function __get($name)
	{
		if(array_key_exists($name, $this))
		{
			return $this[$name];
		}
		throw new InvalidPropertyException($name, get_called_class());
	}
}

class ControlProps
{
	public $type;
	public $callback;

	public function __construct($type = null, $callback = null)
	{
		$this->type     = $type;
		$this->callback = $callback;
	}
}