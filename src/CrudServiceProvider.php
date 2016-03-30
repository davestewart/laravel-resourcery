<?php

namespace davestewart\laravel\crud
{
	use Illuminate\Support\ServiceProvider;

	/**
	 * Class CrudServiceProvider
	 *
	 * Provides default functionality for Eloquent models
	 *
	 * @package davestewart\laravel\crud
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
			$this->app->bind('CrudService',     'davestewart\laravel\crud\services\CrudService');
			$this->app->bind('CrudMetaService', 'davestewart\laravel\crud\services\CrudMetaService');
			$this->app->bind('CrudLangService', 'davestewart\laravel\crud\services\CrudLangService');

			// objects
			$this->app->bind('CrudMeta',        'davestewart\laravel\crud\CrudMeta');
			$this->app->bind('CrudField',       'davestewart\laravel\crud\CrudField');
			$this->app->bind('CrudRepo',        'davestewart\laravel\crud\repos\EloquentRepo');
			$this->app->bind('CrudControl',     'davestewart\laravel\crud\controls\FormControl');
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

	use davestewart\laravel\crud\CrudField;

	if( ! function_exists('control') )
	{
		function control(CrudField $field)
		{
			return \App::make('CrudControl', [$field]);

		}
	}
}