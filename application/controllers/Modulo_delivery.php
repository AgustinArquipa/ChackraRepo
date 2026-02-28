<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modulo_delivery extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('Grocery_CRUD');
		$this->load->library('OutputView');
        
        $this->script_datatables = '"language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        }';
        
        $this->user = $this->ion_auth->user()->row();
	}
    
    public function categorias(){
        $tabla = 'del_categorias';
        $titulo = 'Delivery: Categorias de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('cat_nombre');
			$crud->columns('cat_nombre')
                ->display_as('cat_nombre','Nombre')
                ->display_as('cat_foto','Foto');
		
            $crud->set_field_upload('cat_foto','assets/uploads/delivery/categorias/');            
            $crud->callback_after_upload(array($this,'callback_after_upload'));
            
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array($titulo => '');
            $data['script_datatables'] = $this->script_datatables;
            $data['extra'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
	
	public function horarios(){
        $tabla = 'del_horarios';
        $titulo = 'Delivery: Horarios de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_del_prestadores','hor_dia', 'hora_inicio', 'hora_fin');
			$crud->columns('id_del_prestadores','hor_dia', 'hora_inicio', 'hora_fin')
                ->display_as('id_del_prestadores','Prestador')
                ->display_as('hor_dia','Dia')
                ->display_as('hor_inicio','Inicio')
                ->display_as('hor_fin','Fin');
					
            $crud->set_relation('id_del_prestadores','del_prestadores','{pre_nombre_completo}');
            
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
	
	public function prestadores(){
        $tabla = 'del_prestadores';
        $titulo = 'Delivery: Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_del_prestadores','id_del_categorias', 'pre_nombre_completo', 'pre_domicilio', 'pre_telefono', 'pre_descripcion', 'pre_foto');
			$crud->columns('id_del_prestadores','pre_nombre_completo', 'id_del_categorias')
                ->display_as('id_del_prestadores','ID')
                ->display_as('id_del_categorias','Categoria')
                ->display_as('pre_nombre_completo','Nombre Completo')
                ->display_as('id_usuarios','Usuario')
                ->display_as('pre_domicilio','Domicilio')
                ->display_as('pre_telefono','Telefono')
                ->display_as('pre_descripcion','Descripcion')
                ->display_as('pre_foto','Foto de Perfil')
                ->display_as('pre_foto_portada','Foto de Portada')
                ->display_as('pre_estado','Estado');
            
            $crud->field_type('pre_fecha_alta', 'hidden');
            
            $crud->unset_texteditor('pre_descripcion');
            
            $crud->set_field_upload('pre_foto','assets/uploads/delivery/prestadores/');
            $crud->set_field_upload('pre_foto_portada','assets/uploads/delivery/prestadores/');
            
            $crud->callback_after_upload(array($this,'callback_after_upload'));
			
            $crud->set_relation('id_del_categorias','del_categorias','{cat_nombre}');
            $crud->set_relation('id_usuarios','usuarios','{username}');
            
			$output = $crud->render();

            //COMBO LOCALIDADES
            $datos = array(
                //GET THE STATE OF THE CURRENT PAGE - E.G LIST | ADD
                'estado' =>  $crud->getState(),
                //SETUP YOUR DROPDOWNS
                //Parent field item always listed first in array, in this case countryID
                //Child field items need to follow in order, e.g idprovincia then idlocalidad
                'combos' => array('id_tur_categorias','id_tur_subcategorias'),
                //SETUP URL POST FOR EACH CHILD
                //List in order as per above
                'url' => array('', site_url().'modulo_turnero/buscarsubcategorias/'),
                //LOADER THAT GETS DISPLAYED NEXT TO THE PARENT DROPDOWN WHILE THE CHILD LOADS
                'icon_ajax' => base_url().'ajax-loader.gif'
            );
            $output->combo_localidades = $datos;
            
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
	
	public function productos(){
        $tabla = 'del_productos';
        $titulo = 'Delivery: Productos de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_del_prestadores','ser_nombre', 'pro_precio', 'pro_descripcion');
			$crud->columns('id_del_productos','id_del_prestadores','pro_nombre', 'pro_precio')
                ->display_as('id_del_productos','ID')
                ->display_as('id_del_prestadores','Prestador')
                ->display_as('pro_nombre','Nombre')
                ->display_as('pro_precio','Precio')
				->display_as('pro_imagen','Imagen')
                ->display_as('pro_descripcion','Descripcion');
			
            $crud->set_relation('id_del_prestadores','del_prestadores','{pre_nombre_completo}');
            
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
}