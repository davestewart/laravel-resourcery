<?php

namespace davestewart\resourcery
{
	use Illuminate\Support\ServiceProvider;

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
			$this->app->bind('CrudLangService', 'davestewart\resourcery\services\CrudLangService');

			// objects
			$this->app->bind('CrudMeta',        'davestewart\resourcery\CrudMeta');
			$this->app->bind('CrudField',       'davestewart\resourcery\CrudField');
			$this->app->bind('CrudRepo',        'davestewart\resourcery\repos\EloquentRepo');
			$this->app->bind('CrudControl',     'davestewart\resourcery\controls\FormControl');
		}

		/**
		 * Bootstrap the application services.
		 *
		 * @return void
		 */
		public function boot()
		{
			// vendor folders
			$this->loadTranslationsFrom(__DIR__ . '/lang', 'crud');
			$this->loadViewsFrom(__DIR__ . '/views', 'crud');

			// publishes
			$this->publishes
			([
				__DIR__ . '/views' => base_path('resources/views/vendor/crud'),
				__DIR__ . '/lang' => base_path('resources/lang/vendor/crud'),
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