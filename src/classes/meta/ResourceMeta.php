<?php namespace davestewart\resourcery\classes\meta;

use davestewart\resourcery\classes\exceptions\InvalidPropertyException;

/**
 * ResourceMeta Class
 * 
 * Defines properties and behavior (through methods) for a resource
 *
 * @property string     $class          The fully-qualified class of the Model
 * @property string[]   $related        The names of relationships to eager load on the index page
 * @property string[]   $clauses        Default behaviour for index pages
 *
 * @property string[]   $naming         Default naming for translation
 *
 * @property string[]   $views          Paths to the view files that will render data
 * @property string[]   $fields         Fields to show for each action
 * @property string[]   $hidden         Fields to keep secret when repopulating forms
 *
 * @property string[]   $labels         Optional array of labels for fields
 * @property string[]   $controls       Preferred controls for fields
 * @property string[]   $rules          Validation rules
 *
 * @property string     $name           The single name of the Model used to load custom translations, etc
 */
class ResourceMeta
{

	// -----------------------------------------------------------------------------------------------------------------
	// DATABASE

		/**
		 * The fully-qualified class of the Model
		 *
		 * @var string
		 */
		protected $class        = '\App\Models\Item';

		/**
		 * The names of relationships to eager load on the index page
		 *
		 * Should be an array of table names
		 *
		 * - ['users', 'clients', 'posts']
		 *
		 * @var array
		 */
		protected $related      = [ ];

		/**
		 * Default behaviour for index pages
		 *
		 * @var int
		 */
		protected $clauses =
		[
			'orderBy'           => 'id',
			'orderDir'          => 'desc',
			'perPage'           => 50,
		];


	// -----------------------------------------------------------------------------------------------------------------
	// LANGUAGE

		/**
		 * Default naming for translation
		 *
		 * Note that the title attribute entry is used by the default getTitle() method on this class, to attempt to
		 * resolve a human-readable title for the model.
		 *
		 * This method can be left as-is, or overridden in the subclass
		 *
		 * @var array
		 */
		protected $naming =
		[
			'item'              => 'item',
			'items'             => 'items',
			'titleAttribute'    => 'id',
		];


	// -----------------------------------------------------------------------------------------------------------------
	// DATA

		/**
		 * Paths to the view files that will render data
		 *
		 * Defaults to the package's catch-all templates
		 *
		 * These can be overridden per meta, per view, so for example to output a custom "edit" or "show" page,
		 * just update the keys like so:
		 *
		 *  - 'edit'            => 'forms.product.edit',
		 *  - 'show'            => 'entities.products.software.show',
		 *
		 * Everything else will then render as expected, with just these two views being swapped out, but all the
		 * same data will be passed in
		 *
		 * @var string[]
		 */
		protected $views =
		[
			// layout
			'layout'            => 'resourcery::layout',
			
			// pages
			'create'            => 'resourcery::page.create',
			'index'             => 'resourcery::page.index',
			'show'              => 'resourcery::page.show',
			'edit'              => 'resourcery::page.edit',
			
			// list
			'list'              => 'resourcery::list.layout',
			'actions'           => 'resourcery::list.actions',
			'related'           => 'resourcery::list.related',

			// form
			'form'              => 'resourcery::form.layout',
			'fields'            => 'resourcery::form.fields',
			'field'             => 'resourcery::form.field',
			'submit'            => 'resourcery::form.submit',
		    'errors'            => 'resourcery::form.errors',

		    // components
		    'pagination'        => 'resourcery::components.pagination',
		    'status'            => 'resourcery::components.status',
		];

		/**
		 * Fields to show for each action
		 *
		 * Should be an array of space-separated strings (or an array of arrays) of the form $action => $fields:
		 *
		 *  - 'index' => 'username email created_date'
		 *
		 * If you want to render any values that require some interim functionality, specify a getter function:
		 *
		 * - 'index' => 'posts:getPostCount'
		 *
		 * Callbacks should be available as a class method, of the format function($model, $action) and should return
		 * an associative array of $value => $label pairs. PHP's array_column() provides a neat way to do this!
		 *
		 * You may also use the following placeholders to mirror your model's setup:
		 *
		 * - :all           All fields
		 * - :visible       All visible fields
		 * - :fillable      All fillable fields
		 *
		 * @var string[]
		 */
		protected $fields =
		[
			'index'			    => ':visible',
			'create'		    => ':fillable',
			'edit'			    => ':fillable',
			'show'			    => ':visible',
		];

		/**
		 * Fields to keep secret when repopulating forms
		 *
		 * @var string[]
		 */
		protected $hidden =
		[
			'password',
			'password_confirm'
		];


	// -----------------------------------------------------------------------------------------------------------------
	// FORM

