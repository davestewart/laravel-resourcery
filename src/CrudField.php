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

		/** @var string */
		protected $error;

		/** @var string[] */
		protected $rules;

		/** @var object */
		protected $model;


	// ------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct()
		{

		}

		public function __set($name, $value)
		{
			if(property_exists($this, $name))
			{
				$this->$name = $value;
			}
		}

		public function __get($name)
		{
			if($name === 'value')
			{
				if( ! isset($this->model) )
				{
					throw new \Exception('Cannot get field value, as the $model property was never set');
				}
				return $this->getDotProp($this->model, $this->name);
			}
			else if(property_exists($this, $name))
			{
				return $this->$name;
			}
		}

		public function getValue($model)
		{
			return $this->getDotProp($model, $this->name);
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