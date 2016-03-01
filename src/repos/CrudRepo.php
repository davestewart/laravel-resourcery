<?php namespace davestewart\laravel\crud\repos;

use davestewart\laravel\crud\CrudMeta;

interface CrudRepo
{
	public function initialize(CrudMeta $meta);

	public function all($limit = null, array $related = null);

	public function find($id);

	public function update($id, $data);

	public function store($data);

	public function destroy($id);

}