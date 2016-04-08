<?php

namespace davestewart\resourcery\services;


use davestewart\resourcery\classes\exceptions\InvalidPropertyException;
use davestewart\resourcery\classes\meta\ResourceMeta;
use Illuminate\Translation\Translator;
use PhpParser\Error;

class LangService
{

	// ------------------------------------------------------------------------------------------------
	// PROPERTIES

		/**
		 * The currently-loaded resource
		 *
		 * @var string $name
		 */
		protected $name;

		/**
		 * An array of placeholder values
		 *
		 * @var array $values
		 */
		protected $values;

		/**
		 * A secondary value object used to populate custom placeholder values
		 *
		 * @var mixed $model
		 */
		protected $model;

		/**
		 * Custom labels array
		 *
		 * Loaded from lang configuration file
		 *
		 * @var array $labels
		 */
		protected $labels;

		/**
		 * The current group
		 *
		 * Set when accessing translation keys via magic property access i.e. $lang->action->create
		 *
		 * @var string $group
		 */
		protected $group;

		/**
		 * The current text transformation function name
		 *
		 * @var string $filter
		 */
		protected $filter;

		/**
		 * The translator instance used to retrieve strings
		 *
		 * @var Translator $translator
		 */
		protected $translator;


	// ------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function initialize($name = null, $values = [])
		{
			// initialize translator
			$this->translator = app('translator');

			// values
			$this->loadLabels($name);
			$this->setValues($values);

			// return
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// value setters

		/**
		 * Set values for standard placeholders
		 *
		 * @param   array   $values     An array of key => value pairs
		 * @param   bool    $merge      An optional boolean to merge, rather than replace, values
		 * @return  self
		 */
		public function setValues(array $values, $merge = false)
		{
			$this->values = $merge
			    ? $this->values + $values
				: $values;
			return $this;
		}

		/**
		 * Sets model for model-specific placeholders
		 *
		 * @param   mixed   $model
		 * @return  self
		 */
		public function setModel($model)
		{
			$this->model = $model;
			return $this;
		}

		/**
		 * Sets resource-specific labels
		 *
		 * Also updates "naming" values if ":item" and ":items" keys exist in the array
		 *
		 * @param   array $labels An array of id => Label values
		 * @return  self
		 */
		public function setLabels(array $labels)
		{
			// update class "naming" values from array if they exist
			foreach(['item', 'items'] as $key)
			{
				$_key = ":$key";
				if(isset($labels[$_key]))
				{
					$this->values[$key] = $labels[$_key];
					unset($labels[$_key]);
				}
			}

			// load and merge defaults array if it exists
			$defaults       = $this->translator->trans('resourcery::labels.:defaults');
			if(is_array($defaults))
			{
				$labels     = array_merge($defaults, $labels);
			}

			// update labels
			$this->labels = $labels;

			// return
			return $this;
		}

		/**
		 * Loads resource-specific labels from language config
		 *
		 * @param   string  $name   The name of the resource to load
		 * @return  self
		 */
		public function loadLabels($name)
		{
			// variables
			$this->name     = $name;
			$labels         = $this->translator->trans("resourcery::labels.$name");

			// set values
			if(is_array($labels))
			{
				$this->setLabels($labels);
			}
			else
			{
				$this->labels = [];
			}

			// return
			return $this;
		}



	// ------------------------------------------------------------------------------------------------
	// MAGIC GETTERS

		/**
		 * Fluid getter for "messages" translation *only*
		 *
		 * Allows use of property-style getter syntax rather than methods:
		 *
		 *  - $lang->title('create')
		 *  - $lang->title->create
		 *
		 * The access order is as follows:
		 *
		 *  - $lang->$key       - returns a key value if a group has previously been set
		 *  - $lang->label      - returns the labels array
		 *  - $lang->value      - returns the values array
		 *  - $lang->$method    - sets a standard group and returns self
		 *  - $lang->$group     - checks for and sets a custom group and returns self
		 *
		 * @param   string          $id        A group, or if previously-supplied, key name, or a property on this class
		 * @return  self|string
		 * @throws  InvalidPropertyException
		 */
		public function __get($id)
		{
			// if a group has previously-been set, this must be the key for the group, so return the value
			if($this->group)
			{
				$group = $this->group;
				$this->group = null;
				return $this->$group($id);
			}

			// used when user is calling $lang->label
			else if($id === 'label')
			{
				return (object) $this->labels;
			}

			// used when user is calling $lang->values
			else if($id === 'value')
			{
				return (object) $this->values;
			}

			// used to treat a core lang method (action, prompt, etc) as a property
			else if(method_exists($this, $id))
			{
				$this->group = $id;
				return $this;
			}

			// user to retrieve custom groups
			else if($this->hasGroup($id))
			{
				$this->group = $id;
				return $this;
			}
			throw new InvalidPropertyException($id, __CLASS__);
		}
	
		/**
		 * Magic call, mainly to allow retrieval of custom message keys as methods
		 *
		 * @param   string      $name           The name of the message group
		 * @param   array       $arguments
		 * @return  string
		 * @throws  \Exception
		 */
		public function __call($name, $arguments)
		{
			if($this->hasGroup($name))
			{
				return $this->message($name, $arguments[0], count($arguments) > 1 ? $arguments[1] : null);
			}
			throw new \Exception("No such message group `$name` for resource `{$this->name}`");
		}


	// ------------------------------------------------------------------------------------------------
	// TRANSLATION GETTERS

		/**
		 * Loads translation values from config
		 *
		 * @param   string  $path       The full path to the translation item, e.g. "messages.title.create"
		 * @param   array   $values     An optional array of placeholder values
		 * @return  string
		 */
		public function get($path, $values = [])
		{
			$text = $this->translator->trans("resourcery::$path");
			return $this->parse($text, $values);
		}

		/**
		 * Populates and and transforms arbitrary text
		 *
		 *  - translates text using stored or supplied values
		 *  - attempts to populate any remaining :placeholders using model values
		 *  - transforms text if a text transformation filter is set
		 *
		 * @param   string  $text       An arbitrary string with :placeholders to replace
		 * @param   mixed   $values     A values object (arrays will be converted) of key->values
		 * @return  string
		 */
		public function parse($text, $values = null)
		{
			// parameters
			if($values == null)
			{
				$values = $this->values;
			}

			// process values
			$text = $this->process($text, (object) $values);

			// if any placeholders remain, attempt to resolve them using any loaded model
			if($this->model)
			{
				$text = $this->process($text, $this->model);
			}

			// apply filters
			if($this->filter)
			{
				if ($this->filter == 'ucfirst')
					return ucfirst($text);
				if ($this->filter == 'ucwords')
					return ucwords($text);
				if ($this->filter == 'upper')
					return strtoupper($text);
				if ($this->filter == 'lower')
					return strtolower($text);
			}

			// return
			return $text;
		}


	// ------------------------------------------------------------------------------------------------
	// FILE GETTERS

		/**
		 * Get translated message
		 *
		 * Also allows a Capitalized key to be passed in, which will capitalize the return value
		 *
		 * @param   string  $group  The message group to choose
		 * @param   string  $key    The group key to choose
		 * @param   array   $values Values to replace placeholders with
		 * @return  string
		 */
		public function message($group, $key, $values = [])
		{
			// check for capitalization
			$capitalize = false;
			if(ctype_upper($key[0]))
		    {
		        $capitalize = true;
			    $key = strtolower($key);
		    }

			// get translation
			$text   = $this->get("messages.$group.$key", $values);
			$text   = $this->parse($text, $values);

			// capitalize and return
			return $capitalize
				? ucfirst($text)
				: $text;
		}

		/**
		 * Get the translated label for a model's field
		 *
		 * @param   string      $name       The field name to be fetched, e.g. name, age, location
		 * @param   string      $default    The default value to pass back if teh field isn't found
		 * @return  string
		 */
		public function label($name, $default = null)
		{
			$item       = $this->values['item'];
			$default    = $default === null
							? "resourcery::labels.$item.$name"
							: $default; 
			return array_get($this->labels, $name, $default);
		}
	
		public function validation()
		{
			return $this->translator->trans('resourcery::validation');
		}


	// ------------------------------------------------------------------------------------------------
	// MESSAGE GETTERS

		/**
		 * Get translated title
		 *
		 * @param   string  $key    The action's key, e.g. index, create, results
		 * @return  string
		 */
		public function title($key)
		{
			return $this->message('title', $key);
		}

		/**
		 * Get translated user prompt
		 *
		 * @param   string  $key    The prompt's key, e.g. show, submit, select
		 * @return  string  string
		 */
		public function prompt($key)
		{
			return $this->message('prompt', $key);
		}

		/**
		 * Get translated action name
		 *
		 * @param   string  $key    The action's key, e.g. create, edit, submit
		 * @return  string
		 */
		public function action($key)
		{
			return $this->message('action', $key);
		}

		/**
		 * Get translated confirmation string
		 *
		 * @param   string  $key    The confirmation's key, e.g. delete, cancel, back
		 * @return  string
		 */
		public function confirm($key)
		{
			return $this->message('confirm', $key);
		}

		/**
		 * Get translated status message
		 *
		 * @param   string  $key    The status message's key, e.g. created, not_updated, invalid
		 * @return  string
		 */
		public function status($key)
		{
			return $this->message('status', $key);
		}

		/**
		 * Get translated miscellaneous text
		 *
		 * @param   string  $key    The text's key, e.g. actions, related
		 * @return  string
		 */
		public function text($key)
		{
			return $this->message('text', $key);
		}


	// ------------------------------------------------------------------------------------------------
	// FILTERS

		/**
		 * Reset all text transformation filters
		 * 
		 * @return $this
		 */
		public function reset()
		{
			$this->filter = null;
			return $this;
		}

		/**
		 * Set text transformation filter to Title Case
		 * 
		 * @return $this
		 */
		public function ucwords()
		{
			$this->filter = __FUNCTION__;
			return $this;
		}

		/**
		 * Set text transformation filter to Sentence case
		 * 
		 * @return $this
		 */
		public function ucfirst()
		{
			$this->filter = __FUNCTION__;
			return $this;
		}

		/**
		 * Set text transformation filter to UPPER CASE
		 * 
		 * @return $this
		 */
		public function upper()
		{
			$this->filter = __FUNCTION__;
			return $this;
		}

		/**
		 * Set text transformation filter to lower case
		 * 
		 * @return $this
		 */
		public function lower()
		{
			$this->filter = __FUNCTION__;
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Checks if a message group exists
		 *
		 * @param   string  $key    The group key to check for
		 * @return  bool
		 */
		protected function hasGroup($key)
		{
			return $this->translator->has("resourcery::messages.$key");
		}

		/**
		 * Replaces text with placeholder values
		 *
		 * Also allows Capitalized version of keys to be passed in, which will capitalize the replaced placeholders
		 *
		 * @param   string  $text       The input text with :placeholders to replace
		 * @param   object  $values     An object with gettable properties
		 * @return  string
		 */
		protected function process($text, $values)
		{
			if(strstr($text, ':') !== false)
			{
				return preg_replace_callback('/:(\w+)/', function ($matches) use ($values)
				{
					// extract values
					list($match, $key) = $matches;

					// test if we're being passed a Capitalized key
					$capitalize = false;
					if(ctype_upper($key[0]))
				    {
					   $capitalize = true;
					   $key = strtolower($key);
				    }

					// resolve property
					$value = @ $values->$key; // using error suppression here, rather than isset, to avoid calling model getter attributes twice

					// return value
					return ($value
						? $capitalize
							? ucwords($value)
							: $value
						: $match);
				}, $text);
			}

			return $text;
		}

}