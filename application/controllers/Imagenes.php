<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Imagenes extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		/* Standard Libraries */
		$this->load->database();
		/* ------------------ */
		
		$this->load->helper('url'); //Just for the examples, this is not required thought for the library
		
		$this->load->library('image_CRUD');
        $this->load->library('OutputView');
	}
	
	function _example_output($output = null)
	{
		$this->load->view('example.php',$output);	
	}
	
	function index()
	{
		$this->_example_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
	}

	function inspecciones()
	{
		$image_crud = new image_CRUD();
	
		$image_crud->set_primary_key_field('id');
		$image_crud->set_url_field('url');
		$image_crud->set_table('inspecciones_fotos')
		->set_relation_field('id_inspecciones')
		->set_ordering_field('priority')
		->set_image_path('assets/uploads/inspecciones');
			
        $output = $image_crud->render();
	
		$data['judul'] = 'Fotos';
        $data['crumb'] = array( 'Inspecciones' => 'modulos_principales/inspecciones', 'Fotos' => '' );
        $data['script_datatables'] = '';
        $data['extra_info'] = '';
        $data['extra'] = '';

        $template = 'admin_template';
        $view = 'grocery';
        $this->outputview->output_admin($view, $template, $data, $output);

        //$this->_example_output($output);
	}
    

	function example4()
	{
		$image_crud = new image_CRUD();
	
		$image_crud->set_primary_key_field('id');
		$image_crud->set_url_field('url');
		$image_crud->set_title_field('title');
		$image_crud->set_table('example_4')
		->set_ordering_field('priority')
		->set_image_path('assets/uploads');
			
		$output = $image_crud->render();
	
		$this->_example_output($output);
	}
	
	function simple_photo_gallery()
	{
		$image_crud = new image_CRUD();
		
		$image_crud->unset_upload();
		$image_crud->unset_delete();
		
		$image_crud->set_primary_key_field('id');
		$image_crud->set_url_field('url');
		$image_crud->set_table('example_4')
		->set_image_path('assets/uploads');
		
		$output = $image_crud->render();
		
		$this->_example_output($output);		
	}

}