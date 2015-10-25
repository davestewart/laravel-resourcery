<?php namespace davestewart\laravel\crud;

use Eloquent;
use Illuminate\Http\Response;

/**
 * Class CrudMethods
 * @package davestewart\laravel\crud
 */
trait CrudMethods
{

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/** @var CrudHelper crud */
		protected $crud;


	// -----------------------------------------------------------------------------------------------------------------
	// PUBLIC PROPERTIES

		/**
		 * Display a listing of the resource.
		 *
		 * @return Response
		 */
		public function index()
		{
			return $this->crud->index()->result;
		}
	
		/**
		 * Show the form for creating a new resource.
		 *
		 * @return Response
		 */
		public function create()
		{
			return $this->crud->create()->result;
		}
	
		/**
		 * Store a newly created resource in storage.
		 *
		 * @return Response
		 */
		public function store()
		{
			return $this->crud->store()->result;
		}

		/**
		 * Display the specified resource.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function show($id)
		{
			return $this->crud->show($id)->result;
		}
	
		/**
		 * Show the form for editing the specified resource.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function edit($id)
		{
			return $this->crud->edit($id)->result;
		}
	
		/**
		 * Update the specified resource in storage.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function update($id)
		{
			return $this->crud->update($id)->result;
		}
	
		/**
		 * Remove the specified resource from storage.
		 *
		 * @param  int|Eloquent $id
		 * @return Response
		 */
		public function destroy($id)
		{
			return $this->crud->destroy($id)->result;
		}

}
