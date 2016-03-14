<?php namespace davestewart\laravel\crud\repos;

use davestewart\laravel\crud\CrudMeta;

interface CrudRepo
{
	public function initialize($class);

	public function index($orderBy = 'id', $orderDir = 'asc', $limit = null, array $filter = null);

	public function find($id);

	public function update($id, $data);

	public function store($data);

	public function destroy($id);

	public function getFields();

}