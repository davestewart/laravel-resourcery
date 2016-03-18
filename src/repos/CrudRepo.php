<?php namespace davestewart\laravel\crud\repos;

use Illuminate\Database\Eloquent\Collection;

class CrudRepo
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
		 * Returns multiple items
		 *
		 * @param null $limit
		 * @return array|Collection|mixed
		 */
		public function all($limit = null)
		{
			return [];
		}

		/**
		 * Finds a single row in the database
		 *
		 * @param $id
		 * @return mixed
		 */
		public function find($id)
		{
			return (object) [];
		}

		/**
		 * Updates an existing row in the database
		 *
		 * @param $id
		 * @param $data
		 * @return mixed
		 */
		public function update($id, $data)
		{
			return true;
		}

		/**
		 * Adds a new row to the database
		 *
		 * @param $data
		 * @return mixed
		 */
		public function store($data)
		{
			return true;
		}

		/**
		 * Deletes a row from the database
		 *
		 * @param $id
		 * @return mixed
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
		 * @param        $column
		 * @param string $direction
		 * @return CrudRepo
		 */
		public function orderBy($column, $direction = 'asc')
		{
			return $this;
		}

		/**
		 * Adds filters to the query
		 *
		 * @param array $params
		 * @return CrudRepo
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

}