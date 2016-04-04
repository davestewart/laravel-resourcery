<?php

namespace davestewart\resourcery
{

	use davestewart\resourcery\classes\validation\Factory;
	use Illuminate\Support\ServiceProvider;

	/**
	 * Class CrudServiceProvider
	 *
	 * Provides default functionality for Eloquent models
	 *
	 * @package davestewart\resourcery
	 */
	class ResourceryServiceProvider extends ServiceProvider {

		/**
		 * Register the application services.
		 *
		 * @return void
		 */
		public function register()
		{
			// services
			$this->app->bind('CrudService',     'davestewart\resourcery\services\CrudService');
			$this->app->bind('MetaService',     'davestewart\resourcery\services\MetaService');
			$this->app->bind('CrudLangService', 'davestewart\resourcery\services\LangService');

			// objects
			$this->app->bind('CrudMeta',        'davestewart\resourcery\classes\data\CrudMeta');
			$this->app->bind('CrudRepo',        'davestewart\resourcery\classes\repos\EloquentRepo');
			$this->app->bind('CrudField',       'davestewart\resourcery\classes\forms\CrudField');
			$this->app->bind('CrudControl',     'davestewart\resourcery\classes\forms\FormControl');

			$this->app->singleton(Factory::class);
		}

		/**
		 * Bootstrap the application services.
		 *
		 * @return void
		 */
		public function boot()
		{
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

	use davestewart\resourcery\classes\forms\CrudField;

	if( ! function_exists('control') )
	{
		function control(CrudField $field)
		{
			return \App::make('CrudControl', [$field]);

		}
	}
}