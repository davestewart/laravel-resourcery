<?php namespace davestewart\laravel\crud\meta;

use Illuminate\Support\Fluent;

/**
 * Class Meta
 * @package app\Services\Meta
 */
class Meta extends Fluent
{

	public function merge($key, $values)
	{
		$this->$key = array_merge($this->$key, $values);
		return $this;
	}
}