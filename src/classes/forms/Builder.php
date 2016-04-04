<?php namespace davestewart\resourcery\classes\forms;

use davestewart\resourcery\classes\forms\Control;
use davestewart\resourcery\classes\forms\Field;
use Form;

class Builder extends Control
{

	public function label(Field $field, array $attrs = [])
	{
		return Form::label($field->id, $field->label, $attrs);
	}

	public function info(Field $field, array $attrs = [])
	{
		return Form::text($field->name, $field->value, array_merge($attrs, ['readonly' => 'readonly']));
	}

	public function text(Field $field, array $attrs = [])
	{
		return Form::input($field->type, $field->name, $field->value, $attrs);
	}

	public function textarea(Field $field, array $attrs = [])
	{
		return Form::textarea($field->name, $field->value, $attrs);
	}

	public function select(Field $field, array $attrs = [])
	{
		return Form::select($field->name, $field->options, $field->value, $attrs);
	}

	public function radios(Field $field, array $attrs = [])
	{
		return $this->group($field, $attrs);
	}

	public function checkboxes(Field $field, array $attrs = [])
	{
		if( ! is_array($field->value) )
		{
			throw new \Exception('checkboxes() expects the passed field->value to be an array');
		}
		return $this->group($field, $attrs);
	}

	public function checkbox(Field $field, array $attrs = [])
	{
		Form::checkbox($field->name, $field->value, !! $field->value);
	}

	public function group(Field $field, array $attrs = [])
	{
		// variables
		$index  = 0;
		$html   = '<div class="controls-group">';

		// loop
		foreach($field->options as $value => $label)
		{
			// common values
			$id     = $field->id . '_' . $value;
			$name   = $field->name;

			// control-specific values
			if($field->type == 'radios')
			{
				$method     = 'radio';
				$checked    = $field->value == null && $index++ == 0 // always check e
								? true
								: $field->value == $value;
			}
			else
			{
				$name       .= '[]';
				$method     = 'checkbox';
				$checked    = in_array($value, $field->value);
			}

			// html
			$html .= '<div class="control-group">';
			$html .= Form::$method($name, $value, $checked, ['id' => $id]);
			$html .= ' ';
			$html .= Form::label($id, $label);
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
	}

}
