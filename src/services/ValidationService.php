<?php namespace davestewart\resourcery\services;

use App;
use davestewart\resourcery\classes\validation\Factory;
use davestewart\resourcery\classes\validation\FieldValidator;
use Illuminate\Validation\Validator;

/**
 * ValidationService
 *
 * Special validation class that essentially collates two sets of error
 * messages; one for the page and one for the controls
 *
 * @property FieldValidator $field
 * @property Validator $page
 * @property array $errors
 */
class ValidationService
{
	
	// ------------------------------------------------------------------------------------------------
	// properties
	
		/** @var LangService */
		protected $lang;
	
		/** @var FieldValidator $field */
		protected $field;
		
		/** @var Validator $page */
		protected $page;

	
	// ------------------------------------------------------------------------------------------------
	// setup
	
		public function initialize(LangService $lang, array $rules, array $messages, array $attributes)
		{
			$this->lang = $lang;
			$this->page = $this->factory(Validator::class);
			return $this;
		}

	
	// ------------------------------------------------------------------------------------------------
	// accessors
	
		public function setRules($value)
		{
			$this->page->setRules($value);
			return $this;
		}
	
		public function setMessages($value)
		{
			$this->page->setCustomMessages($value);
			return $this;
		}
	
		public function setAttributes($value)
		{
			$this->page->setAttributeNames($value);
			return $this;
		}

	/**
	 * Validate input
	 * 
	 * @param array $input
	 * @param array $rules
	 * @return bool
	 */
	public function validate(array $input, array $rules)
	{
		// initialize page-level validation
		if( ! $this->page )
		{
			$this->page = $this->factory(Validator::class);
			$this->page->setRules($rules);
		}

		// update validator
		$this->page->setData($input);

		// validate
		if($this->page->fails())
		{
			// initialize field-level validation
			if( ! $this->field )
			{
				$this->field = $this->factory(FieldValidator::class);
				$this->field->setRules($rules);
			}

			// update validator
			$this->field->setData($input);
			$this->field->fails();

			return false;
		}

		return true;
	}
	
	public function __get($name)
	{
		if(property_exists($this, $name))
		{
			return $this->$name;
		}
		if($name == 'errors')
		{
			return $this->page->errors();
		}
		
	}

	protected function factory($class)
	{
		return App::make(Factory::class)->makeCustom($class);
	}
}