<?php namespace davestewart\laravel\crud\meta;

/**
 * Class CrudMeta
 * @package app\Services\Meta
 */
class CrudMeta
{

	/**
	 * The index route of this controller, which will load first time,
	 * and users will be redirected to after updating the model
	 *
	 * @var string
	 */
	public $route			= 'items';

	/**
	 * The namespaced class of the Model which will be queried in this controller
	 *
	 * @var string
	 */
	public $model			= '\Models\ItemModel';

	/**
	 * Paths to the view files that will render your model's data (defaults to
	 * the package's catch-all templates)
	 *
	 * @var array|object
	 */
	public $view				=
	[
		'path'					=> 'vendor.crud',
		'names'					=>
		[
			'create'			=> 'create',
			'index'				=> 'index',
			'show'				=> 'show',
			'edit'				=> 'edit'
		]
	];

	// TODO Add pagination preferences here, or should this be on the repo?

	/**
	 * Constructor function for CrudMeta
	 *
	 * @param   string  $route      The base route of this controller, i.e. dashboard/users
	 * @param   string  $model      The namespaced path to the model, i.e. App\Models\User
	 * @param   string  $viewPath   The base path of all REST views, i.e. common.users
	 * @param   null $viewNames		An optional array of view names that will be merged with the default,
	 *                          	i.e. ['delete' => 'delete'] (include a leading slash to replace the base path)
	 */
	public function __construct($route, $model, $viewPath = NULL, $viewNames = NULL)
	{
		// setup
		$this->view				= (object) $this->view;

		// required parameters
		$this->model			= $model;
		$this->route			= $route;

		// optional parameters
		if($viewPath)
			$this->view->path	= $viewPath;
		if(is_array($viewNames))
			$this->view->names	= array_merge($this->view->names, $viewNames);
	}

	public function data()
	{
		$data = (object) $this;
		return $data;
	}

}