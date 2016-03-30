<?php namespace davestewart\laravel\crud\controls;

use davestewart\laravel\crud\CrudField;
use Form;

class FormControl extends CrudControl
{

	protected function make_label(CrudField $field, array $attrs = [])
	{
		return Form::label($field->id, $field->label, $attrs);
	}

	protected function make_info(CrudField $field, array $attrs = [])
	{
		return Form::text($field->name, $field->value, array_merge($attrs, ['readonly' => 'readonly']));
	}

	protected function make_text(CrudField $field, array $attrs = [])
	{
		return Form::input($field->type, $field->name, $field->value, $attrs);
	}

	protected function make_textarea(CrudField $field, array $attrs = [])
	{
		return Form::textarea($field->name, $field->value, $attrs);
	}

	protected function make_select(CrudField $field, array $attrs = [])
	{
		return Form::select($field->name, $field->options, $field->value, $attrs);
	}

	protected function make_radios(CrudField $field, array $attrs = [])
	{
		return $this->make_control_group($field, $attrs);
	}

	protected function make_checkboxes(CrudField $field, array $attrs = [])
	{
		if( ! is_array($field->value) )
		{
			throw new \Exception('make_checkboxes() expects the passed field->value to be an array');
		}
		return $this->make_control_group($field, $attrs);
	}

	protected function make_checkbox(CrudField $field, array $attrs = [])
	{
		Form::checkbox($field->name, $field->value, !! $field->value);
	}

	protected function make_control_group(CrudField $field, array $attrs = [])
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
