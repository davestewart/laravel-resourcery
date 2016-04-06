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

		/** @var Translator $translator */
		protected $translator;
	
		/** @var array $values */
		protected $values;

		/** @var mixed $model */
		protected $model;

		/** @var array $labels */
		protected $labels;

		/** @var string $group */
		protected $group;

		/** @var string $filter */
		protected $filter;


	// ------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function initialize(ResourceMeta $meta)
		{
			// initialize translator
			$this->translator = app('translator');

			// get initial naming values from Meta instance
			$this->values   = array_intersect_key($meta->naming, array_flip(['item', 'items']));

			// load in translation values 
			$this->loadLabels($meta->name);

			// add "Sentence case" versions of values so we can use :Item and :Items in translations
			foreach ($this->values as $key =>$value)
			{
				$this->values[ucfirst($key)] = ucfirst($value);
			}

			// return
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// value setters

		public function setValues($values)
		{
			$this->values = $values;
			return $this;
		}

		public function setModel($model)
		{
			$this->model = $model;
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// TRANSLATION GETTERS

		/**
		 * Fluid getter for "messages" translation *only*
		 *
		 * Allows use of property-style getter syntax rather than methods:
		 *
		 *  - $lang->title('create')
		 *  - $lang->title->create
		 *
		 * Alternatively, a group is not found, falls back to class properties
		 *
		 * @param   string          $id        A group, or if previously-supplied, key name, or a property on this class
		 * @return  self|string
		 * @throws  InvalidPropertyException
		 */
		public function __get($id)
		{
			if($this->group)
			{
				$group = $this->group;
				$this->group = null;
				return $this->$group($id);
			}
			else if($id === 'label')
			{
				return (object) $this->labels;
			}
			else if($id === 'values')
			{
				return (object) $this->values;
			}
			else if(method_exists($this, $id))
			{
				$this->group = $id;
				return $this;
			}
			throw new InvalidPropertyException($id, __CLASS__);
		}
	
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
		 * @param   string  $group  The message group to choose
		 * @param   string  $key    The group key to choose
		 * @param   array   $values Values to replace placeholders with
		 * @return  string
		 */
		public function message($group, $key, $values = [])
		{
			$lower  = strtolower($key);
			$text   = $this->get("messages.$group.$lower", $values);
			$text   = $this->parse($text, $values);
			return ctype_upper($key[0])
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
			$default    = $default ?: "resourcery::labels.$item.$name";
			return array_get($this->labels, $name, $default);
		}
	
		public function validation()
		{
			return $this->translator->trans('resourcery::validation');
		}


	// ------------------------------------------------------------------------------------------------
	// MESSAGE GETTERS

		/**
		 * Get translated action name
		 *
		 * @param   string  $key    The action's key, e.g. create, edit, store
		 * @return  string
		 */
		public function action($key)
		{
			return $this->message('action', $key);
		}

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
		 * Replaces text with placeholder values
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
					list($match, $prop) = $matches;
					$value = @ $values->$prop; // using error suppression here, rather than isset, to avoid calling model getter attributes twice
					return $value ? $value : $match;
				}, $text);
			}

			return $text;
		}

		/**
		 * Loads resource-specific labels if they exist
		 *
		 * Also updates "naming" values if :item and :items keys exist in the loaded array
		 *
		 * @param $group
		 */
		protected function loadLabels($group)
		{
			// load resource-specific labels
			$labels = $this->translator->trans("resourcery::labels.$group");

			// update class "naming" values from array if they exist
			if(is_array($labels))
			{
				foreach(['item', 'items'] as $key)
				{
					$_key = ":$key";
					if(isset($labels[$_key]))
					{
						$this->values[$key] = $labels[$_key];
						unset($labels[$_key]);
					}
				}
			}
			else
			{
				$labels = [];
			}

			// load and merge defaults array if it exists
			$defaults       = $this->translator->trans('resourcery::labels.:defaults');
			if(is_array($defaults))
			{
				$labels     = array_merge($defaults, $labels);
			}

			// update labels
			$this->labels = $labels;
		}

}