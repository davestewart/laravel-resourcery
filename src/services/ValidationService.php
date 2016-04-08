<?php namespace davestewart\resourcery\services;

use App;
use davestewart\resourcery\classes\validation\Factory;
use davestewart\resourcery\classes\validation\FieldValidator;
use Illuminate\Validation\Validator;

/**
 * ValidationService
 *
 * Special validation class that essentially collates two sets of error messages;
 * one which is form-centric, i.e. "The name field is required" and one which is
 * field-centric, i.e. "This field is required".
 *
 * The main form error messages are loaded from the standard application validation
 * config, with the fields error messages being loaded by the FieldValidator instance
 * from the package config.
 * 
 * The form validation instance is used as the main validation source until there is
 * an error, then a fields validation instance is instantiated and run, with the sole
 * purpose of generating field-centric error messages.
 *
 * @property FieldValidator $fields
 * @property Validator      $form
 * @property array          $errors
 */
class ValidationService
{
	
	// ------------------------------------------------------------------------------------------------
	// properties
	
		/** @var LangService */
		protected $lang;
	
		/** @var FieldValidator $fields */
		protected $fields;
		
		/** @var Validator $page */
		protected $page;

	
	// ------------------------------------------------------------------------------------------------
	// setup
	
		public function initialize(LangService $lang, array $rules, array $messages, array $attributes)
		{
			$this->lang = $lang;
			$this->form = $this->factory(Validator::class);
			return $this;
		}

	
	// ------------------------------------------------------------------------------------------------
	// accessors
	
		public function setRules($value)
		{
			$this->form->setRules($value);
			return $this;
		}
	
		public function setMessages($value)
		{
			$this->form->setCustomMessages($value);
			return $this;
		}
	
		public function setAttributes($value)
		{
			$this->form->setAttributeNames($value);
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
		// initialize form-level validation
		if( ! $this->form )
		{
			$this->form = $this->factory(Validator::class);
			$this->setRules($rules);
		}

		// update validator
		$this->form->setData($input);

		// validate
		if($this->form->fails())
		{
			// initialize field-level validation
			if( ! $this->fields )
			{
				$this->fields = $this->factory(FieldValidator::class);
			}

			// update validator
			$this->fields->setRules($this->form->getRules());
			$this->fields->setCustomMessages($this->form->getCustomMessages());
			$this->fields->setAttributeNames($this->form->getCustomAttributes());
			$this->fields->setData($input);
			$this->fields->fails();

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
			return $this->form->errors();
		}
		
	}

	protected function factory($class)
	{
		return App::make(Factory::class)->makeCustom($class);
	}
}