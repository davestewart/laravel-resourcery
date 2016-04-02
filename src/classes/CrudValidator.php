<?php namespace davestewart\resourcery\classes;

use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

class CrudValidator extends Validator
{
	public function __construct(array $data, array $rules)
	{
		/**  @var Translator $trans */
		$trans      = \App::make('translator');

		/** @var array $messages */
		$messages   = trans('resourcery::validation');

		// parent
		parent::__construct($trans, $data, $rules, $messages);

		// set up machinery for unique validation
		$app = app();
		if (isset($app['validation.presence'])) {
			$this->setPresenceVerifier($app['validation.presence']);
		}
	}

	protected function getInlineMessage($attribute, $lowerRule, $source = null)
	{
		$message = parent::getInlineMessage($attribute, $lowerRule, $source);
		if (is_array($message))
		{
			$type = $this->getAttributeType($attribute);
			return $message[$type];
		}
		return $message;
	}

}
