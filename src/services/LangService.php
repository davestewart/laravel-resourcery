<?php

namespace davestewart\resourcery\services;


use davestewart\resourcery\classes\data\CrudMeta;
use Illuminate\Translation\Translator;

class LangService
{

	// ------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var array $values */
		protected $values;

		/** @var Translator $translator */
		protected $translator;

	// ------------------------------------------------------------------------------------------------
	// INSTANTIATION

		public function initialize(CrudMeta $meta)
		{
			// get initial values
			$this->values = array_filter($meta->toArray(), function($value){ return is_string($value); });

			// TODO initialize values in meta that will need to be translated

			// initialize translator
			$this->translator = app('translator');

			// return
			return $this;
		}


	// ------------------------------------------------------------------------------------------------
	// GROUP GETTERS

		public function validation()
		{
			return trans('resourcery::validation');
		}

		public function message($group, $key, $values = [])
		{
			return $this->trans('messages', $group, $key, $values);
		}


	// ------------------------------------------------------------------------------------------------
	// LOCAL GETTERS

		public function action($key)
		{
			return $this->message('action', $key);
		}

		public function title($key)
		{
			return $this->message('title', $key, $this->values);
		}

		public function prompt($key)
		{
			return $this->message('prompt', $key);
		}

		public function confirm($key)
		{
			return $this->message('confirm', $key);
		}

		public function label($model, $field)
		{
			// check the global array first
			// should this be loaded / cached at the start?
			return $this->trans('labels', $model, $field);
		}

		public function status($key)
		{
			return $this->message('status', $key, $this->values);
		}


	// ------------------------------------------------------------------------------------------------
	// GLOBAL GETTER

		public function trans($file, $group, $key, $values = [])
		{
			return $this->translator->trans("resourcery::$file.$group.$key", $values);
		}

}