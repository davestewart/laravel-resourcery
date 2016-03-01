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

		public function __construct()
		{

		}

		public function initialize(CrudMeta $meta)
		{
			$this->class = $meta->class;
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// DATA ACCESS

		/**
		 * Get all items
		 * 
		 * @param int        $limit
		 * @param array|null $related
		 *
		 * @return Collection
		 */
		public function all($limit = null, array $related = null)
		{
			if($related)
			{
				$query = $this->query('with', $related);
				return $limit == null
					? $query->all()
					: $query->paginate($limit);
			}
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
			return $id instanceof Eloquent
				? $id
				: $this->query('findOrFail', $id);
		}

		/**
		 * @param   Eloquent|int    $id
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
			return $this->query('store', $data);
		}

		/**
		 * @param   Eloquent|int    $id
		 * @return  Eloquent
		 */
		public function destroy($id)
		{
			return $this->find($id)->delete();
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
		protected function query($method, &$parameters = null)
		{
			$model = $this->class . '::' . $method;
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

		protected function paginateRelated($query, $limit, $related)
		{
			// Get all records.
			$results    = $this->query($query);

			// Get pagination information and slice the results.
			$total      = count($results);
			$start      = (Paginator::getCurrentPage() - 1) * $limit;
			$sliced     = array_slice($results, $start, $limit);

			// Eager load the relation.
			$collection = $this->query('hydrate', $sliced);
			$collection->load($related);

			// Create a paginator instance.
			return Paginator::make($collection->all(), $total, $limit);
		}

}