<?php namespace davestewart\resourcery\classes\validation;

use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

/**
 * Validation class with custom messages
 *
 * Also fixes a bug where array messages for size attributes don't return the correct element
 */
class CrudValidator extends Validator
{
	public function __construct(Translator $translator, array $data, array $rules)
	{
		parent::__construct($translator, $data, $rules, trans('resourcery::validation'));
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
