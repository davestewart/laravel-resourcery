<?php namespace davestewart\laravel\crud;

/**
 * Class FieldMeta
 *
 * @property    string      $value
 * @property    string      $old
 * @property    string      $type
 * @property    string[]    $options
 * @property    string      $error
 * @property    object      $model
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
		}

		public function getValue($model)
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

}