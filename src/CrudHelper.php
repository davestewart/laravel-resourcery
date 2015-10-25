<?php namespace davestewart\laravel\crud;

use davestewart\laravel\crud\meta\CrudMeta;
use davestewart\laravel\crud\meta\ModelMeta;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\RedirectResponse;
use Input;
use Redirect;
use Request;
use Response;
use Session;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Validator;
use View;

/**
 * Class CrudHelper
 *
 * Magic response properties:
 *
 * @property 	\Symfony\Component\HttpFoundation\Response result
 * @property 	\Symfony\Component\HttpFoundation\JsonResponse json
 * @property 	\Illuminate\Support\Facades\View view
 *
 * @package		app\Http\Controllers
 */
class CrudHelper
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		// meta
		protected $meta;
		protected $accessors    = ['route', 'model', 'viewPath', 'viewNames', 'action', 'ajax', 'singular', 'plural'];

		// request values
		/**	@var string */
		protected $action;

		/** @var bool */
		protected $ajax;

		/** @var array **/
		protected $input;

		// make this $_response

		/** @var \Symfony\Component\HttpFoundation\Response */
		protected $response;

		// public properties
		/** @var CrudRepository */
		public $repo;

		/** @var \Illuminate\Contracts\Support\Arrayable */
		public $data;

		/** @var array */
		public $errors;

		/** @var string */
		public $message;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * Constructor function
		 *
		 * @param string|CrudMeta	$route
		 * @param string|ModelMeta	$model
		 * @param string			$view
		 * @param string			$singular
		 * @param string			$plural
		 * @param array				$viewNames
		 */
		public function __construct($route = null, $model = null, $view = null, $singular = null, $plural = null, array $viewNames = null)
		{
			// properties
			$this->meta = (object) ['crud' => null, 'model' => null];
			$this->ajax = \Request::ajax();

			// parameters
			$this->initialize($route, $model, $view, $singular, $plural, $viewNames);
		}

		/**
		 * Setup function
		 *
		 * @param string|CrudMeta	$route
		 * @param string|ModelMeta	$model
		 * @param string			$view
		 * @param string			$singular
		 * @param string			$plural
		 * @param array				$viewNames
		 */
		protected function initialize($route, $model, $view, $singular = null, $plural = null, array $viewNames = null)
		{
			// special case if meta objects are passed in
			if($route instanceof CrudMeta)
			{
				// override
				$this->meta->crud	= $route;
				$this->repo			= new CrudRepository($this->meta->crud->model);

				// model
				if($model instanceof ModelMeta)
				{
					$this->meta->model = $model;
				}
			}

			// parameters
			else
			{
				$this->meta->crud	= new CrudMeta($route, $model, $view, $viewNames);
				$this->meta->model	= new ModelMeta($singular, $plural);
				$this->repo			= new CrudRepository($model);
			}

			// optional properties
			if($singular)
				$this->meta->model->singular = $singular;
			if($plural)
				$this->meta->model->plural = $plural;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		/**
		 * Chainable utility to set properties
		 *
		 * @param	string|array	$name	The name of the property to set, or an array of name:value pairs
		 * @param	mixed			$value	The value of the property to set
		 * @return self
		 */
		public function set($name, $value)
		{
			// multiple properties
			if(is_array($name))
			{
				foreach($name as $key => $value)
				{
					$this->set($key, $value);
				}
				return $this;
			}


			// single property
			if(in_array($name, $this->accessors))
			{
				switch($name)
				{
					case 'route':
						$this->meta->crud->route = $value;
						break;

					case 'model':
						if($value instanceof CrudRepository)
						{
							$this->repo = $value;
							$this->meta->crud->model = $value->name;
						}
						else
						{
							$this->repo = new CrudRepository($value);
							$this->meta->crud->model = $value;
						}
						break;

					case 'viewPath':
						$this->meta->crud->view->path = $value;
						break;

					case 'viewNames':
						array_merge($this->meta->crud->viewNames, $value);
						break;

					case 'action':
					case 'ajax':
						$this->$name = $value;
						break;

					case 'singular':
					case 'plural':
						$this->meta->model->$name = $value;
						break;
				}
			}

			// error if invalid
			else
			{
				throw new InvalidParameterException("Invalid property name '$name''");
			}

			// chain
			return $this;
		}

		/**
		 * @param string $name
		 * @return self|\Symfony\Component\HttpFoundation\Response
		 * @throws \Exception
		 */
		public function __get($name)
		{
			switch($name)
			{
				case 'result':
					return $this->makeResult();
					break;

				case 'json':
					return $this->makeJson();
					break;

				case 'view':
					return $this->makeview();
					break;
			}

			// error if invalid property
			throw new \Exception("Property '$name' does not exist");
		}


	// -----------------------------------------------------------------------------------------------------------------
	// CONTROLLER METHODS

		/**
		 * Display a listing of the resource.
		 *
		 * @return self
		 */
		public function index()
		{
			$this->action	= 'index';
			$this->data		= $this->repo->query('all');
			return $this;
		}
	
		/**
		 * Show the form for creating a new resource.
		 *
		 * @return self
		 */
		public function create()
		{
			$this->action	='create';
			return $this;
		}

		/**
		 * Store a newly created resource in storage.
		 *
		 * @param bool $force	An optional boolean to force saving (skipping validation). Defaults to false
		 * @return self
		 */
		public function store($force = false)
		{
			// properties
			$this->action	= 'store';

			// validate
			if($force === false)
			{
				$this->validate();
			}

			// if successful
			if( ! $this->errors )
			{
				// create and save model
				$this->data			= $this->repo->query('create', $this->input);

				// update response
				$this->response		= Redirect::to($this->meta->crud->route);
				$this->message		= 'Successfully created ' . $this->meta->model->singular;
			}

			// return
			return $this;
		}
	
		/**
		 * Display the specified resource.
		 *
		 * @param  int  $id
		 * @return self
		 */
		public function show($id)
		{
			$this->action		= 'show';
			$this->data			= $this->repo->find($id);
			return $this;
		}
	
		/**
		 * Show the form for editing the specified resource.
		 *
		 * @param  int  $id
		 * @return self
		 */
		public function edit($id)
		{
			$this->action		= 'edit';
			$this->data			= $this->repo->find($id);
			return $this;
		}
	
		/**
		 * Update the specified resource in storage.
		 *
		 * @param  int  $id
		 * @param  bool $force	An optional boolean to force saving (skipping validation). Defaults to false
		 * @return self
		 */
		public function update($id, $force = false)
		{
			// properties
			$this->action	= 'update';

			// variables
			if($force === false)
			{
				$this->validate();
			}

			// if successful
			if( ! $this->errors )
			{
				// update model
				$this->data = $this->repo->find($id);
				$this->data->update($this->input);

				// update response
				$this->message	= 'Successfully updated ' . $this->meta->model->singular;
				$this->response	= Redirect::to($this->meta->crud->route);
			}

			// return
			return $this;
		}
	
		/**
		 * Remove the specified resource from storage.
		 *
		 * @param  int  $id
		 * @return self
		 */
		public function destroy($id)
		{
			// delete
			$this->repo->destroy($id);

			// update response
			$this->action	= 'destroy';
			$this->message	= 'Successfully deleted ' . $this->meta->model->singular;
			$this->response = Redirect::to($this->meta->crud->route);

			// return;
			return $this;
		}

		public function view($name, $data = null)
		{
			$this->action = $name;
			if($data)
			{
				$this->data = $data;
			}
			return $this->makeView();
		}

	// -----------------------------------------------------------------------------------------------------------------
	// RESPONSES

		/**
		 * Builds and returns a response object
		 *
		 * @return View|RedirectResponse|\Symfony\Component\HttpFoundation\JsonResponse
		 */
		protected function makeResult()
		{
			if($this->ajax)
			{
				return $this->makeJson();
			}
			else if($this->response instanceof RedirectResponse)
			{
				if($this->message)
				{
					Session::flash('message', $this->message);
				}
				return $this->response;
			}
			else
			{
				return $this->makeView();
			}
		}

		/**
		 * Builds and returns a View response
		 *
		 * @return View
		 */
		protected function makeView()
		{
			if( ! $this->action )
			{
				throw new \Exception('A view cannot be shown as a view/action has not yet been set. Call one of the 4 main crud methods(index, show, create, edit), or view($name) to force a view' );
			}
			return View
				::make($this->meta->crud->view->path . '.' . $this->meta->crud->view->names[$this->action])
				->with($this->getViewData());
		}

		/**
		 * Builds and returns a JSON response
		 *
		 * @return \Symfony\Component\HttpFoundation\JsonResponse
		 */
		protected function makeJson()
		{
			// if we have errors, return an error response
			if($this->errors)
			{
				$data =
				[
					'message'	=> $this->message,
					'errors'	=> $this->errors
				];
				return Response::json($data, 422);
			}

			// otherwise, return the data
			else
			{
				$data =
				[
					'message' => $this->message
				];
				if($this->data)
				{
					$data['data'] = $this->data->toArray();
				}
			}

			// return
			return Response::json($data);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// VALIDATION

		/**
		 * Validates input
		 */
		protected function validate()
		{
			// variables
			$input  = Input::all();
			$rules  = $this->getRules($this->action);

			// reset
			$this->errors = null;

			// validate
			if($rules)
			{
				// validate
				$validator = Validator::make($input, $rules);

				// if failed
				if ($validator->fails())
				{
					$this->message	= 'Validation failed';
					$this->errors	= $validator->errors();
					$this->response = Redirect::back()
						->withErrors($validator)
						->withInput(Input::except('password'));
				}
			}

			// store the input
			$this->input = $input;
		}

		/**
		 * Utility method to retrieve an array of validation rules
		 *
		 * Attempts to find rules on the model first, then falls back to this object
		 *
		 * @param string $action
		 * @return array|null
		 */
		protected function getRules($action = null)
		{
			$model = $this->meta->crud->model;
			if($this->meta->model->rules)
			{
				return $this->meta->model->rules;
			}
			else if($model::$rules)
			{
				return $model::$rules;
			}
			return null;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Utility function to return a bunch of useful values for the view
		 * 
		 * @return array
		 */
		protected function getViewData()
		{
			// convert flat model meta to hierarchical data
			$model		= $this->meta->model->data();

			// get fields for view
			$fields		= $model->fields[$this->action];

			// if we don't have any fields, use the model's attributes
			if(count($fields) == 0)
			{
				// attempt to get a model
				$modelClass = $this->meta->crud->model;
				$obj = $this->data instanceof \Eloquent
					? $this->data
					: (count($this->data) > 0
						? $this->data[0]
						: new $modelClass);

				// when we finally have a model, attempt to grab some fields
				if($obj)
				{
					switch($this->action)
					{
						case 'create':
							$fields = $obj->getFillable();
							break;

						case 'edit':
							$fields = array_diff($obj->getFillable(), $obj->getHidden());
							break;

						default:
							$fields = array_keys($obj->toArray());
					}
				}
			}

			// build initial values
			$values =
			[
				// request
				'route'			=> $this->meta->crud->route,
				'view'			=> $this->action,

				// words
				'action'		=> $this->action,
				'Action'		=> ucwords($this->action),
				'singular'		=> $model->singular,
				'Singular'		=> ucwords($model->singular),
				'plural'		=> $model->plural,
				'Plural'		=> ucwords($model->plural),

				// fields
				'fields'		=> $fields,
				'controls'		=> $model->controls,

			];

			// compact values (so they can easily be dumped in the view for debugging purposes)
			$values =
			[
				'values'	=> $values,
				'data'		=> $this->data,
			];

			// append original values to the end (so they can be accessed by name)
			$values			= array_merge($values, $values['values']);

			// debug
			//pd($values);

			// return
			return $values;
		}

}
