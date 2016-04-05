<?php namespace davestewart\resourcery\classes\meta;


class ControlMeta
{
	public $type;
	public $callback;

	public function __construct($type = null, $callback = null)
	{
		$this->type     = $type;
		$this->callback = $callback;
	}

	public static function create($data, $action)
	{
		// single control type
		if(strstr($data, ':') === FALSE)
		{
			return new ControlMeta($data);
		}

		// compound control type
		else
		{
			// grab controls
			preg_match_all('/(\w+):(\w+)/', $data, $matches);
			$controls = array_combine($matches[1], $matches[2]);

			// single entry
			if(count($controls) === 1)
			{
				return new ControlMeta($matches[1][0], $matches[2][0]);
			}
			else
			{
				$options = isset($controls['options']) ? $controls['options'] : null;
				return new ControlMeta($controls[$action], $options);
			}
		}

	}
}