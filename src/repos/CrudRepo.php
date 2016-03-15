<?php namespace davestewart\laravel\crud\repos;

use Illuminate\Database\Eloquent\Collection;

interface CrudRepo
{
	public function initialize($class);

	/**
	 * @param null $limit
	 * @return array|Collection|mixed
	 */
	public function index($limit = null);

	/**
	 * @param $id
	 * @return mixed
	 */
	public function find($id);

	/**
	 * @param $id
	 * @param $data
	 * @return mixed
	 */
	public function update($id, $data);

	/**
	 * @param $data
	 * @return mixed
	 */
	public function store($data);

	/**
	 * @param $id
	 * @return mixed
	 */
	public function destroy($id);

	/**
	 * @param        $column
	 * @param string $direction
	 * @return CrudRepo
	 */
	public function orderBy($column, $direction = 'asc');

	/**
	 * @param array $params
	 * @return CrudRepo
	 */
	public function filter(array $params);

	/**
	 * @return string[]
	 */
	public function getFields();

}