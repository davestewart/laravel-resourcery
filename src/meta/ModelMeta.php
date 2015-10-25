<?php namespace davestewart\laravel\crud\meta;

/**
 * Class ModelMeta
 * @package app\Services\Meta
 */
class ModelMeta
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		public $singular    = 'item';

		public $plural      = 'items';

		/**
		 * Validation rules
		 *
		 * should be an array of $field => $rule strings
		 *
		 * @var array
		 */
		public $rules =
		[
			// 'email'			=> 'required|email|unique:users',
		];

		/**
		 * Preferred controls for forms
		 *
		 * should be an array of $field => $control strings
		 *
		 * @var array
		 */
		public $controls =
		[
			// 'name'			=> 'text',
		];

		/**
		 * Fields to show on each page
		 *
		 * should be an array of space-separated $view => $fields strings
		 * will be converted to arrays before being injected into the view
		 *
		 * @var array
		 */
		public $fields =
		[
			// 'index'			=> 'id name email',
			'index'				=> '',
			'create'			=> '',
			'show'				=> '',
			'edit'				=> ''
		];


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * Constructor function for ModelMeta
		 *
		 * @param   string  $singular
		 * @param   string  $plural
		 * @param   array   $rules
		 * @param   array   $controls
		 * @param   array   $fields
		 */
		public function __construct($singular = NULL, $plural = NULL, array $rules = NULL, array $controls = NULL, array $fields = NULL)
		{
			// required
			if($singular)
				$this->singular	= $singular;
			if($plural)
				$this->plural	= $plural;

			// optional
			if($rules)
				$this->rules	= $rules;
			if($controls)
				$this->controls	= $controls;
			if($fields)
				$this->fields	= $fields;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		public function data()
		{
			// convert to object
			$data = (object) (array) $this;

			// convert string fields to arrays
			foreach($data->fields as $name => $value)
			{
				if(is_string($value)) { preg_match_all('/\w+/', $value, $value); }
				$data->fields[$name] = $value[0];
			};

			// check all fields have corresponding controls
			$controls = array_keys($data->controls);
			foreach($data->fields as $view => $list)
			{
				$diff = array_diff($list, $controls);
				//pr(['list' => $list, 'diff' => $diff]);
				if( count($diff) )
				{
					$fields	= implode("', '", $diff);
					$class	= get_called_class();
					throw new \Exception("Missing 'controls' definition for '$fields' in $class");
				}
			}

			// return
			return $data;
		}

		public function __set($property, $value)
		{
			if (property_exists($this, $property))
			{
				$this->$property = $value;
			}
		}

		public function __get($property)
		{
			if (property_exists($this, $property))
			{
				switch($property)
				{
					// convert string fields to arrays
					case 'fields':
						$fields = [];
						foreach($this->fields as $name => $value)
						{
							if(is_string($value)) { preg_match_all('/\w+/', $value, $value); }
							$files[$name] = $value;
						};
						return (object) $fields;

					// check all fields have corresponding controls
					case 'controls':
						$fields = $this->fields;
						pd($fields);
						foreach($fields as $view => $list)
						{
							foreach($list as $field)
							{
								if( ! $this->controls[$field] )
								{
									throw new \Exception("Missing control for field '$field'");
								}
							}
						}
						return (object) $this->controls;
				}
				return $this->$property;
			}
		}

}