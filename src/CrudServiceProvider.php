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
		 * Bootstrap the application services.
		 *
		 * @return void
		 */
		public function boot()
		{
			//
		}

		/**
		 * Register the application services.
		 *
		 * @return void
		 */
		public function register()
		{
			$this->app->bind('CrudService', 'davestewart\laravel\crud\CrudService');
			$this->app->bind('CrudRepo',    'davestewart\laravel\crud\repos\EloquentRepo');
			$this->app->bind('CrudField',   'davestewart\laravel\crud\CrudField');
			$this->app->bind('CrudControl', 'davestewart\laravel\crud\controls\FormControl');
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