<?php

namespace davestewart\resourcery
{

	use davestewart\resourcery\classes\validation\Factory;
	use davestewart\resourcery\classes\validation\FieldValidator;
	use Illuminate\Support\ServiceProvider;
	//use Validator;

	/**
	 * Class CrudServiceProvider
	 *
	 * Provides default functionality for Eloquent models
	 *
	 * @package davestewart\resourcery
	 */
	class CrudServiceProvider extends ServiceProvider {

		/**
		 * Register the application services.
		 *
		 * @return void
		 */
		public function register()
		{
			// services
			$this->app->bind('CrudService',     'davestewart\resourcery\services\CrudService');
			$this->app->bind('CrudMetaService', 'davestewart\resourcery\services\CrudMetaService');
			$this->app->bind('CrudLangService', 'davestewart\resourcery\services\LangService');

			// objects
			$this->app->bind('CrudMeta',        'davestewart\resourcery\CrudMeta');
			$this->app->bind('CrudField',       'davestewart\resourcery\CrudField');
			$this->app->bind('CrudRepo',        'davestewart\resourcery\repos\EloquentRepo');
			$this->app->bind('CrudControl',     'davestewart\resourcery\controls\FormControl');

			$this->app->singleton(Factory::class);
		}

		/**
		 * Bootstrap the application services.
		 *
		 * @return void
		 */
		public function boot()
		{
			/*
			// validation
			Validator::resolver(function($translator, $data, $rules, $messages)
			{
				if(isset($data['_resourcery']))
				{
					return new CrudValidator($translator, $data, $rules, $messages);
				}
				return new \Illuminate\Validation\Validator($translator, $data, $rules, $messages);
			});
			*/

			// variables
			$resources = realpath(__DIR__ . '/../') . '/resources/';

			// vendor folders
			$this->loadViewsFrom($resources . 'views', 'resourcery');
			$this->loadTranslationsFrom($resources . 'lang', 'resourcery');

			// publishes
			$this->publishes
			([
				$resources . 'views' => base_path('resources/views/vendor/resourcery'),
				$resources . 'lang' => base_path('resources/lang/vendor/resourcery'),
			]);
		}

	}
}

namespace
{

	use davestewart\resourcery\CrudField;

	if( ! function_exists('control') )
	{
		function control(CrudField $field)
		{
			return \App::make('CrudControl', [$field]);

		}
	}
}