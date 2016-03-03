<?php namespace davestewart\laravel\crud\repos;

use davestewart\laravel\crud\CrudMeta;
use davestewart\laravel\crud\repos\CrudRepo;
use Eloquent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

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
			$this->class = $class;
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// DATA ACCESS

		/**
		 * Get all items
		 * 
		 * @param int               $limit
		 *
		 * @return Collection
		 */
		public function all($limit = null)
		{
			return $limit == null
				? $this->query('all')
				: $this->query('paginate', $limit);
		}

		/**
		 * Finds and / or returns an eloquent model
		 *
		 * @param   Eloquent|int    $id
		 * @return  Eloquent
		 */
		public function find($id)
		{
			return $this->query('findOrFail', $id);
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
			return $this->query('create', $data);
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
	// UTILITIES

		/**
		 * Get the fields for an Eloquent model (used mainly by the CrudMeta constructor)
		 *
		 * @return  string[]                An array of field names for different types of view
		 */
		public function getFields()
		{
			/** @var Eloquent $model */
			$model          = \App::make($this->class);
			$data           =
			[
				'all'		=> $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable()),
				'visible'	=> $model->getVisible(),
				'fillable'	=> $model->getFillable(),
				'hidden'    => $model->getHidden(),
			];

			// return
			return $data;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// PROTECTED METHODS

		/**
		 * Query calls on arbitrary models
		 *
		 * @param   $method         string  The method to call
		 * @param   $parameters,... mixed   unlimited OPTIONAL number of additional variables to display
		 * @return  Collection|Eloquent
		 */
		protected function query($method, $parameters = null)
		{
			$callable = $this->class . '::' . $method;
			if(func_num_args() > 1)
			{
				return call_user_func_array($callable, array_slice(func_get_args(), 1));
			}
			return call_user_func($callable);
		}

}