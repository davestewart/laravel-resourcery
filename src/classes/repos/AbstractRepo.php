<?php namespace davestewart\resourcery\classes\repos;

use Illuminate\Database\Eloquent\Collection;

/**
 * AbstractRepo
 *
 * Defines a set of base methods for:
 *
 *  - find, all
 *  - create, read, update, delete
 *  - pagination
 *  - listing fields
 *  - filtering, ordering, sorting
 *  - eager loading
 */
abstract class AbstractRepo
{

	// ------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * Stub initialize method
		 *
		 * @param $options
		 */
		public function initialize($options)
		{
			// implement in subclass
			// use func_get_args() to get more than one parameter
		}


	// ------------------------------------------------------------------------------------------------
	// DATA ACCESS

		/**
		 * Returns a new, empty model
		 *
		 * @param   array       $data
		 * @return  mixed
		 */
		public function create($data = [])
		{
			return (object) [];
		}

		/**
		 * Returns multiple items
		 *
		 * @param   int|null    $limit
		 * @return  array|Collection|mixed
		 */
		public function all($limit = null)
		{
			return [];
		}

		/**
		 * Finds a single row in the database
		 *
		 * @param   int|mixed   $id
		 * @return  mixed
		 */
		public function find($id)
		{
			return (object) [];
		}

		/**
		 * Updates an existing row in the database
		 *
		 * @param   int|mixed   $id
		 * @param   array       $data
		 * @return  mixed
		 */
		public function update($id, $data)
		{
			return (object) [];
		}

		/**
		 * Adds a new row to the database
		 *
		 * @param   array       $data
		 * @return  mixed
		 */
		public function store($data)
		{
			return (object) [];
		}

		/**
		 * Deletes a row from the database
		 *
		 * @param   int|mixed   $id
		 * @return  mixed
		 */
		public function destroy($id)
		{
			return true;
		}


	// ------------------------------------------------------------------------------------------------
	// MODIFIERS

		/**
		 * Updates the order by clause
		 *
		 * @param   string      $column
		 * @param   string      $direction
		 * @return  AbstractRepo
		 */
		public function orderBy($column, $direction = 'asc')
		{
			return $this;
		}

		/**
		 * Adds filters to the query
		 *
		 * @param   array       $params
		 * @return  AbstractRepo
		 */
		public function filter(array $params)
		{
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Gets the fields for different types of input, i.e. hidden ,fillable, etc
		 *
		 * @return string[]
		 */
		public function getFields()
		{
			return [];
		}

		public function getRelated(array $fields)
		{
			return [];
		}

		/**
		 * Loads related items on a model
		 *
		 * @param   mixed       $items
		 * @param   array       $relations
		 */
		public function loadRelated($items, array $relations)
		{
			// implement in subclass
		}

}