<?php namespace davestewart\laravel\crud;

use Eloquent;
use Illuminate\Http\Response;

/**
 * CrudMethods trait
 *
 * Use in resource controller to setup all CRUD functionality
 */
trait CrudMethods
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var CrudService crud */
		protected $crud;


	// -----------------------------------------------------------------------------------------------------------------
	// PUBLIC PROPERTIES

		/**
		 * Setup function
		 *
		 * @param CrudMeta $meta
		 */
		protected function setup(CrudMeta $meta, $debug = false)
		{
			$this->crud = \App::make('CrudService')->initialize($meta);
			if($debug)
			{
				$this->crud->debug();
			}
		}

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
