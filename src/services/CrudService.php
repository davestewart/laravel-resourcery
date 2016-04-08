<?php namespace davestewart\resourcery\services;

use davestewart\resourcery\classes\meta\ResourceMeta;
use davestewart\resourcery\classes\repos\AbstractRepo;
use Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Input;
use Redirect;
use Request;
use Response;
use Route;
use Session;
use View;

/**
 * Class CrudService
 *
 * Responsible for marshalling:
 *
 * - user input
 * - database calls via EloquentRepo
 * - field generation via MetaService
 * - views and responses
 *
 * Magic response properties:
 *
 * @property 	string                                         $data
 * @property 	string                                         $success
 * @property 	\Symfony\Component\HttpFoundation\Response     $response
 * @property 	\Symfony\Component\HttpFoundation\JsonResponse $json
 * @property 	\Illuminate\Support\Facades\View               $view
 */
class CrudService
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		// request / response

		/**
		 * The base route of this resource controller, excluding the action
		 *
		 * @var string
		 */
		protected $route;

		/**
		 * The current controller action
		 *
		 * @var string
		 */
		protected $action;

		/**
		 * Any custom view
		 *
		 * @var
		 */
		protected $view;

		/**
		 * Any response that's been generated
		 *
		 * @var \Symfony\Component\HttpFoundation\Response
		 */
		protected $response;


		// services

		/**
		 * The meta object that provides all info to this CrudService
		 *
		 * @var MetaService
		 */
		protected $meta;

		/**
		 * The AbstractRepo implementation that interacts with the database
		 *
		 * @var AbstractRepo
		 */
		protected $repo;


		/**
		 * Manages messages and translations
		 *
		 * @var LangService
		 */
		protected $lang;

		/**
		 * Validation service that validates page and field messages
		 *
		 * @var ValidationService $validator
		 */
		protected $validator;

		// data

		/**
		 * The data returned by the repo, such as a single model, or a collection of models
		 *
		 * @var \Illuminate\Contracts\Support\Arrayable
		 */
		protected $data;

		/**
		 * Additional values to be set on the view
		 *
		 * @var \Illuminate\Contracts\Support\Arrayable
		 */
		protected $values;

		/**
		 * A copy of the any submitted user input
		 *
		 * @var array
		 */
		protected $input;

		/**
		 * Any errors generated during validation
		 *
		 * @var array
		 */
		protected $errors;

		/**
		 * Confirmation messages generated by CRUD methods or supplied by fail()
		 *
		 * @var string
		 */
		protected $message;

		/**
		 * Flag to store whether related data has been loaded
		 *
		 * @var bool
		 */
		protected $loaded;

		/**
		 * Flag to store whether the data was saved
		 *
		 * @var bool
		 */
		protected $success;



	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * Constructor function
		 */
		public function __construct()
		{
			$this->values   = [];
			$this->loaded   = false;
		}

		/**
		 * Initialize the service with meta pertaining to the resource
		 *
		 * @param   ResourceMeta    $meta       A ResourceMeta subclass instance
		 * @param   string|null     $route      Optional route; defaults to active route
		 * @return  CrudService
	     */
		public function initialize(ResourceMeta $meta, $route = null)
		{
			// services
			$this->repo         = \App::make('CrudRepo')->initialize($meta->class);
			$this->lang         = \App::make('CrudLangService')->initialize($meta->name, $meta->naming);
			$this->meta         = \App::make('MetaService')->initialize($meta, $this->lang, $this->repo->getFields());
			$this->validator    = \App::make(ValidationService::class);
			
			// route
			if( ! $route )
			{
				$route = str_replace('.', '/', preg_replace('/\\.\\w+$/', '', Route::currentRouteName()));
			}
			$this->route = $route;

			return $this;
		}

		public function debug()
		{
			pr('CrudService::debug() is now listening for database calls...');
			DB::listen(function($sql, $bindings, $duration)
			{
				$data =
				[
					'query'     => preg_replace_callback('/\?/', function($matches) use (&$bindings) { return "'" . array_shift($bindings) . "'"; }, $sql),
					'start'     => round((microtime(true) - LARAVEL_START) * 1000, 2),
					'timed'     => $duration,
				];
				pr('Database call', $data);
			});
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// CONTROLLER METHODS

		/**
		 * Display a listing of the resource.
		 *
		 * @param   mixed|null  $data
		 * @return  self
		 */
		public function index($data = null)
		{
			$this->setAction('index');
			$this->setRedirect(Request::fullUrl());
			if( ! is_object($data) )
			{
				// variables
				$defaults   = \App::make(ResourceMeta::class)->clauses;
				$clauses    = array_merge($defaults, $this->meta->getMeta()->clauses);

				/** @var string $orderBy */
				/** @var string $orderDir */
				/** @var int $perPage */
				extract($clauses);

				// data
				$data   = $this->repo
							->filter(Input::all())
							->orderBy($orderBy, $orderDir)
							->all($perPage);
			}
			$this->setData($data);
			return $this;
		}

		/**
		 * Display the specified resource.
		 *
		 * @param  int|object   $id
		 * @return self
		 */
		public function show($id)
		{
			$this->setAction('show');
			$this->setData($this->repo->find($id));
			return $this;
		}

		/**
		 * Show the form for editing the specified resource.
		 *
		 * @param  int|object   $id
		 * @return self
		 */
		public function edit($id)
		{
			$this->setAction('edit');
			$this->setData($this->repo->find($id));
			return $this;
		}

		/**
		 * Show the form for creating a new resource.
		 *
		 * @return self
		 */
		public function create()
		{
			$this->setAction('create');
			return $this;
		}

		/**
		 * Store a newly created resource in storage.
		 *
		 * @param   array   $input
		 * @param   bool    $force
		 * @return  self
		 */
		public function store($input = null, $force = false)
		{
			return $this->save('store', $input, null, $force);
		}

		/**
		 * Update the specified resource in storage.
		 *
		 * @param   int     $id
		 * @param   array   $input
		 * @param   bool    $force
		 * @return  self
		 */
		public function update($id, $input = null, $force = false)
		{
			return $this->save('update', $input, $id, $force);
		}

		/**
		 * Remove the specified resource from storage.
		 *
		 * @param  int  $id
		 * @return self
		 */
		public function destroy($id)
		{
			// properties
			$this->setAction('destroy');

			// delete
			$this->repo->destroy($id);

			// update response
			$this->setStatus(true, $this->lang->status('deleted'));
			$this->response     = $this->makeRedirect();

			// return;
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// VALIDATION

		/**
		 * Orchestrates validation and saving
		 *
		 * @param   string     $action
		 * @param   array|null $input
		 * @param   int|null   $id
		 * @param bool         $force
		 * @return CrudService
		 */
		protected function save($action, $input = null, $id = null, $force = false)
		{
			// action
			$this->action = $action;

			// input
			if($input == null)
			{
				$input = \Input::all();
			}

			// validate
			if($force)
			{
				$this->input = $input;
			}
			else
			{
				$this->validate($input, $action, null, $id);
			}

			//pd($action, $input, $this->errors, $this->meta->rules);

			// if successful
			if( ! $this->errors || $force)
			{
				// persist data
				if($action == 'store')
				{
					$this->repo->store($input);
					$this->setStatus(true, $this->lang->status('created'));
				}
				else if($action == 'update')
				{
					$this->repo->update($id, $input);
					$this->setStatus(true, $this->lang->status('updated'));
				}
				//pd('data', $this->data);

				// update response
				$this->response		    = $this->makeRedirect();
			}

			// return
			return $this;
		}

		/**
		 * Validates input, optionally with alternate rules
		 *
		 * @param   array      $input
		 * @param null         $action
		 * @param   array|null $rules
		 * @param null         $id
		 * @return CrudService
		 */
		public function validate($input, $action = null, $rules = null, $id = null)
		{
			// reset
			$this->success  = true;
			$this->errors   = null;
			$this->input    = $input;

			// validate
			if($rules == null)
			{
				$rules      = $this->meta->getRules($action, $id);
			}
			$state          = $this->validator->validate($input, $rules);

			//pd($rules);

			// actions
			if ( ! $state )
			{
				// properties
				$this->setStatus(false, $this->lang->status('invalid'));

				// errors
				$this->errors	= $this->validator->form->errors();
				Session::flash('crud.errors', $this->validator->field->errors());

				// build response
				$this->response = Redirect::back()
					->withErrors($this->validator->form->errors())
					->withInput($this->getInput());
			}

			// return
			return $this;
		}

		/**
		 * Explicitly set the current operation to fail with a message
		 *
		 * @param   string      $message    The reason for failing
		 * @return  self
		 */
		public function fail($message)
		{
			$this->setStatus(false, $message);
			$this->response = Redirect::back()
				->withInput($this->getInput());
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// OVERRIDES

		/**
		 * Explicitly sets the action
		 *
		 * @param   string      $action
		 * @return  self
		 */
		public function setAction($action)
		{
			$this->action = $action;
			return $this;
		}

		/**
		 * Explicitly sets the view path
		 *
		 * Override the default view for the action, as set in the ResourceMeta instance
		 *
		 * @param   string      $path
		 * @return  self
		 */
		public function setView($path)
		{
			$this->view = $path;
			return $this;
		}

		/**
		 * Explicitly sets the resource data
		 *
		 * @param   mixed       $data
		 * @return  self
		 */
		public function setData($data)
		{
			$this->data = $data;
			return $this;
		}

		/**
		 * Sets additional view values
		 *
		 * @param   array       $values
		 * @return  self
		 */
		public function setValues(array $values)
		{
			$this->values = array_merge($this->values, $values);
			return $this;
		}

		/**
		 * Store the index redirect URL in session
		 *
		 * @param   string      $url
		 * @return  self
		 */
		public function setRedirect($url)
		{
			Session::set('crud.redirect.' . $this->route, $url);
			return $this;
		}

		/**
		 * Set the success state and current message
		 *
		 * @param   boolean     $state
		 * @param   string      $message
		 * @return  self
		 */
		public function setStatus($state, $message)
		{
			$this->success  = $state;
			$this->message  = $message;
			return $this;
		}

		protected function flashStatus()
		{
			$method = $this->success ? 'success' : 'error';
			Flash::$method($this->message);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// VIEW DATA

		/**
		 * Collates all view values, labels, text etc that pertains to this route
		 *
		 * @return array
		 */
		public function getValues()
		{
			$values =
			[
				'route'         => $this->route,
				'action'	    => $this->action,
				'redirect'      => $this->getRedirect(),
			];
			return $values;
		}

		/**
		 * Loads and returns all data for the resource
		 *
		 * @return \Illuminate\Contracts\Support\Arrayable
		 */
		public function getData()
		{
			if($this->action == 'create')
			{
				return $this->repo->create();
			}
			if(in_array($this->action, ['index', 'show', 'edit']) && ! $this->loaded )
			{
				$this->loadRelated();
			}
			return $this->data;
		}

		/**
		 * Collates the view data as an array of field instances
		 *
		 * @return array
		 */
		public function getFields()
		{
			return $this->meta->getFields($this->action, $this->getData(), $this->getErrors());
		}

		/**
		 * Collates all view data (values, fields, data)
		 *
		 * @return array
		 */
		public function getViewData()
		{
			// prepare data
			$meta           = (object) $this->meta->getMeta();

			// base values
			$values         = $this->getValues();
			$data           = $this->getData();
			$fields         = $this->getFields();
			$views          = (object) $meta->views;
			$lang           = $this->lang->setModel($data);

			// payload
			$payload        = [] + $values;

			// add zipped values
			$payload        += compact('values', 'data', 'views', 'fields', 'lang');

			// debug
			//pr($lang->setModel($data)->get('messages.confirm.delete'));
			//dd($payload);

			// return
			return $payload;
		}

		/**
		 * Returns a copy of the input data, minus hidden fields
		 *
		 * @return array
		 */
		public function getInput()
		{
			$input = array_merge([], $this->input);
			array_forget($input, $this->meta->getMeta()->hidden);
			return $input;
		}

		/**
		 * Gets errors from the session
		 *
		 * @return \Illuminate\Contracts\Support\MessageBag|null
		 */
		public function getErrors()
		{
			return Session::get('crud.errors');
		}

		/**
		 * Gets the index redirect for this resource from the session
		 *
		 * @return mixed|string
		 */
		public function getRedirect()
		{
			$route = Session::get('crud.redirect.' . $this->route);
			if($route == null)
			{
				$route = $this->route;
			}
			return $route;
		}

	// -----------------------------------------------------------------------------------------------------------------
	// ACCESSORS

		/**
		 * @param string $name
		 * @return self|bool|\Symfony\Component\HttpFoundation\Response
		 * @throws \Exception
		 */
		public function __get($name)
		{
			switch($name)
			{
				case 'response':
					return $this->makeResponse();
					break;

				case 'json':
					return $this->makeJson();
					break;

				case 'view':
					return $this->makeView();
					break;

				case 'success':
					return $this->success;
					break;

				case 'data':
					return $this->data
						? $this->data
						: (object) $this->input;
					break;
			}

			// error if invalid property
			throw new \Exception("Property '$name' does not exist");
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MODEL DATA

		/**
		 * Checks that all related models have been loaded
		 */
		public function loadRelated()
		{
			$fields     = $this->meta->getFieldsMeta($this->action);
			$relations  = $this->meta->getRelated() ?: $this->repo->getRelated($fields);;
			if($relations)
			{
				//pr('relations', $relations);
				$this->repo->loadRelated($this->data, $relations);
			}
			$this->loaded = true;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// RESPONSES

		/**
		 * Builds and returns a response object
		 *
		 * @return View|RedirectResponse|\Symfony\Component\HttpFoundation\JsonResponse
		 */
		protected function makeResponse()
		{
			if(\Request::ajax())
			{
				return $this->makeJson();
			}

			if($this->response instanceof RedirectResponse)
			{
				$this->flashStatus();
				return $this->response;
			}

			return $this->makeView();
		}

		/**
		 * Builds and returns a View response
		 *
		 * @param   null|string     $path       The path to a view file
		 * @return  View
		 * @throws  \Exception
		 */
		protected function makeView($path = null)
		{
			if( ! $this->action && $path == null)
			{
				throw new \Exception('A view cannot be constructed as a CRUD method has not yet been called. Call one of the 4 main crud methods(index, show, create, edit) to force a view' );
			}

			if($path == null)
			{
				$path = $this->view
					? $this->view
					: $this->meta->getView($this->action);
			}

			// return view
			return View::make($path, $this->getViewData());
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
					'success'	=> $this->success,
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
					'success'	=> $this->success,
					'message'   => $this->message,
				];
				if($this->data)
				{
					$data['data'] = $this->getData()->toArray();
				}
			}

			// return
			return Response::json($data);
		}

		/**
		 * Single redirect method, with CRUD route fallback
		 *
		 * @param null  $route
		 * @param array $data
		 * @return RedirectResponse
		 */
		protected function makeRedirect($route = null, $data = [])
		{
			if($route == null)
			{
				$route = $this->getRedirect();
			}
			return Redirect::to($route)->with($data);
		}


}
