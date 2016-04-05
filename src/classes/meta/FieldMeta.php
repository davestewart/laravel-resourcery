<?php namespace davestewart\resourcery\classes\meta;

/**
 * Meta class to parse field shorthand properties into an actual class
 */
class FieldMeta
{
	// ------------------------------------------------------------------------------------------------
	// types

		const PROPERTY      = 'property';   // name
		const OBJECT        = 'object';     // name[prop] or name.prop (where it doesn't lead to a model)
		const RELATED       = 'related';    // name.prop.prop (where it leads - any point in the path - to a model)
		const RELATION      = 'relation';   // name (potential relation; needs to be discovered outside of this class)


	// ------------------------------------------------------------------------------------------------
	// properties

		public $id;
		public $name;
		public $type;
		public $path;
		public $relation;
		public $callback;                   // name:prop
		public $value;


	// ------------------------------------------------------------------------------------------------
	// methods

		/**
		 * FieldMeta constructor
		 *
		 * @param   string  $value      A shorthand field value, such as field, field.property, field:callback
		 */
		public function __construct($value)
		{
			// variables
			list($name, $callback) = array_merge(explode(':', $value), [null]);

			// basic properties
			$this->name         = $name;
			$this->type         = FieldMeta::PROPERTY;
			$this->path         = $name;
			$this->value        = $value;
			$this->callback     = $callback;
			$this->id           = preg_replace('/^_+|_+$/', '', preg_replace('/\W+/', '_', $name));

			// set properties
			preg_match('/[\[\.]/', $name, $matches);
			if(strstr($name, '.') !== false)
			{
				$this->type     = FieldMeta::OBJECT;
			}
			else if(strstr($name, '[') !== false)
			{
				$this->type     = FieldMeta::OBJECT;
				$this->path     = trim(preg_replace('/[\[\]]+/', '.', $name), '.');
			}
		}

}
