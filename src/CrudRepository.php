<?php namespace davestewart\laravel\crud;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Class CrudModel
 * @package davestewart\laravel\crud
 */
class CrudRepository
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var string */
		protected $model;

		/** @var ReflectionClass */
		protected $ref;

		/** @var \Illuminate\Database\Eloquent\Model */
		protected $class;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct($model = null)
		{
			if(is_string($model))
			{
				$this->model    = $model;
				//$this->class    = new ReflectionClass($model);
			}
		}

		public function __get($property)
		{
			if (property_exists($this, $property))
			{
				return $this->$property;
			}
		}


	// -----------------------------------------------------------------------------------------------------------------
	// METHODS

		/**
		 * Utility method to find or return a model, depending on the input
		 *
		 * @param $id
		 * @return Eloquent|mixed
		 */
		public function find($id)
		{
			return $id instanceof Eloquent
				? $id
				: $this->query('findOrFail', $id);
		}

		/**
		 * Utility method to query static method calls on arbitrary models
		 *
		 * @param   $method         string  The method to call
		 * @param   $parameters,... mixed   unlimited OPTIONAL number of additional variables to display
		 * @return  mixed
		 */
		public function query($method, &$parameters = NULL)
		{
			$model = $this->model . '::' . $method;
			if(isset($parameters))
			{
				$params = array_slice(func_get_args(), 1);
				return call_user_func_array($model, $params);
			}
			else
			{
				return call_user_func($model);
			}
		}

		public function destroy($id)
		{
			$model = $this->find($id);
			$model->delete();
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MAGIIC METHOD ACCESSOR

		public function __call($name, array $values)
		{
			// parameters
			$value  = $values[0];

			// process
			if(in_array($name, $this->magicAccessors))
			{
				// if no values passed, return a value
				if(count($values) == 0)
				{
					return $this->$name;
				}

				// if we have values, set them
				if(is_array($this->$name))
				{
					$this->$name = array_merge($this->$name, $value);
				}
				else
				{
					$this->$name = $value;
				}

				// return for chaining
				return $this;
			}

			// error if an invalid property was requested
			throw new InvalidParameterException("The method $name() is not a valid magic accessor");
		}


}