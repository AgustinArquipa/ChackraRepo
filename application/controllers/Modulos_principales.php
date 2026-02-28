<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modulos_principales extends CI_Controller {
    
    private $crud = null;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Grocery_CRUD');
		$this->load->library('OutputView');
        $this->load->library('session');        

        $this->meses = array ('"Enero"', '"Febrero"', '"Marzo"', '"Abril"', '"Mayo"', '"Junio"', '"Julio"', '"Agosto"', '"Septiembre"', '"Octubre"', '"Noviembre"', '"Diciembre"');
        $this->script_datatables = '"language": {
            "url": "'. base_url('assets/js/plugins/datatables/spanish.js').'"
        }';
        
        $this->user = $this->ion_auth->user()->row();
                
        $this->js_jquery = 'assets/grocery_crud/js/jquery-1.11.1.min.js';
        
        $this->css_chosen = 'assets/grocery_crud/css/jquery_plugins/chosen/chosen.css';
        $this->js_chosen = 'assets/grocery_crud/js/jquery_plugins/jquery.chosen.min.js';
        $this->js_config_chosen = 'assets/grocery_crud/js/jquery_plugins/config/jquery.chosen.config.js';
	}
    
    function unique_field_name($field_name) {
	    return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not with a number
    }
    
    public function avisos(){
        $tabla = 'avisos';
        $titulo = 'Avisos';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
            
            $crud->fields('id_usuarios', 'id_avisos_categorias', 'id_avisos_subcategorias', 'avi_titulo', 'avi_descripcion');
            
			$crud->columns('id_avisos', 'id_usuarios', 'id_avisos_categorias', 'id_avisos_subcategorias', 'avi_titulo')
                ->display_as('id_avisos','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('id_avisos_categorias','Categoria')
                ->display_as('id_avisos_subcategorias','Subcategoria')
                ->display_as('avi_titulo','Titulo')
                ->display_as('avi_descripcion','Descripcion');
            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
            $crud->set_relation('id_avisos_categorias','avisos_categorias','{avi_cat_nombre}');
            $crud->set_relation('id_avisos_subcategorias','avisos_subcategorias','{avi_sub_nombre}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
	public function notificaciones(){
        $tabla = 'notificaciones';
        $titulo = 'Notificaciones';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
            
			$crud->columns('id_notificaciones', 'id_usuarios', 'not_fecha_alta')
                ->display_as('id_notificaciones','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('not_fecha_alta','Fecha Alta');
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
                        
            $crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
	
    public function mascotas(){
        $tabla = 'mascotas';
        $titulo = 'Mascotas';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
            $crud->fields('id_usuarios', 'mas_titulo', 'mas_descripcion', 'mas_imagen');
            
			$crud->columns('id_mascotas', 'id_usuarios', 'mas_titulo')
                ->display_as('id_mascotas','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('mas_titulo','Titulo');
            
            $crud->set_field_upload('mas_imagen','assets/uploads/'.$tabla.'/');
            $crud->callback_after_upload(array($this,'callback_after_upload'));
            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    function callback_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');

        
        $file = $field_info->upload_path.'/'.$uploader_response[0]->name;   
            
        
        $file_uploaded = $field_info->upload_path.'/thumb-50-'.$uploader_response[0]->name;
        $this->image_moo->load($file)->resize_crop(50,50)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);

        $file_uploaded = $field_info->upload_path.'/thumb-200-'.$uploader_response[0]->name;
        $this->image_moo->load($file)->load_watermark('assets/uploads/logo.png')->resize_crop(300,300)->save($file_uploaded,true);
        
        return true;
    }
    
    
    public function profesionales(){
        $tabla = 'profesionales';
        $titulo = 'Profesionales';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_profesionales', 'id_usuarios', 'id_profesionales_rubros', 'nombre_completo', 'mp')
                ->display_as('id_profesionales','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('id_profesionales_rubros','Rubros')
                ->display_as('nombre_completo','Nombre');            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
            $crud->set_relation('id_profesionales_rubros','profesionales_rubros','{pro_rub_nombre}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function denuncias(){
        $tabla = 'denuncias';
        $titulo = 'Denuncias';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_denuncias', 'id_usuarios', 'den_titulo')
                ->display_as('id_denuncias','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('den_titulo','Titulo');
            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function obituarios(){
        $tabla = 'obituarios';
        $titulo = 'Obituarios';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_obituarios', 'id_usuarios', 'nombre_completo')
                ->display_as('id_obituarios','ID')
                ->display_as('id_usuarios','Usuario');
            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function comentarios(){
        $tabla = 'comentarios';
        $titulo = 'Comentarios';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_comentarios', 'id_usuarios', 'tabla_foranea_nombre')
                ->display_as('id_comentarios','ID')
                ->display_as('id_usuarios','Usuario')
                ->display_as('tabla_foranea_nombre','Tabla');
            
            
            $crud->set_relation('id_usuarios','usuarios','{username}');
                        
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    public function getAjaxDetalle(){       
        $this->load->model('imagenes_model');
        
        if ($this->input->is_ajax_request()){
            $id = $this->input->post("id");
            $tabla = $this->input->post("tabla");
            
            $this->load->model($tabla.'_model');

            switch($tabla){
                case 'avisos':
                    $data['detalle'] = $this->avisos_model->get($id);
                    
                    $data['imagenes'] = $this->imagenes_model->get_id_tabla_foranea($tabla, $id);  
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
                case 'mascotas':
                    $data['detalle'] = $this->mascotas_model->get($id);
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
                case 'profesionales':
                    $data['detalle'] = $this->profesionales_model->get($id);
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
                case 'obituarios':
                    $data['detalle'] = $this->obituarios_model->get($id);
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
                case 'denuncias':
                    $data['detalle'] = $this->denuncias_model->get($id);
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
                case 'comentarios':
                    $data['detalle'] = $this->comentarios_model->get($id);
                    
                    $rta = $this->load->view($tabla.'/detalle_view', $data, true);

                break;
            }
            echo $rta;
        }
    }
    
    
    
    
    
    function plot_point_js() {
        error_log(print_r($this->session->userdata,1));
        $retailer_id = $this->session->userdata('retailer_id');
        $retailer = $this->cModel->getByField('retailers', 'rid', $retailer_id);
        if (count($retailer) > 0) {
            $latitude = $retailer['latitude'];
            $longitude = $retailer['longitude'];
            $script = '
                var map;
                var marker;
                var circle;
                var geocoder;
                window.onload = function() {
                    geocoder = new google.maps.Geocoder();
                    var latlng = new google.maps.LatLng(' . $latitude . ',' . $longitude . ');
                    var myOptions = {
                      zoom: 18,
                      center: latlng,
                      mapTypeId: roadmap
                    };
                    map = new google.maps.Map(document.getElementById("retailer-map"), myOptions);
                    addMarker(map.getCenter());
                    google.maps.event.addListener(map,"click", function(event) {
                        //alert("You cannot reset the location by changing pointer in here");
                        //addMarker(event.latLng);
                    });
                }

                function addMarker(location) {
                    if(marker) {marker.setMap(null);}
                    marker = new google.maps.Marker({
                        position: location,
                        draggable: true
                    });
                    marker.setMap(map);
                }
            ';
            echo $script;
        }
    }
    
    
    function show_map_field($value = false, $primary_key = false) {
        return '<p>Seleccione en el mapa la ubicación del comercio...</p>
				<div id="retailer-map" style="width:530px; height:300px;"></div>';
    }
    
    public function usuarios(){
        $tabla = 'usuarios';
        $titulo = 'usuarios';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_usuarios', 'id_facebook', 'username', 'nombre_completo')
                ->display_as('id_usuarios','ID')
                ->display_as('id_facebook','Facebook')
                ->display_as('nombre_usr','Usuario');
            
            //$crud->set_relation('id_inspecciones_tipos','inspecciones_tipos','{ins_tip_nombre}');
            //$crud->unset_add();
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
    
    
    
    
    
    
    
    
    
    
	public function inspecciones(){
        $tabla = 'inspecciones';
        $titulo = 'Inspecciones';
        
		try{
			$crud = new Grocery_CRUD();

            $this->crud = $crud;
            
			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_inspecciones', 'id_empresas', 'titulo', 'id_inspecciones_tipos', 'fecha_creacion')
                ->display_as('id_inspecciones','ID')
                ->display_as('id_empresas','Empresa')
                ->display_as('id_inspecciones_tipos','Tipo')
                ->display_as('titulo','Titulo')
                ->display_as('fecha_creacion','Fecha');
            
            
            //$crud->set_relation('id_empresas','empresas','{emp_nombre}');
            
            //$crud->set_relation('id_formularios','formularios','{for_nombre}', array('id_empresas' => $this->empresa->id_empresas));
            $crud->set_relation('id_formularios','formularios','{for_nombre}');
            
            $crud->add_fields('id_formularios', 'titulo', 'ins_fecha_creacion', 'id_empresas', 'id_users');
            $crud->field_type('id_users', 'hidden', $this->user->id);

            $crud->field_type('id_empresas', 'hidden', $this->empresa->id_empresas);
            
            $crud->callback_after_insert(array($this,'inspecciones_after_insert'));
            
            $success = 'Your data has been successfully stored into the database.<br/>Please wait while you are redirecting to the another page.";';
            $success .= 'window.location = "'.base_url("modulos_principales/redireccionar_inspeccion/");
            
            $crud->set_lang_string('insert_success_message', $success);
            
            //$crud->unset_add();
            
            $output = $crud->render();
            
            //AGREGO LA COLUMNA POR LA QUE VOY A FILTRAR
            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra'] = '';
            
			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    function inspecciones_after_insert($post_array, $primary_key){
        /*error_log(print_r($post_array, TRUE));
        error_log(print_r($primary_key, TRUE));*/
        
        $this->session->set_userdata('last_pk', $primary_key);

        $datos_historial = array(
            "id_users" => $this->user->id,
            "tabla" => 'inspecciones',
            "nombre_tabla" => 'Inspecciones - Encabezado',
            "id_vinculado" => $primary_key,
            "accion" => 'Inserción',
            "descripcion" => 'Insercion de encabezado de nueva inspeccion. Id vinculado: '.$primary_key.' (id_inspecciones)',
            "fecha" => date('Y-m-d'),
            "hora" => date('H:i:s')
        );

        $this->historial_model->cargar($datos_historial);
 
        return true;
    }
    
    function redireccionar_inspeccion(){
        redirect(base_url('modulos_principales/cargar_inspeccion/'.$this->session->userdata('last_pk')),'refresh');
    }
    
    public function ajax_cargar_inspeccion(){
        if ($this->input->is_ajax_request()){
            $id = $this->input->post("id");
            $datos = $this->input->post("datos_formulario");
            $respuestas = $this->input->post("respuestas_formulario");
            
            $this->inspecciones_model->cargarDatosInspeccion($id, $datos);
            
            $this->inspecciones_model->cargarRespuestasInspeccion($id, $respuestas);
            
            $datos_historial = array(
                "id_users" => $this->user->id,
                "tabla" => 'inspecciones',
                "nombre_tabla" => 'Inspecciones - Detalle',
                "id_vinculado" => $this->input->post("id"),
                "accion" => 'Inserción',
                "descripcion" => 'Insercion de detalle de nueva inspeccion. Id vinculado: '.$id.' (id_inspecciones)',
                "fecha" => date('Y-m-d'),
                "hora" => date('H:i:s')
            );

            if ($this->historial_model->cargar($datos_historial)){
                echo true;    
            }else{
                echo false;
            }
        }else{
            echo false;
        }
    }
    
    public function cargar_inspeccion($id){
        $url_actual = base_url();        
        
        $inspeccion = $this->inspecciones_model->GetDetalle($id);
        $idFormulario = $inspeccion->id_formularios;
        $titulo_inspeccion = $inspeccion->titulo;
        $datos_tipos = $this->inspecciones_model->getFormDatosTipos($idFormulario);
        $categorias = $this->inspecciones_model->getCategorias($idFormulario);        
        
        $respuestas = array();
        foreach ($categorias as $row) {
            $respuestas[$row->id_form_categorias] = $this->inspecciones_model->getFormRespuestasxCategoria($row->id_form_categorias);    
        }
   
        $output = (object)array('inspeccion' => $inspeccion, 'datos' => $datos_tipos, 'categorias' => $categorias, 'respuestas' => $respuestas);
        
        $data['judul'] = $titulo_inspeccion;
        $data['crumb'] = array( 'Inspecciones' => 'modulos_principales/inspecciones ', 'Detalle' => '' );
        $data['script_datatables'] = '';
        $data['extra'] = '';
        $data["cuerpo"] = "";
        
        $template = 'admin_template';
        $view = 'inspecciones/cargar';
        
        $this->outputview->output_admin($view, $template, $data, $output);
	}
    
    public function inspeccion($id){
        $url_actual = base_url();        
        
        $inspeccion = $this->inspecciones_model->GetDetalle($id);
        $idFormulario = $inspeccion->id_formularios;
        $titulo_inspeccion = $inspeccion->titulo;
        $datos_tipos = $this->inspecciones_model->GetDatosTipos($id);
        $categorias = $this->inspecciones_model->getCategorias($idFormulario);
        $responsablev = $inspeccion->responsable;
        
        $imagenesv = $this->imagenes_model->lista($id);
        
        $respuestas = array();
        if ($categorias !== false){
            foreach ($categorias as $row) {
                $respuestas[$row->id_form_categorias] = $this->inspecciones_model->getRespuestasxCategoria($id, $row->id_form_categorias);    
            }    
        }
   
        $output = (object)array('inspeccion' => $inspeccion, 'datos' => $datos_tipos, 'categorias' => $categorias, 'respuestas' => $respuestas, 'imagenes' => $imagenesv, 'responsables' => $responsablev);
        
        $data['judul'] = $titulo_inspeccion;
        $data['crumb'] = array( 'Inspecciones' => 'modulos_principales/inspecciones ', 'Detalle' => '' );
        $data['script_datatables'] = '';
        $data['extra'] = "";
        $data["cuerpo"] = "";
        
        $template = 'admin_template';
        $view = 'inspecciones/detalle';
        
        $this->outputview->output_admin($view, $template, $data, $output);
	}
    
    public function detalle_inspeccion(){
		//if (!$this->ion_auth->is_admin())
        if (!$this->ion_auth)
		{
			return show_error('Debes estar identificado en el sistema.');
		}
		else
		{
            if($this->ion_auth->is_admin()){
                $output = (object)array('data' => '' , 'output' => '' , 'js_files' => null , 'css_files' => null);

                $data['judul'] = '';

                $template = 'admin_template';
                $view = 'grocery';
                $this->outputview->output_admin($view, $template, $data, $output);
                      
            }else if ($this->ion_auth->in_group('revendedor')){
                redirect('web/index');    
            }else{
                return show_error('No tienes permisos');     
            }
			
		}
	}
    
    public function formularios(){
        $tabla = 'formularios';
        $titulo = 'Formularios';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->columns('id_inspecciones_tipos', 'for_nombre', 'fecha_creacion')
                ->display_as('id_inspecciones_tipos','Tipo Inspección')
                ->display_as('for_nombre','Nombre')
                ->display_as('fecha_creacion','Fecha');
            
            $crud->set_relation('id_inspecciones_tipos','inspecciones_tipos','{ins_tip_nombre}');
            $crud->unset_add();
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
    
    public function formulario($id){
        $url_actual = base_url();        
        
        $formulario = $this->formularios_model->GetDetalle($id);
        $titulo_formulario = $formulario->for_nombre;
        $datos_tipos = $this->inspecciones_model->getFormDatosTipos($id);
        $categorias = $this->inspecciones_model->getCategorias($id);
        
        $respuestas = array();
        if ($categorias !== false){
            foreach ($categorias as $row) {
                $respuestas[$row->id_form_categorias] = $this->inspecciones_model->getFormRespuestasxCategoria($row->id_form_categorias);    
            }    
        }
   
        $output = (object)array('formulario' => $formulario, 'datos' => $datos_tipos, 'categorias' => $categorias, 'respuestas' => $respuestas);
        
        $data['judul'] = $titulo_formulario;
        $data['crumb'] = array( 'Formularios' => 'modulos_principales/formularios', 'Detalle' => '' );
        $data['script_datatables'] = '';
        $data['extra'] = "";
        $data["cuerpo"] = "";
        
        $template = 'admin_template';
        $view = 'formularios/detalle';
        
        $this->outputview->output_admin($view, $template, $data, $output);
	}
    
    public function estadisticas(){
        $rta = $this->inspecciones_model->estadisticasInspeccionesRealizadas('usuario', $this->user->id);       
        
        $valores = array();
        
        if ($rta !== false){
            foreach ($rta as $valor){
                $valores[$valor->mes - 1] = $valor->cantidad;
            }
        }
        
        for ($i=0; $i < 12; $i++){
            if (!isset($valores[$i])){
                $valores[$i] = 0;    
            }
        }
        ksort($valores);
        
        $valores_cumplimientos = $this->cumplimientos_asignados_model->estadisticasCumplimientos($this->user->id, 2016);
        
        if ($valores_cumplimientos !== false){
            ksort($valores_cumplimientos);
            $valores_cumplimientos_string = implode(", ", $valores_cumplimientos);
        }else{
            $valores_cumplimientos_string = '';    
        }
        
        
        $valores_string = implode(", ", $valores);
        $meses_string = implode(", ", $this->meses);
        
        $inspecciones_tiposv = $this->inspecciones_model->estadisticasInspeccionesTipos($this->user->id);
        
        $html = '';
        if ($inspecciones_tiposv !== false){
            foreach ($inspecciones_tiposv as $row){
                $html.= '{
                    value: '.$row->cantidad.',
                    color: "#'.$row->color1.'",
                    highlight: "#'.$row->color2.'",
                    label: "'.$row->ins_tip_nombre.'"
                },';
            }    
        }
        
        /// ESTADISTICAS GLOBALES DE LA EMPRESA
        $rta = $this->inspecciones_model->estadisticasInspeccionesRealizadas('empresa', $this->user->id_empresas);
        $valores_globales = array();
        
        if ($rta !== false){
            foreach ($rta as $valor){
                $valores_globales[$valor->mes - 1] = $valor->cantidad;
            }
        }
        for ($i=0; $i < 12; $i++){
            if (!isset($valores_globales[$i])){
                $valores_globales[$i] = 0;    
            }
        }
        ksort($valores_globales);
        $valores_globales_string = implode(", ", $valores_globales);
        /////////////////////////////////////
        
        $titulo = 'Estadisticas';
        
		$output = (object)array('data' => '' , 'output' => '' , 'js_files' => null , 'css_files' => null, 'meses' => $meses_string, 'valores' => $valores_string, 'valores_cumplimientos' => $valores_cumplimientos_string, 'valores_globales' => $valores_globales_string, 'valores_torta' => $html );

        $data['judul'] = $titulo;
        $data['crumb'] = array( $titulo => '' );
        $data['extra'] = '';

        $template = 'admin_template';
        $view = 'estadisticas_view';
        $this->outputview->output_admin($view, $template, $data, $output);
	}
    
    public function cumplimientos_asignados($id_user = 0){        
        if (!$this->ion_auth){
			return show_error('Debes estar identificado en el sistema.');
		}else{
            if($this->ion_auth->is_admin() || $this->ion_auth->in_group('encargado')){
                $url_actual = base_url();
        
                /* AGREGO JS Y CSS para el combo de jquery */
                $css_files[sha1($this->css_chosen)] = base_url().$this->css_chosen;

                $js_files[sha1($this->js_jquery)] = base_url().$this->js_jquery;

                $js_lib_files[sha1($this->js_chosen)] = base_url().$this->js_chosen;
                $js_files[sha1($this->js_chosen)] = base_url().$this->js_chosen;

                $js_files[sha1($this->js_config_chosen)] = base_url().$this->js_config_chosen;
                $js_config_files[sha1($this->js_config_chosen)] = base_url().$this->js_config_chosen;

                if($this->ion_auth->in_group('encargado')){
                    $usuariosv = $this->users_model->getTodos($this->empresa->id_empresas);    
                }else{
                    $usuariosv = $this->users_model->getTodos();    
                }
                

                $usuariov = $this->users_model->getDetalle($id_user);

                $output = (object)array(
                                    'meses' => $this->meses, 
                                    'id_user' => $id_user, 
                                    'usuariosv' => $usuariosv, 
                                    'usuariov' => $usuariov,
                                    'js_files' => $js_files, 
                                    'js_lib_files' => $js_lib_files, 
                                    'js_config_files' => $js_config_files, 
                                    'css_files' => $css_files);

                $data['judul'] = 'Asignación de Inspecciones';
                $data['crumb'] = array( 'Usuarios' => 'crud/users', 'Asignación' => '' );
                $data['script_datatables'] = '';
                $data['extra'] = "";
                $data["cuerpo"] = "";

                $template = 'admin_template';
                $view = 'cumplimientos_asignados/principal';

                $this->outputview->output_admin($view, $template, $data, $output);    
            }else{
                return show_error('No tiene permisos para visualizar esta sección.');
            }
        }        
	}
    
    public function ajax_cargar_cumplimientos(){
        if ($this->input->is_ajax_request()){
            $datos = $this->input->post("datos");
            $anio = $this->input->post("anio");
            $user = $this->input->post("user");
            
            $this->cumplimientos_asignados_model->cargarDatos($datos, $anio, $user);
        }else{
            echo false;
        }
    }
    
    public function ajax_cumplimientos_user(){
        if ($this->input->is_ajax_request()){
            $user = $this->input->post("user");
            $anio = $this->input->post("anio");
            
            $this->cumplimientos_asignados_model->estadisticasCumplimientos($user, 2016, 'json');
        }else{
            echo false;
        }     
    }
    
    public function ajax_user_detalle(){
        if ($this->input->is_ajax_request()){
            $user = $this->input->post("user");
            
            $this->users_model->getDetalle($user, 'json');
        }else{
            echo false;
        }       
    }
    
    public function licenciamientos(){
        $tabla = 'licenciamientos';
        $titulo = 'Licenciamientos';
        
		try{
			$crud = new Grocery_CRUD();

			$crud->set_table($tabla);
			$crud->set_subject($titulo);
			$crud->required_fields('pub_nombre');
			$crud->columns('id_licenciamientos', 'id_licencias_tipos', 'id_empresas', 'fecha_baja', 'estado')
                ->display_as('id_licenciamientos','ID')
                ->display_as('id_licencias_tipos','Tipo de Licencia')
                ->display_as('id_empresas','Empresa')
                ->display_as('fecha_baja','Baja');

			$crud->set_relation('id_licencias_tipos','licencias_tipos','lic_nombre');
            $crud->set_relation('id_empresas','empresas','emp_nombre');
            
			$output = $crud->render();

            $script_datatables = $this->script_datatables.', "order": [[ 0, "desc" ]]';
            
			$data['judul'] = $titulo;
			$data['crumb'] = array( $titulo => '' );
            $data['script_datatables'] = $script_datatables;
            $data['extra_info'] = '';
            
            
            $data['extra'] = $this->load->view('test_view', '', true);

			$template = 'admin_template';
			$view = 'grocery';
			$this->outputview->output_admin($view, $template, $data, $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}
    
    function generar_thumb_callback($uploader_response,$field_info, $files_to_upload){
        $this->load->library('image_moo');

        //Is only one file uploaded so it ok to use it with $uploader_response[0].
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        
        $new_file = $field_info->upload_path.'/thumb_'.$uploader_response[0]->name; 
        
        $this->image_moo->load($file_uploaded)->resize(290, 145)->save($new_file,true);

        return true;
    }
    
    function cambiarEstado($id, $value){
        
        $data = array(
               'activo' => $value
            );

        $this->db->where('id_vehiculos', $id);
        $this->db->update('vehiculos', $data);
        
		redirect('modulos_principales/vehiculos');
	}
    
    function cbklocalidades(){

        //creamos el combo
        $combo = '<select name="id_localidades" class="chosen-select" data-placeholder="Seleccionar localidad" style="width: 300px; display: none;">';

    $fincombo = '</select>';

        //Tomamos el id de inmueble si se enviocomo parámetro por url
        $id_vehiculo = $this->uri->segment(4);


        //Verificamos la operacion que estamos haciendo si agregamos o editamos
        $crud = new Grocery_CRUD();
        $estado = $crud->getState();

        //Si estamos editando y el id de inmueble no es vació

        if(isset($id_vehiculo) && $estado == "edit") {
            //consultamos la provincia y la localidad actual del inmueble
            $this->db->select('id_provincias, id_localidades')
                ->from('vehiculos')
                ->where('id_vehiculos', $id_vehiculo);


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
    
    function buscarlocalidades(){
		$id_provincia = $this->uri->segment(3);
		
		$this->db->select("*")
				 ->from('localidades')
				 ->where('id_provincias', $id_provincia);
		$db = $this->db->get();
		
		$array = array();
		foreach($db->result() as $row):
			$array[] = array("value" => $row->id_localidades, "property" => $row->loc_nombre);
		endforeach;
		
		echo json_encode($array);
		exit;
	}
    
    function cbkcategorias(){

        //creamos el combo
        $combo = '<select name="id_categorias" class="chosen-select" data-placeholder="Seleccionar categoria" style="width: 300px; display: none;">';

    $fincombo = '</select>';

        //Tomamos el id de inmueble si se enviocomo parámetro por url
        $id_comercio = $this->uri->segment(4);


        //Verificamos la operacion que estamos haciendo si agregamos o editamos
        $crud = new Grocery_CRUD();
        $estado = $crud->getState();

        //Si estamos editando y el id de inmueble no es vació

        if(isset($id_vehiculo) && $estado == "edit") {
            //consultamos la provincia y la localidad actual del inmueble
            $this->db->select('id_rubros, id_categorias')
                ->from('comercios')
                ->where('id_comercios', $id_comercio);


            $db = $this->db->get();
            $row = $db->row(0);

            $id_rubro = $row->id_rubros;
            $id_categoria = $row->id_categorias;
            
            //Cargamos el combo con todas las localidades de la pronvincia

            $this->db->select('*')
                ->from('categorias')
                ->where('id_rubros', $id_rubros);

            $db = $this->db->get();
            
            //Si ecnontramos el id de localidad actual lo ponemos como selecionado
            //sino seguimos cargando las demas localidades
            foreach($db->result() as $row):
                if($row->id_categorias == $id_categorias) {
                    $combo .= '<option value="'.$row->id_categorias.'" selected="selected">'.$row->cat_nombre.'</option>';
                } else {
                    $combo .= '<option value="'.$row->id_categorias.'">'.$row->cat_nombre.'</option>';
                }
            endforeach;
            
            //Devolvemos el combo cargado

            return $combo.$fincombo;
        } else {
            return $combo.$fincombo;
        }
    }
    
    function buscarcategorias(){
		$id_rubro = $this->uri->segment(3);
		
		$this->db->select("*")
				 ->from('categorias')
				 ->where('id_rubros', $id_rubro);
		$db = $this->db->get();
		
		$array = array();
		foreach($db->result() as $row):
			$array[] = array("value" => $row->id_categorias, "property" => $row->cat_nombre);
		endforeach;
		
		echo json_encode($array);
		exit;
	}
        
}