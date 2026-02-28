<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modulo_turnero extends CI_Controller {

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
        $tabla = 'tur_categorias';
        $titulo = 'Turnero: Categorías de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('cat_nombre');
			$crud->columns('cat_nombre')
                ->display_as('cat_nombre','Nombre')
                ->display_as('cat_foto','Foto');
		
            $crud->set_field_upload('cat_foto','assets/uploads/turnero/categorias/');            
            $crud->callback_after_upload(array($this,'callback_after_upload'));
            
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
    
    public function subcategorias(){
        $tabla = 'tur_subcategorias';
        $titulo = 'Turnero: Subcategorías de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_tur_categorias', 'sub_nombre');
			$crud->columns('id_tur_categorias', 'sub_nombre')
                ->display_as('id_tur_categorias','Categoría')
                ->display_as('sub_nombre','Nombre')
                ->display_as('sub_foto','Foto');
            
            $crud->set_field_upload('sub_foto','assets/uploads/turnero/subcategorias/');
            $crud->callback_after_upload(array($this,'callback_after_upload'));
						
            $crud->set_relation('id_tur_categorias','tur_categorias','{cat_nombre}');
            
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
    
    public function horarios(){
        $tabla = 'tur_horarios';
        $titulo = 'Turnero: Horarios de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_tur_prestadores','hor_dia', 'hora_inicio', 'hora_fin');
			$crud->columns('id_tur_prestadores','hor_dia', 'hora_inicio', 'hora_fin')
                ->display_as('id_tur_prestadores','Prestador')
                ->display_as('hor_dia','Día')
                ->display_as('hor_inicio','Inicio')
                ->display_as('hor_fin','Fin');
					
            $crud->set_relation('id_tur_prestadores','tur_prestadores','{pre_nombre_completo}');
            
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
        $tabla = 'tur_prestadores';
        $titulo = 'Turnero: Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_tur_prestadores','id_tur_categorias', 'id_tur_subcategorias', 'pre_nombre_completo', 'pre_domicilio', 'pre_telefono', 'pre_descripcion', 'pre_foto');
			$crud->columns('id_tur_prestadores','pre_nombre_completo', 'id_tur_instituciones', 'id_tur_categorias', 'id_tur_subcategorias')
                ->display_as('id_tur_prestadores','ID')
                ->display_as('id_tur_instituciones','Institución')
                ->display_as('id_tur_categorias','Categoría')
                ->display_as('id_tur_subcategorias','Subcategoría')
                ->display_as('pre_nombre_completo','Nombre Completo')
                ->display_as('id_usuarios','Usuario')
                ->display_as('pre_domicilio','Domicilio')
                ->display_as('pre_telefono','Teléfono')
                ->display_as('pre_descripcion','Descripción')
                ->display_as('pre_foto','Foto de Perfil')
                ->display_as('pre_foto_portada','Foto de Portada')
                ->display_as('pre_estado','Estado');
            
            $crud->field_type('pre_fecha_alta', 'hidden');
            
            $crud->unset_texteditor('pre_descripcion');

            
            $crud->callback_add_field('id_tur_subcategorias', array($this, 'cbksubcategorias'));
            $crud->callback_edit_field('id_tur_subcategorias', array($this, 'cbksubcategorias'));
            
            $crud->set_field_upload('pre_foto','assets/uploads/turnero/prestadores/');
            $crud->set_field_upload('pre_foto_portada','assets/uploads/turnero/prestadores/');
            
            $crud->callback_after_upload(array($this,'callback_after_upload'));
			
            $crud->set_relation('id_tur_instituciones','tur_instituciones','{ins_nombre}');
            $crud->set_relation('id_tur_categorias','tur_categorias','{cat_nombre}');
            $crud->set_relation('id_tur_subcategorias','tur_subcategorias','{sub_nombre}'); 
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
    
    public function servicios(){
        $tabla = 'tur_servicios';
        $titulo = 'Turnero: Servicios de Prestadores';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_tur_prestadores','ser_nombre', 'ser_precio', 'ser_duracion', 'ser_descripcion');
			$crud->columns('id_tur_servicios','id_tur_prestadores','ser_nombre', 'ser_precio', 'ser_duracion')
                ->display_as('id_tur_servicios','ID')
                ->display_as('id_tur_prestadores','Prestador')
                ->display_as('ser_nombre','Nombre')
                ->display_as('ser_precio','Precio')
                ->display_as('ser_duracion','Duración')
                ->display_as('ser_descripcion','Descripción');
			
            $crud->set_relation('id_tur_prestadores','tur_prestadores','{pre_nombre_completo}');
            
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
    
    public function turnos(){
        $tabla = 'tur_turnos';
        $titulo = 'Turnero: Turnos';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_tur_prestadores','id_tur_servicios', 'id_usuarios', 'tur_inicio', 'tur_fin', 'tur_fecha', 'duracion_min', 'tur_estado_prestador', 'tur_estado_cliente');
			$crud->columns('id_tur_turnos','id_tur_prestadores', 'id_tur_servicios', 'tur_fecha', 'tur_inicio', 'tur_fin')
                ->display_as('id_tur_turnos','ID')
                ->display_as('id_tur_prestadores','Prestador')
                ->display_as('id_usuarios','Usuario')
                ->display_as('id_tur_servicios','Servicio')
                ->display_as('tur_fecha','Fecha')
                ->display_as('tur_inicio','Inicio')
                ->display_as('tur_fin','Fin')
                ->display_as('duracion_min','Duración')
                ->display_as('tur_estado_prestador','Estado (Prestador)')
                ->display_as('tur_estado_cliente','Estado (Cliente)');
			
            $crud->set_relation('id_tur_prestadores','tur_prestadores','{pre_nombre_completo}');
            $crud->set_relation('id_tur_servicios','tur_servicios','{ser_nombre}');
            $crud->set_relation('id_usuarios','usuarios','{nombre_completo}');
            
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
    
    public function instituciones(){
        $tabla = 'tur_instituciones';
        $titulo = 'Turnos: Instituciones';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('ins_nombre');
			$crud->columns('ins_nombre')
                ->display_as('id_tur_instituciones','ID')
				->display_as('ins_cuit','CUIT')
                ->display_as('ins_nombre','Nombre')
				->display_as('ins_imagen','Imagen')
				->display_as('ins_descripcion','Descripción');
		            
			$crud->set_field_upload('ins_imagen','assets/uploads/turnero/instituciones/');
			
			$crud->unset_texteditor('ins_descripcion');
			
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
	
	public function instituciones_usuarios(){
        $tabla = 'tur_instituciones_usuarios';
        $titulo = 'Turnos: Usuarios de Instituciones';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('id_instituciones', 'ins_usu_nombre_completo', 'ins_usu_email', 'ins_usu_clave');
			$crud->columns('ins_usu_nombre_completo', 'id_instituciones')
                ->display_as('id_tur_instituciones_usuarios','ID')
                ->display_as('id_instituciones','Institución')
				->display_as('ins_usu_nombre_completo','Nombre Completo')
                ->display_as('ins_usu_email','Email')
				->display_as('ins_usu_clave','Clave');
		      
			$crud->set_relation('id_instituciones','tur_instituciones','{ins_nombre}');
			
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
    
    function callback_after_upload($uploader_response,$field_info, $files_to_upload){
        $this->load->library('image_moo');
        
        $file = $field_info->upload_path.'/'.$uploader_response[0]->name;   
        
        $file_uploaded = $field_info->upload_path.'/thumb-600-400-'.$uploader_response[0]->name;
        $this->image_moo->load($file)->resize_crop(600,400)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);

        $file_uploaded = $field_info->upload_path.'/thumb-250-'.$uploader_response[0]->name;
        $this->image_moo->load($file)->resize_crop(250,250)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);
        //$this->image_moo->load($file)->load_watermark('assets/uploads/logo.png')->resize_crop(300,300)->save($file_uploaded,true);
        
        return true;
    }
    
    function cbksubcategorias(){

        //creamos el combo
        $combo = '<select name="id_tur_subcategorias" class="chosen-select" data-placeholder="Seleccionar subcategoria" style="width: 300px; display: none;">';

        $fincombo = '</select>';

        $id_prestador = $this->uri->segment(4);

        $crud = new Grocery_CRUD();
        $estado = $crud->getState();

        if(isset($id_prestador) && $estado == "edit") {
            //consultamos la provincia y la localidad actual del inmueble
            $this->db->select('id_tur_categorias, id_tur_subcategorias')
                ->from('tur_prestadores')
                ->where('id_tur_prestadores', $id_prestador);


            $db = $this->db->get();
            $row = $db->row(0);

            $id_categoria = $row->id_tur_categorias;
            $id_subcategoria = $row->id_tur_subcategorias;
            
            //Cargamos el combo con todas las localidades de la pronvincia

            $this->db->select('*')
                ->from('tur_subcategorias')
                ->where('id_tur_categorias', $id_categoria);

            $db = $this->db->get();
            
            //Si ecnontramos el id de localidad actual lo ponemos como selecionado
            //sino seguimos cargando las demas localidades
            foreach($db->result() as $row):
                if($row->id_tur_subcategorias == $id_subcategoria) {
                    $combo .= '<option value="'.$row->id_tur_subcategorias.'" selected="selected">'.$row->sub_nombre.'</option>';
                } else {
                    $combo .= '<option value="'.$row->id_tur_subcategorias.'">'.$row->sub_nombre.'</option>';
                }
            endforeach;
            
            //Devolvemos el combo cargado

            return $combo.$fincombo;
        } else {
            return $combo.$fincombo;
        }
    }
    
    function buscarsubcategorias(){
		$id_categoria = $this->uri->segment(3);
		
		$this->db->select("*")
				 ->from('tur_subcategorias')
				 ->where('id_tur_categorias', $id_categoria);
		$db = $this->db->get();
		
		$array = array();
		foreach($db->result() as $row):
			$array[] = array("value" => $row->id_tur_subcategorias, "property" => $row->sub_nombre);
		endforeach;
		
		echo json_encode($array);
		exit;
	}
}