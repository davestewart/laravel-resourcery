<?php namespace davestewart\laravel\crud\repos;

use Eloquent;
use Illuminate\Support\Collection;
use Request;

/**
 * Class CrudModel
 * @package davestewart\laravel\crud
 */
class EloquentRepo implements CrudRepo
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
		 * Initialize the repo with a model's class
		 *
		 * @param   string      $class      A model class
		 * @return  array
		 * @throws  \Exception
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
		 * Get all items
		 *
		 * @param   string      $orderBy
		 * @param   string      $orderDir
		 * @param   int         $limit
		 * @param   array|null  $filter
		 * @return  Collection
		 */
		public function index($orderBy = 'id', $orderDir = 'asc', $limit = null, array $filter = null)
		{
			$this->builder->orderBy($orderBy, $orderDir);
			if($filter)
			{
				$this->filter($filter);
			}
			return $limit == null
				? $this->builder->get()
				: $this->paginate($limit);
		}

		/**
		 * Finds and / or returns an eloquent model
		 *
		 * @param   Eloquent|int    $id
		 * @return  Eloquent
		 */
		public function find($id)
		{
			return $this->builder->find($id);
		}

		/**
		 * @param   int             $id
		 * @param   array           $data
		 * @return  Eloquent
		 */
		public function update($id, $data)
		{
			return $this->find($id)->update($data);
		}

		/**
		 * @param   array           $data
		 * @return  mixed
		 */
		public function store($data)
		{
			return $this->call('create', $data);
		}

		/**
		 * @param   int             $id
		 * @return  Eloquent
		 */
		public function destroy($id)
		{
			return $this->find($id)->delete();
		}


	// -----------------------------------------------------------------------------------------------------------------
	// DATA ACCESS

		public function filter(array $params)
		{
			// exit early if no params
			if(count($params) == 0)
			{
				return $this;
			}

			// filter
			$fillable   = $this->getFields('all');
			$fields     = array_combine($fillable, $fillable);
			$where      = array_intersect_key($params, $fields);

			// query
			$this->builder->where($where);

			// return
			return $this;
		}

		public function paginate($limit)
		{
			return $this->builder
				->paginate($limit)
				->appends(Request::except('page'));
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MAGIC

		/**
		 * Forward all method calls to builder
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
		 * Return existing properties
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
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Get the fields for an Eloquent model (used mainly by the CrudMeta constructor)
		 *
		 * @param null $name
		 * @return \string[] An array of field names for different types of view
		 */
		public function getFields($name = null)
		{
			// param
			$types  = $name == null ? ['all', 'visible', 'fillable', 'hidden'] : [$name];

			// variables
			$model  = \App::make($this->class);
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
