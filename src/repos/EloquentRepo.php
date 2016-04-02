<?php namespace davestewart\resourcery\repos;

use Eloquent;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Request;

/**
 * Class CrudModel
 * @package davestewart\resourcery
 */
class EloquentRepo extends CrudRepo
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
		 */
		public function __get($name)
		{
			if(property_exists($this, $name))
			{
				return $this->$name;
			}
			return null;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Returns the fields for an Eloquent model (used mainly by the CrudMeta constructor)
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
		 * Loads related items on a model
		 *
		 * @param   mixed       $data
		 * @param   array       $relations
		 */
		public function loadRelated($data, array $relations)
		{
			// get original data source
			$items = $data instanceof AbstractPaginator
				? $data->items()
				: $data;

			// if we have at least one data item, look to eager load
			if(count($items))
			{
				$item  = $items[0];
				foreach($relations as $relation)
				{
					if( method_exists($item, $relation) && ! isset($item->$relation) )
					{
						$data->load($relation);
					}
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

}
