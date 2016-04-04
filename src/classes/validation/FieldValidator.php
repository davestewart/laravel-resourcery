<?php namespace davestewart\resourcery\classes\validation;

use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

/**
 * Custom Resourcery Validation class
 *
 * - Loads custom field-centric validation messages
 * - Fixes getInlineMessage() bug which doesn't return the sub-element for array messages
 */
class FieldValidator extends Validator
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
