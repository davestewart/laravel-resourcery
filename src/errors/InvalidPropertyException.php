<?php

namespace davestewart\laravel\crud\errors;


class InvalidPropertyException extends \Exception
{
	public function __construct($name, $class)
	{
		$this->message = "Property `$name` does not exist on object `$class`";
	}
}