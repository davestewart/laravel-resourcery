<?php namespace davestewart\resourcery\services;

use davestewart\resourcery\classes\forms\Field;
use davestewart\resourcery\classes\meta\FieldMeta;
use davestewart\resourcery\classes\meta\ControlMeta;
use davestewart\resourcery\classes\meta\ResourceMeta;
use davestewart\resourcery\classes\exceptions\InvalidPropertyException;
use ArrayObject;
use Illuminate\Support\MessageBag;
use Input;
use Validator;

/**
 * Class MetaService
 *
 * Responsible for turning ResourceMeta information into Fields and Controls
 */
class MetaService
{

	// ------------------------------------------------------------------------------------------------
	// CACHED

		/**
		 * @var ResourceMeta
		 */
		protected $meta;

		/**
		 * @var LangService
		 */
		protected $lang;

		/**
		 * A cached version of all expanded field properties
		 *
		 * @var array
		 */
		protected $fields;

		/**
		 * A cached version of all merged / combined controls arrays
		 *
		 * @var array
		 */
		protected $controls;

		/**
		 * A cached version of all merged / combined rules arrays
		 *
		 * @var array
		 */
		protected $rules;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct()
		{
			// nothing to do here
		}

		/**
		 * Initialize the Meta object
		 *
		 * @param   ResourceMeta    $meta
		 * @param   LangService     $lang
		 * @param   array           $fields
		 * @return  self
		 */
		public function initialize(ResourceMeta $meta, $lang, $fields = [])
		{
			// meta
			$this->meta = $meta;
			$this->lang = $lang;

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
		 * @return  array               An array of related tables
		 */
		public function getRelated()
		{
			$related = $this->meta->related;
			return count($related) ? $related : null;
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
		 * Returns the label for a field
		 *
		 * The method first checks the `labels` array, then defaults to the ucwords version of the field name
		 *
		 * @param   string  $name       The action to get view for
		 * @return  string              The view path
		 */
		public function getLabel($name)
		{
			// get default label
			$label = array_key_exists($name, $this->meta->labels)
				? $this->meta->labels[$name]
				: trim(ucwords(preg_replace('/[_\W]+/', ' ', $name)));

			// get translation
			return $this->lang->label($name, $label);
		}

		/**
		 * Converts the fields list shorthand into a list of FieldMeta instances
		 *
		 * @param $action
		 * @return FieldMeta[]
		 */
		public function getFieldsMeta($action)
		{
			$fields     = array_get($this->fields, $action);
			if($fields)
			{
				return $fields;
			}

			// variables
			$data       = $this->meta->fields[$action];
			$fields     = [];

			// parse
			preg_match_all('/\S+/', $data, $matches);
			foreach ($matches[0] as $match)
			{
				$field = new FieldMeta($match);
				$fields[$field->id] = $field;
			}

			// cache results
			array_set($this->fields, $action, $fields);

			// return
			return $fields;
		}

		/**
		 * Gets all form fields for a named view as an array of Field instances
		 *
		 * @param   string      $action     The resource action
		 * @param   mixed       $data       The model
		 * @param   MessageBag  $errors     Any validation errors
		 * @return  array
		 */
		public function getFields($action, $data = null, $errors = null)
		{
			// get fields
			$fieldsMeta = $this->getFieldsMeta($action);
			$fields     = new FieldList();

			// loop over $meta and build meta
			foreach($fieldsMeta as $id => $meta)
			{
				$fields[$id] = $this->getField($action, $meta, $data, $errors);
			}

			// return
			return $fields;
		}

		/**
		 * Return a single field as a Field instance
		 *
		 * @param   string              $action     The resource action
		 * @param   FieldMeta|string    $ref        A FieldMeta instance, field name, or shorthand field reference of the Field to build
		 * @param   mixed               $data       The source data, such as a model
		 * @param   MessageBag          $errors     Any validation errors
		 * @return  Field
		 * @throws  \Exception
		 */
		public function getField($action, $ref, $data, $errors = null)
		{
				/** @var Field */
				$field      = \App::make(Field::class);
				$meta       = $ref instanceof FieldMeta 
								? $ref 
								: new FieldMeta($ref);


			// ------------------------------------------------------------------------------------------------
			// basic information

				// field name & label
				$field->id              = $meta->id;
				$field->name            = $meta->name;
				$field->label           = $this->getLabel($meta->id);

				// if we have a callback, call it
				if($meta->callback && method_exists($this->meta, $meta->callback))
				{
					if( ! method_exists($this->meta, $meta->callback) )
					{
						throw new \Exception("Callback '{$meta->callback}' does not exist on " . get_class($this->meta));
					}
					$field->value   = function($model) use ($meta) { return $this->meta->{$meta->callback}($model); };
				}

				// if we're on the index route, there's no point assigning values as they will be resolved per-model in the for loop
				else if($action == 'index')
				{
					// do nothing
				}

				// otherwise, attempt to resolve a value, unless hidden
				else
				{
					$field->value   = ! in_array($meta->id, $this->meta->hidden)
										? $this->getProperty($data, $meta->path)
										: null;
				}


			// ------------------------------------------------------------------------------------------------
			// controls, for create or edit routes only

				if($action === 'create' || $action === 'edit')
				{
					// control
					$control                = $this->getControlMeta($meta->name, $action);
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
					$errorName = str_replace(']', '', str_replace('[', '.', $meta->name));
					if($errors && $errors->has($errorName))
					{
						$field->error       = $errors->first($errorName);
					}

					// old values
					$old                    = Input::old($meta->name);
					$field->old             = $old ? $old : $field->value;

					// attributes
					$field->rules           = $this->getRule($meta->name, $action);
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
		 * Combines $controls, $controls_create and $controls_edit properties to build the array
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
			$extra      = property_exists($this->meta, $method)
							? $this->meta->$method
							: null;

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
		 * Parses "create:type edit:type options:callback" structure
		 *
		 * @param   string  $name   The name of the control to find
		 * @param   string  $action The current action
		 * @return  object
		 */
		protected function getControlMeta($name, $action)
		{
			$controls   = $this->getControls($action);
			$data       = isset($controls[$name]) 
							? $controls[$name] 
							: 'text';
			return ControlMeta::create($data, $action);
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
			// invalid property
			if($prop == null)
			{
				throw new InvalidPropertyException($prop, $model);
			}

			// single property
			if(strstr($prop, '.') === false)
			{
				return $model->$prop;
			}

			// path property
			$path = explode('.', $prop);
			return array_reduce($path, function($obj, $name)
			{
				return isset($obj->$name)
					? $obj->$name
					: null;
			}, $model);
		}

}

// ------------------------------------------------------------------------------------------------
// supporting classes

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

