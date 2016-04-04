<?php namespace davestewart\resourcery\services;

use davestewart\resourcery\CrudField;
use davestewart\resourcery\CrudMeta;
use davestewart\resourcery\errors\InvalidPropertyException;
use ArrayObject;
use Illuminate\Support\MessageBag;
use Input;
use Validator;

/**
 * Class CrudMetaService
 *
 * Responsible for turning Meta information into fields and controls
 *
 * @package davestewart\resourcery\services
 */
class CrudMetaService
{

	// ------------------------------------------------------------------------------------------------
	// CACHED

		/**
		 * @var CrudMeta
		 */
		protected $meta;

		/**
		 * A cached version of all merged / combined controls arrays
		 *
		 * @var array
		 */
		protected $controls    = null;

		/**
		 * A cached version of all merged / combined rules arrays
		 *
		 * @var array
		 */
		protected $rules       = null;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct()
		{
			// nothing to do here
		}

		/**
		 * Initialize the Meta object
		 *
		 * @param CrudMeta $meta
		 * @param          $fields
		 * @return $this
		 */
		public function initialize(CrudMeta $meta, $fields = [])
		{
			// meta
			$this->meta = $meta;

			// variables
			$types = array_keys($fields);

			// expand fields with :placeholders for :all, :fillable, :hidden, etc
			foreach($meta->fields as $key => $value)
			{
				$type = str_replace(':', '', $value);
				if(in_array($type, $types))
				{
					$values = count($fields[$type])
						? $fields[$type]
						: $fields['all'];
					$meta->set("fields.$key", implode(' ', $values));
				}
			}

			//pr($this->fields);

			// update hidden fields
			if(is_array($fields['hidden']))
			{
				$meta->hidden = array_values(array_unique(array_merge($meta->hidden, $fields['hidden'])));
			}

			// return
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// GETTERS

		public function getMeta()
		{
			return $this->meta;
		}

		/**
		 * Gets any related tables used in the $fields attribute for the specified action
		 *
		 * @param   string  $action     The action to get related tables for
		 * @return  array               An array of related tables
		 */
		public function getRelated($action)
		{
			preg_match_all('/(\w+)\.(?:\w+)/', $this->meta->fields[$action], $matches);
			return $matches ? $matches[1] : null;
		}

		/**
		 * Returns the view path for the specified action
		 *
		 * @param   string  $action     The action to get view for
		 * @return  string              The view path
		 */
		public function getView($action)
		{
			return $this->meta->views[$action];
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
			$attr = $this->meta->titleAttr ?: 'title';
			return $model
				? property_exists($model, $attr)
					? $model->$attr
					: null
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
			return array_key_exists($name, $this->meta->labels)
				? $this->meta->labels[$name]
				: trim(ucwords(preg_replace('/[_\W]+/', ' ', $name)));
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
			$names      = $this->meta->fields[$action];
			$fields     = new FieldList();

			// grab names
			if(is_string($names))
			{
				preg_match_all('/\S+/', $names, $matches);
				$names = $matches[0];
			}

			// loop over fields and build meta
			foreach( (array) $names as $index => $name)
			{
				$field = $this->getField($action, $name, $data, $errors);
				$fields[$field->id] = $field;
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
				$field->id              = preg_replace('/^_+|_+$/', '', preg_replace('/\W+/', '_', $name));
				$field->name            = $name;
				$field->label           = $this->getLabel($name);

				// if we have a callback, call it
				if(isset($callback) && method_exists($this->meta, $callback))
				{
					$field->value   = function($model) use ($callback) { return $this->meta->$callback($model); };
				}

				// if we're on the index route, there's no point assigning values as they will be resolved per-model in the for loop
				else if($action == 'index')
				{
					// do nothing
				}

				// otherwise, attempt to resolve a value, unless hidden
				else
				{
					$field->value   = ! in_array($name, $this->meta->hidden)
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
						if( ! method_exists($this->meta, $control->callback) )
						{
							throw new \Exception("Callback '{$control->callback}' does not exist on " . get_class($this->meta));
						}
						$field->options     = $this->meta->{$control->callback}($data);
					}

					// errors
					$errorName = str_replace(']', '', str_replace('[', '.', $name));
					if($errors && $errors->has($errorName))
					{
						$field->error       = $errors->first($errorName);
					}

					// old values
					$old                    = Input::old($name);
					$field->old             = $old ? $old : $field->value;

					// attributes
					$field->rules           = $this->getRule($name, $action);
					$field->view            = $this->meta->views['field'];
				}

			// return
			return $field;
		}

		/**
		 * Gets the list of fields for a certain action
		 *
		 * Combines or returns rules from $rules_store and $rules_update
		 * Also injects the id where the unique:table,column is found
		 *
		 * @param null $action
		 * @param null $id
		 * @return mixed|null|\string[]
		 */
		public function getRules($action, $id = null)
		{
			// variables
			$method = 'rules_' . $action;
			$rules  = $this->meta->rules;
			$extra  = property_exists($this->meta, $method)
						? $this->meta->$method
						: null;

			// debug
			//pr($method, $rules, $extra);

			// merge
			if(is_array($extra))
			{
				if(count($rules))
				{
					/** @var \Illuminate\Validation\Validator $validator */
					$validator = Validator::make([], $rules);
					foreach($extra as $key => $value)
					{
						$validator->mergeRules($key, $value);
					}
					return $validator->getRules();
				}
				return $extra;
			}

			// update uniques
			if($action == 'update' && $id !== null)
			{
				foreach($rules as $key => $value)
				{
					$rules[$key] = preg_replace('/unique:\w+,\w+/', "\$0,$id", $value);
				}
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
			$rules = $this->getRules($action);
			return isset($rules[$name]) ? $rules[$name] : null;
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
			$controls = array_get($this->controls, $action);
			if($controls)
			{
				return $controls;
			}

			// variables
			$method     = 'controls_' . $action;
			$controls   = $this->meta->controls;
			$extra      = property_exists($this->meta, $method) ? $this->meta->$method : null;

			// merge or replace additional controls
			if(is_array($extra))
			{
				$controls = count($controls)
					? array_merge($controls, $extra)
					: $extra;
			}

			// cache results
			array_set($this->controls, $action, $controls);

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
			$data       = isset($controls[$name]) ? $controls[$name] : 'text';

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
					$options = isset($controls['options']) ? $controls['options'] : null;
					return new ControlProps($controls[$action], $options);
				}
			}
		}

		/**
		 * Utility function to resolve object properties, including from dot.notation paths
		 *
		 * @param   mixed  $model The model to get the property form
		 * @param   string $prop  The property or path to resolve
		 * @return mixed The resolved value
		 * @throws InvalidPropertyException
		 */
		protected function getProperty($model, $prop)
		{
			if($prop == null)
			{
				throw new InvalidPropertyException($prop, $model);
			}

			if(is_string($prop))
			{
				// array[property]
				if(strstr($prop, '[') !== FALSE)
				{
					preg_match_all('/[\w_]+/', $prop, $matches);
					$prop = $matches[0];
				}

				// related.property
				else if(strstr($prop, '.') !== FALSE)
				{
					$prop = explode('.', $prop);
				}

				// normal property
				else
				{
					return $model->$prop;
				}
			}

			return array_reduce($prop, function($obj, $name){ return isset($obj->$name) ? $obj->$name : null; }, $model);
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