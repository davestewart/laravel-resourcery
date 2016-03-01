<?php namespace davestewart\laravel\crud;

use davestewart\laravel\crud\CrudRepository;
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
		$this->app->bind('CrudField',   'davestewart\laravel\crud\CrudField');
		$this->app->bind('CrudRepo',    'davestewart\laravel\crud\repos\EloquentRepo');
	}

}
