<?php namespace davestewart\resourcery\classes\exceptions;

use Exception;

/**
 * Class InvalidPropertyException
 *
 * Typically thrown when an attempt is made to access a non-existent property by way of __get()
 */
class InvalidPropertyException extends Exception
{
	/**
	 * InvalidPropertyException constructor.
	 *
	 * @param string $name
	 * @param int    $class
	 */
	public function __construct($name, $class)
	{
		$this->message = "Property '$name' does not exist on object '$class'";
	}
}