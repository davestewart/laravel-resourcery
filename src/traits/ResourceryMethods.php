<?php namespace davestewart\resourcery\traits;

use davestewart\resourcery\classes\data\ResourceMeta;
use davestewart\resourcery\services\CrudService;
use Eloquent;
use Illuminate\Http\Response;

/**
 * ResourceryMethods trait
 *
 * Use in resource controller to setup all Resourcery CRUD functionality
 */
trait ResourceryMethods
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var CrudService crud */
		protected $crud;


	// -----------------------------------------------------------------------------------------------------------------
	// PROTECTED METHODS

		/**
		 * Setup function
		 *
		 * @param ResourceMeta|string $meta     A ResourceMeta instance, or a FQ model path
		 * @param bool                $debug    An optional boolean to start the CrudService debugger
		 */
		protected function setup($meta, $debug = false)
		{
			// parameters
			if(is_string($meta))
			{
				$meta = new ResourceMeta($meta);
			}

			// setup
			$this->crud = \App::make(CrudService::class)->initialize($meta);

			// debugging
			if($debug)
			{
				$this->crud->debug();
			}
		}


	// -----------------------------------------------------------------------------------------------------------------
	// PUBLIC METHODS

		/**
		 * Display a listing of the resource.
		 *
		 * @return Response
		 */
		public function index()
		{
			return $this->crud->index()->response;
		}
	
		/**
		 * Show the form for creating a new resource.
		 *
		 * @return Response
		 */
		public function create()
		{
			return $this->crud->create()->response;
		}
	
		/**
		 * Store a newly created resource in storage.
		 *
		 * @return Response
		 */
		public function store()
		{
			return $this->crud->store()->response;
		}

		/**
		 * Display the specified resource.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function show($id)
		{
			return $this->crud->show($id)->response;
		}
	
		/**
		 * Show the form for editing the specified resource.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function edit($id)
		{
			return $this->crud->edit($id)->response;
		}
	
		/**
		 * Update the specified resource in storage.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function update($id)
		{
			return $this->crud->update($id)->response;
		}
	
		/**
		 * Remove the specified resource from storage.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function destroy($id)
		{
			return $this->crud->destroy($id)->response;
		}

}
