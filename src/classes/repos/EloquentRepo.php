<?php namespace davestewart\resourcery\classes\repos;

use davestewart\resourcery\classes\exceptions\InvalidPropertyException;
use davestewart\resourcery\classes\exceptions\InvalidRelationException;
use davestewart\resourcery\classes\meta\FieldMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Eloquent;
use Request;

/**
 * EloquentRepo class
 * 
 * Concrete implementation of AbstractRepo
 */
class EloquentRepo extends AbstractRepo
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var string */
		protected $class;

		/** @var Eloquent */
		protected $builder;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * EloquentRepo constructor
		 *
		 * @param string $class
		 */
		function __construct($class = null)
		{
			if($class)
			{
				$this->initialize($class);
			}
		}

		/**
		 * Initialize the repo with a model's class
		 *
		 * @param   string  $class
		 * @return  self
		 */
		public function initialize($class)
		{
			$this->class    = $class;
			$this->builder  = $this->call('query');
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// DATA ACCESS

		/**
		 * Create a new model
		 *
		 * @param array $data
		 * @return Eloquent
		 */
		public function create($data = [])
		{
			return new $this->class($data);
		}

		/**
		 * Returns all items
		 *
		 * @param   int         $limit
		 * @return  Collection
		 */
		public function all($limit = null)
		{
			return $limit == null
				? $this->builder
					->get()
				: $this->builder
					->paginate($limit)
					->appends(Request::except('page'));
		}

		/**
		 * Finds and / or returns an eloquent model
		 *
		 * @param   Eloquent|int    $id
		 * @return  Eloquent
		 */
		public function find($id)
		{
			return is_object($id)
				? $id
				: $this->builder->find($id);
		}

		/**
		 * Updates a database row
		 *
		 * @param   int             $id
		 * @param   array           $data
		 * @return  Eloquent
		 */
		public function update($id, $data)
		{
			return $this->find($id)->update($data);
		}

		/**
		 * Inserts a new row
		 *
		 * @param   array           $data
		 * @return  mixed
		 */
		public function store($data)
		{
			return $this->create($data)->save();
		}

		/**
		 * Deletes a row
		 *
		 * @param   int             $id
		 * @return  Eloquent
		 */
		public function destroy($id)
		{
			return $this->find($id)->delete();
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MODIFIERS

		/**
		 * Orders the query results
		 *
		 * @param   string      $column
		 * @param   string      $direction
		 * @return  $this
		 */
		public function orderBy($column, $direction = 'asc')
		{
			$this->builder->orderBy($column, $direction);
			return $this;
		}

		/**
		 * Filters the query results
		 *
		 * @param array $params
		 * @return $this
		 */
		public function filter(array $params)
		{
			// exit early if no params
			if(count($params) == 0)
			{
				return $this;
			}

			// filter
			$fields     = $this->getFields('all');
			$fields     = array_combine($fields, $fields);
			$where      = array_intersect_key($params, $fields);

			// query
			$this->builder->where($where);

			// return
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MAGIC

		/**
		 * Forwards all method calls to query builder instance
		 *
		 * Enables aggregation on the Rep, for example $repo->all()->count()
		 *
		 * @param $name
		 * @param $values
		 * @return mixed
		 */
		public function __call($name, $values)
		{
			return call_user_func_array([$this, 'query'], array_merge([$name], $values));
		}

		/**
		 * Returns any existing properties
		 *
		 * @param $name
		 * @return mixed
		 * @throws InvalidPropertyException
		 */
		public function __get($name)
		{
			if(property_exists($this, $name))
			{
				return $this->$name;
			}
			throw new InvalidPropertyException($name, __CLASS__);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Returns the fields for an Eloquent model (used by the ResourceMeta constructor)
		 *
		 * @param null $name
		 * @return \string[] An array of field names for different types of view
		 */
		public function getFields($name = null)
		{
			// param
			$types  = $name == null ? ['all', 'visible', 'fillable', 'hidden'] : [$name];

			// variables
			$model  = $this->create();
			$data   = [];

			// get types
			foreach($types as $type)
			{
				switch($type)
				{
					case 'all':
						$data[$type] = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
						break;

					case 'visible':
						$data[$type] = $model->getVisible();
						break;

					case 'fillable':
						$data[$type] = $model->getFillable();
						break;

					case 'hidden':
						$data[$type] = $model->getHidden();
						break;
				}
			}

			// return
			return $name == null ? $data : $data[$name];
		}

		/**
		 * Attempts to determine related models from field list by checking for relationships on linked properties
		 *
		 * Note that the job of this algorithm is NOT to test for object properties or model attributes, but simply
		 * to test whether relations exist and return an array that can be passed to a model's with() method
		 *
		 * @param   FieldMeta[]     $fields     An array of FieldMeta instances. The method uses the `path` property to work out existing relations
		 * @return  array
		 * @throws  InvalidRelationException
		 */
		public function getRelated(array $fields)
		{
			// variables
			$model          = $this->create();
			$relations      = [];

			// debug
			//pr('fields', $fields);
			//pr('source', $model->toArray());

			// loop over fields and extract possible relations
			foreach ($fields as &$field)
			{
				/** @var array $keys The property keys, i.e. "post.user.profile" */
				$keys       = explode('.', $field->path);

				/** @var Model $source The current model; this will be updated as we step through the relations */
				$source     = $model;

				/** @var array $target An array of successful key relations, which is built up as we step through the relations */
				$target     = [];

				// debug
				//pr('keys', $keys);

				// Attempt to resolve all relationships from the "post.user.profile" string,
				// by walking the keys one by one:
				//
				// - $source->post
				// - $post->user
				// - $user->profile
				//
				// Each time a relation is found:
				//
				// - update the $target array with the successful key
				// - update the $source property with the relation
				//
				// If a relationship isn't found, exit early
				foreach ($keys as $key)
				{
					// check if the property key 'posts' is in fact a method, i.e. posts()
					// if so, it's probably an Eloquent relationship, that will return a Relation instance
					if(method_exists($source, $key))
					{
						/** @var Relation $relation */
						$relation = $source->$key();
						if($relation instanceof Relation)
						{
							pr(get_class($relation));
							$source     = $relation->getRelated();
							$target     = array_merge($target, [$key]);
						}
						else
						{
							break;
						}
					}
					else
					{
						break;
					}
				}

				// if any of the target path successfully resolved a relationship
				if(count($target))
				{
					// update the Field instance with the successful relations
					$field->relation    = implode('.', $target);
					$field->type        = FieldMeta::RELATION;

					// update the relations object with the correct path
					array_set($relations, $field->relation, true);
				}
			}

			// flatten nested $relations array into "dot.notation.paths"
			$output = array_keys($this->flatten($relations));

			// debug
			//pd('results', ['relations' =>$relations, 'output' =>$output, 'fields' =>$fields]);

			// return
			return $output;

		}

		/**
		 * Loads related items on a model
		 *
		 * @param   mixed       $data
		 * @param   array       $relations
		 */
		public function loadRelated($data, array $relations = null)
		{
			// debug
			pr('load related');

			// get original data source
			$items = $data instanceof AbstractPaginator
				? $data->items()
				: $data;

			// if we have at least one data item, look to eager load
			if(count($items))
			{
				foreach($relations as $relation)
				{
					$data->load($relation);
				}
			}
		}


	// -----------------------------------------------------------------------------------------------------------------
	// PROTECTED METHODS

		/**
		 * Statically calls the model class
		 *
		 * @param   $method         string  The method to call
		 * @param   $rest,...       mixed   unlimited OPTIONAL number of additional variables to display
		 * @return  Collection|Eloquent
		 */
		protected function call($method, $rest = null)
		{
			return call_user_func_array($this->class . '::' . $method, array_slice(func_get_args(), 1));
		}

		/**
		 * Builds the query on the builder instance
		 *
		 * @param   $method         string  The method to call
		 * @param   $rest,...       mixed   unlimited OPTIONAL number of additional variables to display
		 * @return  Collection|Eloquent
		 */
		protected function query($method, $rest = null)
		{
			return call_user_func_array([$this->builder, $method], array_slice(func_get_args(), 1));
		}


	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		protected function flatten($array, $prefix = '')
		{
			$result = array();
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$result = $result + $this->flatten($value, $prefix . $key . '.');
				} else {
					$result[$prefix . $key] = $value;
				}
			}

			return $result;
		}


}
