<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modulos_generales extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Grocery_CRUD');
		$this->load->library('OutputView');
        
        $this->script_datatables = '"language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        }';
        
        $this->user = $this->ion_auth->user()->row();
	}

	//CRUD EXAMPLES HERE
	public function avisos_categorias(){
        $tabla = 'avisos_categorias';
        $titulo = 'Categorias de Avisos';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('avi_cat_nombre');
			$crud->columns('avi_cat_nombre')
                ->display_as('avi_cat_nombre','Nombre');
						
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function colores(){
        $tabla = 'veh_colores';
        $titulo = 'Color del Vehiculo';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('col_nombre');
			$crud->columns('col_nombre')
                ->display_as('col_nombre','Color');
            $crud->order_by('col_nombre', 'ASC');
						
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function modelos(){
        $tabla = 'modelos';
        $titulo = 'Modelos de Vehiculos';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('mod_nombre', 'mar_nombre');
			$crud->columns('mod_nombre', 'mar_nombre')
                ->display_as('mod_nombre','Modelo')
                ->display_as('mar_nombre','Marca');
						
            $crud->set_relation('id_marcas','marcas','{mar_nombre}');
            
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function empresas(){
        $tabla = 'empresas';
        $titulo = 'Empresas';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('emp_nombre', 'emp_direccion', 'emp_email');
			$crud->columns('emp_nombre', 'id_provincias', 'id_localidades')
                ->display_as('id_provincias','Provincia')
                ->display_as('id_localidades','Localidad')
                ->display_as('emp_nombre','Nombre')
                ->display_as('emp_direccion','Direccion')
                ->display_as('emp_email','Email');

            $crud->set_field_upload('logo','assets/img/empresas/');
            
            $crud->set_relation('id_provincias','provincias','{pro_nombre}');
            
            $crud->callback_add_field('id_localidades', array($this, 'cbklocalidades'));
            $crud->callback_edit_field('id_localidades', array($this, 'cbklocalidades'));
            
			$output = $crud->render();
            
            //COMBO LOCALIDADES
			$datos = array(
				//GET THE STATE OF THE CURRENT PAGE - E.G LIST | ADD
				'estado' =>  $crud->getState(),
				//SETUP YOUR DROPDOWNS
				//Parent field item always listed first in array, in this case countryID
				//Child field items need to follow in order, e.g idprovincia then idlocalidad
				'combos' => array('id_provincias','id_localidades'),
				//SETUP URL POST FOR EACH CHILD
				//List in order as per above
				'url' => array('', site_url().'modulos_principales/buscarlocalidades/'),
				//LOADER THAT GETS DISPLAYED NEXT TO THE PARENT DROPDOWN WHILE THE CHILD LOADS
				'icon_ajax' => base_url().'ajax-loader.gif'
			);
			$output->combo_localidades = $datos;

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';
            $data['extra'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function equipamientos(){
        $tabla = 'equipamientos';
        $titulo = 'Equipamientos';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('equ_nombre');
			$crud->columns('equ_nombre')
                ->display_as('equ_nombre','Equipamiento');
						
			$output = $crud->render();

			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    function cbklocalidades(){

        //creamos el combo
        $combo = '<select name="id_localidades" class="chosen-select" data-placeholder="Seleccionar localidad" style="width: 300px; display: none;">';

        $fincombo = '</select>';

        //Tomamos el id de inmueble si se enviocomo parámetro por url
        $tipo = $this->uri->segment(2);
        $id = $this->uri->segment(4);

        //Verificamos la operacion que estamos haciendo si agregamos o editamos
        $crud = new Grocery_CRUD();
        $estado = $crud->getState();

        //Si estamos editando y el id de inmueble no es vació

        if(isset($id) && $estado == "edit") {
            
            $this->db->select('id_provincias, id_localidades')
                ->from($tipo)
                ->where('id_'.$tipo, $id);


            $db = $this->db->get();
            $row = $db->row(0);

            $id_provincia = $row->id_provincias;
            $id_localidad = $row->id_localidades;
            
            //Cargamos el combo con todas las localidades de la pronvincia

            $this->db->select('*')
                ->from('localidades')
                ->where('id_provincias', $id_provincia);

            $db = $this->db->get();
            
            //Si ecnontramos el id de localidad actual lo ponemos como selecionado
            //sino seguimos cargando las demas localidades
            foreach($db->result() as $row):
                if($row->id_localidades == $id_localidad) {
                    $combo .= '<option value="'.$row->id_localidades.'" selected="selected">'.$row->loc_nombre.'</option>';
                } else {
                    $combo .= '<option value="'.$row->id_localidades.'">'.$row->loc_nombre.'</option>';
                }
            endforeach;
            
            //Devolvemos el combo cargado

            return $combo.$fincombo;
        } else {
            return $combo.$fincombo;
        }
    }
    
    public function sucursales(){
        $tabla = 'sucursales';
        $titulo = 'Sucursales de Empresas';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('suc_nombre', 'id_empresas', 'id_provincias', 'id_localidades');
			$crud->columns('suc_nombre', 'emp_nombre')
                ->display_as('id_provincias','Provincia')
                ->display_as('id_localidades','Localidad')
                ->display_as('suc_nombre','Nombre')
                ->display_as('suc_direccion','Direccion')
                ->display_as('suc_email','Email')
                ->display_as('id_empresas','Empresa')
                ->display_as('suc_telefono','Telefono')
                ->display_as('suc_encargado','Encargado');
            
            $crud->set_relation('id_empresas','empresas','{emp_nombre}');
						
            $crud->set_relation('id_provincias','provincias','{pro_nombre}');
            
            $crud->callback_add_field('id_localidades', array($this, 'cbklocalidades'));
            $crud->callback_edit_field('id_localidades', array($this, 'cbklocalidades'));
            
			$output = $crud->render();

            //COMBO LOCALIDADES
			$datos = array(
				//GET THE STATE OF THE CURRENT PAGE - E.G LIST | ADD
				'estado' =>  $crud->getState(),
				//SETUP YOUR DROPDOWNS
				//Parent field item always listed first in array, in this case countryID
				//Child field items need to follow in order, e.g idprovincia then idlocalidad
				'combos' => array('id_provincias','id_localidades'),
				//SETUP URL POST FOR EACH CHILD
				//List in order as per above
				'url' => array('', site_url().'modulos_principales/buscarlocalidades/'),
				//LOADER THAT GETS DISPLAYED NEXT TO THE PARENT DROPDOWN WHILE THE CHILD LOADS
				'icon_ajax' => base_url().'ajax-loader.gif'
			);
			$output->combo_localidades = $datos;
            
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $this->script_datatables;
            $data['extra_info'] = '';

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    
    public function soporte(){
        $url_actual = base_url();
        
        $output = (object)array('usuario' => $this->user);
        
        $data['judul'] = 'Soporte';
        $data['crumb'] = array( );
        $data['script_datatables'] = $this->script_datatables;
        $data['extra'] = '';
        $data['extra_info'] = '';
        
        $template = 'admin_template';
        $view = 'soporte/contacto_view';
        
        $this->outputview->output_admin($view, $template, $data, $output);
    }
    
}
