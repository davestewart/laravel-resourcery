<?php namespace davestewart\laravel\crud\classes;

use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

class CrudValidator extends Validator
{
	public function __construct(array $data, array $rules)
	{
		/**  @var Translator $trans */
		$trans  = \App::make('translator');
		$trans->load('crud', 'validation', 'en');

		// parent
		parent::__construct($trans, $data, $rules);
	}

	protected function getMessage($attribute, $rule)
	{

		pr($rule);

		$lowerRule = snake_case($rule);

		$inlineMessage = $this->getInlineMessage($attribute, $lowerRule);

		// First we will retrieve the custom message for the validation rule if one
		// exists. If a custom validation message is being used we'll return the
		// custom message, otherwise we'll keep searching for a valid message.
		if ( ! is_null($inlineMessage))
		{
			return $inlineMessage;
		}

		$customKey = "crud::validation.custom.{$attribute}.{$lowerRule}";

		$customMessage = $this->translator->trans($customKey);

		// First we check for a custom defined validation message for the attribute
		// and rule. This allows the developer to specify specific messages for
		// only some attributes and rules that need to get specially formed.
		if ($customMessage !== $customKey)
		{
			return $customMessage;
		}

		// If the rule being validated is a "size" rule, we will need to gather the
		// specific error message for the type of attribute being validated such
		// as a number, file or string which all have different message types.
		elseif (in_array($rule, $this->sizeRules))
		{
			return $this->getSizeMessage($attribute, $rule);
		}

		// Finally, if no developer specified messages have been set, and no other
		// special messages apply for this rule, we will just pull the default
		// messages out of the translator service for this validation rule.
		$key = "crud::validation.{$lowerRule}";

		if ($key != ($value = $this->translator->trans($key)))
		{
			return $value;
		}

		return $this->getInlineMessage(
			$attribute, $lowerRule, $this->fallbackMessages
		) ?: $key;

	}

	/**
	 * Get the inline message for a rule if it exists.
	 *
	 * @param  string  $attribute
	 * @param  string  $lowerRule
	 * @param  array   $source
	 * @return string
	 */
	protected function getInlineMessage($attribute, $lowerRule, $source = null)
	{
		$source = $source ?: $this->customMessages;

		$keys = array("{$attribute}.{$lowerRule}", $lowerRule);

		// First we will check for a custom message for an attribute specific rule
		// message for the fields, then we will check for a general custom line
		// that is not attribute specific. If we find either we'll return it.
		foreach ($keys as $key)
		{
			if (isset($source[$key])) return $source[$key];
		}
	}

	/**
	 * Get the proper error message for an attribute and size rule.
	 *
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @return string
	 */
	protected function getSizeMessage($attribute, $rule)
	{
		$lowerRule = snake_case($rule);

		// There are three different types of size validations. The attribute may be
		// either a number, file, or string so we will check a few things to know
		// which type of value it is and return the correct line for that type.
		$type = $this->getAttributeType($attribute);

		$key = "crud::validation.{$lowerRule}.{$type}";

		return $this->translator->trans($key);
	}
}
