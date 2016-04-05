<?php

namespace davestewart\resourcery
{

	use davestewart\resourcery\classes\validation\Factory;
	use Illuminate\Support\ServiceProvider;

	/**
	 * ResourceryServiceProvider
	 *
	 * Sets up the Resourcery package
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
			$this->mergeConfigFrom($resources . 'config/config.php', 'resourcery');
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

	use davestewart\resourcery\classes\forms\Control;
	use davestewart\resourcery\classes\forms\Field;

	if( ! function_exists('control') )
	{
		function control(Field $field)
		{
			return \App::make(Control::class, [$field]);

		}
	}
}