		/**
		 * Optional array of labels for fields
		 *
		 * Should be an array of the form $field => $name:
		 *
		 *  - 'name'        => 'User Name'
		 *  - 'clientId'    => 'Client'
		 *
		 * If not supplied, labels default to the $field name
		 *
		 * @var string[]
		 */
		protected $labels = [ ];

		/**
		 * Preferred controls for fields
		 *
		 * Should be an array of the form $field => $type:
		 *
		 * - 'username' => 'text'
		 * - 'clientId' => 'email'
		 *
		 * Use any available types that your view or view library can render
		 *
		 * If a control (such as a select) requires additional options to render, specify a getter function:
		 *
		 * - 'clientId' => 'select:getClientIds'
		 *
		 * To specify different control types for different views, prefix the control type with the action:
		 *
		 * - 'clientId' => 'create:select edit:radios'
		 *
		 * To include control options, use the special value 'options:callback':
		 *
		 * - 'clientId' => 'create:select edit:radios options:getClientIds'
		 *
		 * Callbacks should be available as a class method, of the format function($model, $action) and should return
		 * an associative array of $value => $label pairs. PHP's array_column() provides a neat way to do this!
		 *
		 * At some point the following syntax to call a getter on the related model may be supported:
		 *
		 * - 'clientId' => 'dropdown:client[id/name]'
		 *
		 * @var string[]
		 */
		protected $controls = [ ];

		/**
		 * Controls for create action only
		 *
		 * Values will be merged with the original controls array
		 *
		 * @var null|array
		 */
		protected $controls_create = null;

		/**
		 * Controls for edit action only
		 *
		 * Values will be merged with the original controls array
		 *
		 * @var null|array
		 */
		protected $controls_edit = null;


	// -----------------------------------------------------------------------------------------------------------------
	// VALIDATION

		/**
		 * Validation rules
		 *
		 * Should be an array of the form $field => $rule:
		 *
		 * - 'email'        => 'required|email|unique:users',
		 *
		 * @var string[]
		 */
		protected $rules = [ ];

		protected $rules_store = null;

		protected $rules_update = null;


		// defaults
		// placeholders
		// errors

		// ways to get values
		// property
		// property_action
		// getProperty
		// getPropertyOptions($action, $model
		// getPropertyDefault
		// getPropertyPlaceholder
		// getPropertyAttributes
		// getPropertyHtml





	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function __construct($class = null)
		{
			if($class)
			{
				$this->class = $class;
			}
		}


	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		/**
		 * Gets a human-readable title for the model
		 *
		 * This method is called by the translation engine, when it comes across a :title placeholder
		 *
		 * @param $model
		 * @return mixed
		 */
		public function getTitle($model)
		{
			$prop = $this->naming['titleAttribute'];
			return @ $model->$prop;
		}

		/**
		 * Gets a property on the ResourceMeta instance
		 *
		 * @param   string      $name
		 * @return  mixed|null
		 * @throws  InvalidPropertyException
		 */
		public function get($name)
		{
			if(strstr($name, '.') !== false)
			{
				$parts  = explode('.', $name);
				$prop   = array_shift($parts);
				$path   = implode('.', $parts);
				if(array_has($this->$prop, $path))
				{
					return array_get($this->$prop, $path);
				}
			}
			else if(property_exists($this, $name))
			{
				return $this->$name;
			}
			else if($name === 'name')
			{
				return $this->naming['item'];
			}
			throw new InvalidPropertyException($name, get_called_class());
		}

		/**
		 * Sets or merges values into an array property on the ResourceMeta instance
		 *
		 * @param   string      $name
		 * @param   mixed       $value
		 * @return  self
		 * @throws  InvalidPropertyException
		 */
		public function set($name, $value)
		{
			if(strstr($name, '.') !== false)
			{
				$parts  = explode('.', $name);
				$prop   = array_shift($parts);
				$path   = implode('.', $parts);
				if(property_exists($this, $prop))
				{
					array_set($this->$prop, $path, $value);
				}
				else
				{
					throw new InvalidPropertyException($name, get_called_class());
				}
			}
			else if(property_exists($this, $name))
			{
				if(is_array($this->$name))
				{
					$this->$name = array_merge($this->$name, $value);
				}
				else
				{
					$this->$name = $value;
				}
			}
			else
			{
				throw new InvalidPropertyException($name, get_called_class());
			}
			return $this;
		}

		public function __get($name)
		{
			return $this->get($name);
		}

		public function __set($name, $value)
		{
			$this->set($name, $value);
		}


	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		public function toArray()
		{
			$arr = [];
			foreach($this as $key => $value)
			{
				$arr[$key] = $value;
			}
			return $arr;
		}

}