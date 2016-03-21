<?php namespace davestewart\laravel\crud\controls;

use davestewart\laravel\crud\CrudField;
use davestewart\laravel\crud\errors\InvalidPropertyException;
use Illuminate\Support\MessageBag;
use Input;
use Session;

class CrudControl
{

	// ------------------------------------------------------------------------------------------------
	// properties

		/** @var CrudField|mixed  */
		protected $field;

		/** @var array  */
		protected $classes;

		/** @var array  */
		protected $attributes;


	// ------------------------------------------------------------------------------------------------
	// instantiation

		public static function __callStatic($name, $params)
		{
			return call_user_func_array([get_called_class(), 'create'], array_merge([$name], $params));
		}

	/**
	 * Creates a control without
	 *
	 * @param   string          $type
	 * @param   string          $name
	 * @param   array|null      $options
	 * @param   array           $input
	 * @param   MessageBag|null $errors
	 * @return  self
	 */
		public static function create($type, $name, array $options = null, array $input = null, MessageBag $errors = null)
		{
			// variables
			$control        = new static;
			$field          = $control->field;
			$errors         = isset($errors) ? $errors : Session::get('errors', new MessageBag);
			$input          = isset($input) ? $input : Input::all();

			// populate field
			$field->type    = $type;
			$field->name    = $name;
			$field->label   = ucwords($name);
			$field->value   = is_object($input) ? $input->$name : $input[$name];
			$field->error   = $errors ? $errors->first($name) : null;
			$field->options = $options;
			$field->view    = 'vendor.crud.partials.field';

			// debug
			//pd($field);

			// return
			return $control;
		}

		public function __construct(CrudField $field = null)
		{
			// properties
			$this->field        = $field ? $field : \App::make('CrudField');
			$this->attributes   = [];
			$this->classes      = [];

			// initialize
			$this->initialize();
		}

		protected function initialize()
		{
			$this
				->setAttr('id', $this->field->name)
				->setAttr('class', 'form-control')
				->setAttr('autocomplete', 'off');

			if(preg_match('/\brequired\b/', $this->field->rules))
			{
				$this->setClass('required');
			}
		}


	// ------------------------------------------------------------------------------------------------
	// public methods

		public function set($name, $value)
		{
			if(property_exists($this->field, $name))
			{
				$this->field->$name = $value;
			}
			return $this;
		}

		public function setClass($name = null, $state = 1)
		{
			if ($name)
			{
				if ($state)
				{
					$this->classes[$name] = 1;
				}
				else
				{
					unset($this->classes[$name]);
				}
			}
			else
			{
				$this->classes = [];
			}
			return $this;
		}

		public function setAttr($name, $value = null)
		{
			if($value == null)
			{
				unset($this->attributes[$name]);
			}
			else if(is_array($name))
			{
				foreach($name as $key => $value)
				{
					$this->setAttr($key, $value);
				}
			}
			else
			{
				if($name == 'class')
				{
					$this->addAttr($name, $value);
				}
				else
				{
					$this->attributes[$name] = $value;
				}
			}
			return $this;
		}

		public function addAttr($name, $value)
		{
			if( ! isset($this->attributes[$name]) )
			{
				$this->attributes[$name] = $value;
			}
			else
			{
				$this->attributes[$name] .= ' ' . $value;
			}
			return $this;
		}

		/**
		 * Magic getter
		 *
		 * Get field or control properties
		 *
		 * @param $name
		 * @return mixed|string
		 * @throws InvalidPropertyException
		 */
		public function __get($name)
		{
			if(property_exists($this, $name))
			{
				return $this->$name;
			}
			else if(property_exists($this->field, $name))
			{
				return $this->field->$name;
			}
			else if(isset($this->attributes[$name]))
			{
				return $this->attributes[$name];
			}
			else if($name == 'element')
			{
				return $this->render(true);
			}
			throw new InvalidPropertyException($name, get_called_class());
		}

		public function __call($name, $params)
		{
			$value = $params[0];
			switch($name)
			{
				case 'class':
					$this->setClass($name, $value);
					break;

				case 'attr':
					$this->setAttr($params[0], $params[1]);
					break;

				default:
					if(is_object($value))
					{
						$this->field->value = $value->{$this->field->name};
					}
					else if(property_exists($this->field, $name))
					{
						$this->field->$name = $value;
					}
					else
					{
						$this->setAttr($name, $value);
					}
			}
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// render methods

		public function render($view = null)
		{
			// values
			$field      = $this->field;
			$attributes = $this->getControlAttrs();
			$classes    = implode(' ', $this->getGroupClasses($field));

			// control
			$method     = "make_{$field->type}";
			$method     = method_exists($this, $method) ? $method : 'make_text';
			$control    = $this->$method($field, $attributes);
			if($view === true)
			{
				return $control;
			}

			// view
			$view       = is_string($view)
							? $view
							: $field->view;

			// return
			return view($view, compact('field', 'control', 'classes'));
		}

		public function debug()
		{
			return '<pre>' . print_r($this, 1) . '</pre>';
		}


	// ------------------------------------------------------------------------------------------------
	// utilities

		protected function getControlAttrs($attrs = [])
		{
			return $this->attributes + $attrs;
		}

		protected function getGroupClasses($field)
		{
			$classes    = $field->error
							? ['has-error']
							: [];
			$classes    += strstr($field->rules, 'required') !== FALSE
							? ['required']
							: [];
			$classes    += array_keys($this->classes);
			return $classes;
		}

		public function __toString()
		{
			return (string) $this->render();
		}
}