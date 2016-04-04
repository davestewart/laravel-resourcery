<?php namespace davestewart\resourcery\classes\validation;

use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Custom Validation Class Factory
 *
 * @see \Illuminate\Validation\Factory
 * @see \Illuminate\Validation\ValidationServiceProvider
 *
 * @package davestewart\resourcery\classes\validation
 */
class Factory extends \Illuminate\Validation\Factory
{

	/**
	 * Custom Validator Factory constructor
	 *
	 * @param TranslatorInterface $translator
	 * @param Container           $container
	 */
	function __construct(TranslatorInterface $translator, Container $container)
	{
		parent::__construct($translator, $container);
		$this->setPresenceVerifier(app('validation.presence'));
	}

	/**
	 * Create a new Validator instance.
	 *
	 * @param   array       $class
	 * @param   array       $messages
	 * @param   array       $attributes
	 * @return  Validator
	 */
	public function makeCustom($class = Validator::class, array $messages = [], array $attributes = [])
	{
		/** @var Validator $validator */
		$validator = new $class($this->translator, [], []);
		$validator->setCustomMessages($messages);
		$validator->setAttributeNames($attributes);

		return $this->initialize($validator);
	}

	/**
	 * Initialize any Validator instance from outside of the Factory
	 *
	 * @param Validator $validator
	 * @return Validator
	 */
	public function initialize(Validator $validator)
	{
		if ( ! is_null($this->verifier) ){
			$validator->setPresenceVerifier($this->verifier);
		}
		if ( ! is_null($this->container) ){
			$validator->setContainer($this->container);
		}
		$this->addExtensions($validator);

		return $validator;
	}

}