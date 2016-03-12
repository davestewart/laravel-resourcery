<?php namespace davestewart\laravel\crud;

use davestewart\laravel\crud\CrudMeta;
use davestewart\laravel\crud\repos\CrudRepo;

use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Validator;
use Redirect;
use Request;
use Response;
use Route;
use Session;
use View;

/**
 * Class CrudService
 *
 * Magic response properties:
 *
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


		// meta

		/**
		 * The meta object that provides all info to this CrudService
		 *
		 * @var CrudMeta
		 */
		protected $meta;

		/**
		 * The CrudRepo implementation that interacts with the database
		 *
		 * @var CrudRepo
		 */
		protected $repo;


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
		 * Any errors generated during validation
		 *
		 * @var array
		 */
		protected $errors;

		/**
		 * Confirmation messages generated by CRUD methods
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
		 * @param   CrudMeta        $meta       A CrudMeta subclass instance
		 * @param   string|null     $route      Optional route; defaults to active route
		 * @return  CrudService
	     */
		public function initialize(CrudMeta $meta, $route = null)
		{
			// meta
			$this->meta     = $meta;

			// repo
			$this->repo     = \App::make('CrudRepo')->initialize($meta->class);
			//$this->values   = \App::make('CrudValues')->initialize($meta);

			// initialize meta
			$this->meta->initialize($this->repo->getFields());

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
			$this->setData(is_object($data)
				? $data
				: $this->repo->all($this->meta->pagination));
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
			$this->setData($this->resolveId($id));
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
			$this->setData($this->resolveId($id));
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
		 * @param   array       $input
		 * @return  CrudService
		 */
		public function store($input = null)
		{
			return $this->save('store', $input);
		}

		/**
		 * Update the specified resource in storage.
		 *
		 * @param   int         $id
		 * @param   array       $input
		 * @return  CrudService
		 */
		public function update($id, $input = null)
		{
			return $this->save('update', $input, $id);
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
			$this->message	    = $this->meta->getMessage('deleted');
			$this->response     = $this->makeRedirect();

			// return;
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// OVERRIDES

		/**
		 * Explicitly set the action
		 *
		 * @param   $action
		 * @return  self
		 */
		public function setAction($action)
		{
			$this->action = $action;
			return $this;
		}

		/**
		 * Explicitly sets the view
		 *
		 * @param   string  $path
		 * @return  self;
		 */
		public function setView($path)
		{
			$this->view = $path;
			return $this;
		}

		/**
		 * Explicitly sets the data
		 *
		 * @param   mixed   $data
		 * @return  self;
		 */
		public function setData($data)
		{
			$this->data = $data;
			return $this;
		}

		/**
		 * Sets additional view values
		 *
		 * @param   array   $values
		 * @return  self;
		 */
		public function setValues($values)
		{
			$this->values = array_merge($this->values, $values);
			return $this;
		}

		/**
		 * Validates alternative input and optionally, rules
		 *
		 * @param   array           $input
		 * @param   array|null      $rules
		 * @return  bool
		 */
		public function validate($input, $rules = null)
		{
			// reset
			$this->errors = null;

			// validate
			$validator = $rules
				? Validator::make($input, $rules)
				: $this->meta->validate($input);

			// actions
			if ($validator->fails())
			{
				// properties
				$this->message	= $this->meta->getMessage('invalid');
				$this->errors	= $validator->errors();

				// build response
				$this->response = Redirect::back()
					->withErrors($validator)
					->withInput($this->getInput($input));
			}
			return $this;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// VIEW DATA

		/**
		 * Get the loaded data
		 *
		 * @return \Illuminate\Contracts\Support\Arrayable
		 */
		public function getData()
		{
			if(in_array($this->action, ['index', 'show', 'edit']) && ! $this->loaded )
			{
				$this->loadRelated();
			}
			return $this->data;
		}

		/**
		 * Get the data as fields
		 *
		 * @return array
		 */
		public function getFields()
		{
			return $this->meta->getFields($this->action, $this->getData(), $this->getErrors());
		}

		/**
		 * Get all values, labels, text etc that pertains to this route
		 *
		 * @return array
		 */
		public function getValues()
		{
			// prepare data
			$data               = $this->getData();
			$meta               = (object) $this->meta;

			// state
			$props =
			[
				'route'			=> $this->route,
				'view'			=> $this->action,
			];

			// text
			$words =
			[
				'action'		=> $this->action,
				'singular'		=> $meta->singular,
				'plural'		=> $meta->plural,
				'title'			=> $this->meta->getTitle($data),
			];

			// add Capitalized Versions of text
			$text = [];
			foreach($words as $key => $value)
			{
				$text[$key] = $value;
				$text[ucwords($key)] = ucwords($value);
			}

			// fields
			$views =
			[
				'views'         => (object) $meta->views,
			];

			// return
			return array_merge($props, $text, $views, $this->values);
		}

		/**
		 * Get vlues,
		 *
		 * @return array
		 */
		public function getViewData()
		{
			// base values
			$values         = $this->getValues();
			$fields         = $this->getFields();
			$data           = $this->getData();

			// payload
			$payload        = $values + compact('values', 'fields', 'data');

			// debug
			//pr($payload);

			// return
			return $payload;
		}

		/**
		 * Returns a copy of the input data, minus hidden fields
		 *
		 * @param $input
		 * @return array
		 */
		public function getInput($input)
		{
			$input = array_merge([], $input);
			array_forget($input, $this->meta->hidden);
			return $input;
		}

		public function getErrors()
		{
			/** @var ViewErrorBag $errors */
			$errors = Session::get('errors');
			return $errors
				? $errors->getBag('default')
				: null;
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
				case 'success':
					return $this->success;
					break;

				case 'response':
					return $this->makeResponse();
					break;

				case 'json':
					return $this->makeJson();
					break;

				case 'view':
					return $this->makeView();
					break;
			}

			// error if invalid property
			throw new \Exception("Property '$name' does not exist");
		}


	// -----------------------------------------------------------------------------------------------------------------
	// MODEL DATA

		/**
		 * Orchestrates validation and saving
		 *
		 * @param   string      $action
		 * @param   array|null  $input
		 * @param   int|null    $id
		 * @return  self
		 */
		protected function save($action, $input = null, $id = null)
		{
			// action
			$this->action = $action;

			// input
			if($input == null)
			{
				$input = \Input::all();
			}

			// validate
			$this->validate($input);

			// if successful
			if( ! $this->errors )
			{
				// persist data
				if($action == 'store')
				{
					$this->repo->store($input);
					$this->message		= $this->meta->getMessage('created');
				}
				else if($action == 'update')
				{
					$this->repo->update($id, $input);
					$this->message		= $this->meta->getMessage('updated');
				}
				//pd('data', $this->data);

				// update response
				$this->success          = true;
				$this->response		    = $this->makeRedirect();
			}

			// return
			return $this;
		}

		/**
		 * Checks that all related models have been loaded
		 */
		protected function loadRelated()
		{
			// variables
			$relations  = $this->meta->getRelated($this->action);
			$items      = $this->data instanceof AbstractPaginator
							? $this->data->items()
							: $this->data;

			// if we have at least one data item, look to eager load
			if(count($items))
			{
				$item  = $items[1];
				foreach($relations as $relation)
				{
					if( ! isset($item->$relation) )
					{
						$this->data->load($relation);
					}
				}
			}

			// flag loaded
			$this->loaded = true;
		}

		protected function resolveId($id)
		{
			return is_object($id)
				? $id
				: $this->repo->find($id);
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
				if($this->message)
				{
					Session::flash('message', $this->message);
				}
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
					$data['data'] = $this->getData()->toArray();
				}
			}

			// return
			return Response::json($data);
		}

		/**
		 * Single redirect method, with CRUD route fallback
		 *
		 * @param null $route
		 * @return RedirectResponse
		 */
		protected function makeRedirect($route = null)
		{
			if($route == null)
			{
				$route = $this->route;
			}
			return Redirect::to($route)->with($this->getViewData());
		}


}
