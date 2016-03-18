<?php namespace davestewart\laravel\crud;

use davestewart\laravel\crud\controls\CrudControl;

/**
 * Class FieldMeta
 *
 * @property    string          $name
 * @property    string          $label
 * @property    string          $value
 * @property    string          $old
 * @property    string          $type
 * @property    string[]        $options
 * @property    string[]        $rules
 * @property    string          $error
 * @property    string          $view
 * @property    object          $model
 * @property    CrudControl     $control
 */
class CrudField
{

	// ------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var string */
		protected $name;

		/** @var string */
		protected $label;

		/** @var string */
		protected $value;

		/** @var string */
		protected $old;

		/** @var string */
		protected $type;

		/** @var string[] */
		protected $options;

		/** @var string[] */
		protected $rules;

		/** @var string */
		protected $error;

		/** @var string */
		protected $view;


	// ------------------------------------------------------------------------------------------------
	// ACCESSORS

		public function __set($name, $value)
		{
			if(property_exists($this, $name))
			{
				$this->$name = $value;
			}
		}

		public function __get($name)
		{
			if(property_exists($this, $name))
			{
				return $this->$name;
			}
			else if($name == 'control')
			{
				return \App::make('CrudControl', [$this]);
			}
			else if($name == 'dump')
			{
				return '<pre>' . print_r($this, 1) . '</pre>';
			}

		}

		public function value($model)
		{
			return is_callable($this->value)
				? call_user_func($this->value, $model)
				: $this->getDotProp($model, $this->name);
		}

		public function getDotProp($obj, $names)
		{
			if(is_string($names))
			{
				if(strstr($names, '.') === FALSE)
				{
					return $obj->$names;
				}
				$names = explode('.', $names);
			}
			return array_reduce($names, function($obj, $name){ return $obj->$name; }, $obj);
		}


	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		public function __toString()
		{
			return $this->dump;
		}

}