<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Panel_turnero extends CI_Controller {

    function __construct(){
		parent::__construct();
		$this->load->helper('form');
        $this->load->library('session');
		$this->load->library('OutputView');
        
        $this->load->library('ion_auth');
        $this->load->library('pagination'); //Cargamos la librería de paginación
        
        $this->user = $this->ion_auth->user()->row();
		
		$this->load->model('tur_turnos_model');
		$this->load->model('tur_prestadores_model');
		$this->load->model('tur_instituciones_model');
		$this->load->model('tur_instituciones_usuarios_model');
    }
	
	public function identificacion(){
        if ($this->session->userdata('usuario') != null){
			redirect('panel_turnero/index'); 
		}else{
            $data['redirect'] = site_url('panel_turnero/index');
            $view             = 'tur_instituciones_usuarios/login_view';
            $template         = 'web_template';
            $this->outputview->output_front($view, $template, $data); 
		}       
    }
	
	public function registrar(){   
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nombre', 'Nombre','trim|required');
        $this->form_validation->set_rules('apellido', 'Apellido','trim|required');
        $this->form_validation->set_rules('dni', 'DNI','trim|required');
        //$this->form_validation->set_rules('genero', 'Genero','trim|required');
        $this->form_validation->set_rules('email','Email','trim|valid_email|required');
        $this->form_validation->set_rules('password','Clave','trim|min_length[6]|max_length[20]|required');
        $this->form_validation->set_rules('confirm_password','Confirmacion Clave','trim|matches[password]|required');
        
        if($this->form_validation->run()===FALSE){
            $data['errors'] = validation_errors();
            $data['redirect'] = site_url('usuarios/perfil');
            $data['sidebar_izq'] = '';
            //$data['tabla_dropdown'] = $this->load->view('articulos/tabla_dropdown_view', array(), TRUE);
            
            $view             = 'tur_instituciones_usuarios/registrar_view';
            $template         = 'web_template';
            $this->outputview->output_front($view, $template, $data);
        }else{
            $nombre = $this->input->post('nombre');
            $apellido = $this->input->post('apellido');
            $dni = $this->input->post('dni');
            $fecha_nac = str_replace ('/','-',$this->input->post('fecha_nac'));
            $fecha_nac = $this->fechaMySQL($fecha_nac);
            $genero = $this->input->post('genero');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $password2 = $this->input->post('confirm_password');

            $username = $email;
            
            $group 	  = array (3);
            $additional_data     = array();
            
            $additional_data = array(
                'nombre'            => $nombre,
                'apellido'          => $apellido,
                'dni'               => $dni,
                'id_provincias'     => 1,
                'id_localidades'    => 1/*,
                'fecha_nac'         => $fecha_nac,
                'genero'            => $genero*/
            );
            
            $this->load->library('ion_auth');
            $user = $this->ion_auth->register($username,$password,$email,$additional_data,$group);

            //SI SE DIO DE ALTA CORRECTAMENTE, DEBO CARGAR LOS DATOS EN LAS TABLAS RELACIONADAS...
            //PARA DESPUES SOLO ACTUALIZAR CUANDO EL USUARIO LLENE LOS FORMULARIOS CORRESPONDIENTES
            if($user){ 
                log_message('ERROR',$user);
                $nombre_institucion = $this->input->post('ins_nombre');
                $cuit_institucion = $this->input->post('ins_cuit');
                
                $this->cargar_institucion($user, $nombre_institucion, $cuit_institucion);
                
                $this->session->set_flashdata('auth_message', 'Cuenta creada con exito. Por favor verifique su casilla de email para poder verificar y activar su cuenta. Muchas Gracias!');
                redirect('usuarios/login'.$id);
            }else{
                $this->session->set_flashdata('auth_message', $this->ion_auth->errors());
                redirect('usuarios/registrar');
            }
        }
	}
	
	function cargar_institucion($id_usuario, $nombre_institucion, $cuit_institucion){
        $datos = array(
            'ins_nombre' => $nombre_institucion,
            'ins_cuit' => $cuit_institucion
        );
        $id_institucion = $this->tur_instituciones_model->save($datos);
		
		$datos = array(
            'id_usuarios' => $id_usuario,
            'id_tur_instituciones' => $id_institucion
        );
		
		$id_ins_usr = $this->tur_instituciones_usuarios_model->save($datos);
		
        $this->session->set_userdata('id_institucion', $id_institucion);
    }
    
    public function index(){
		//print_r($this->session->userdata('usuario'));
		if ($this->session->userdata('usuario') == null){
            $this->session->set_flashdata('login', 'Necesita iniciar sesion para ver sus datos.');
			redirect('panel_turnero/identificacion'); 
		}else{
            $data['usuario'] = $this->session->userdata('usuario');
			$turnosv = $this->tur_turnos_model->get_instituciones(NULL, date("y-m-d"));
			//print_r($data['usuario']);
			$data['prestadores'] = $this->tur_prestadores_model->get(NULL, NULL, NULL, NULL, NULL, $data['usuario']['id_tur_instituciones']);
			
			$data['turnos_ordenados'] = array();
			
			foreach($turnosv as $turno){
				$data['turnos_ordenados'][$turno['tur_inicio']][] = $turno;
			}
			
			$data['fecha'] = date("d/m/Y");
			
			//print_r($data['turnos_ordenados']);
			
			//print_r($turnosv);
            $view             = 'tur_instituciones/index_view';
            $template         = 'web_template';
            $this->outputview->output_front($view, $template, $data); 
		}       
    }
	
	public function prestadores(){
		if ($this->session->userdata('usuario') == null){
            $this->session->set_flashdata('login', 'Necesita iniciar sesion para ver sus datos.');
			redirect('panel_turnero/identificacion'); 
		}else{
            $data['usuario'] = $this->session->userdata('usuario');
			$data['prestadores'] = $this->tur_prestadores_model->get(NULL, NULL, NULL, NULL, NULL, $data['usuario']['id_tur_instituciones'], 'admin');
			
			//print_r($data['usuario']);
			
			$data['listado_prestadores'] = $this->load->view('tur_prestadores/listado_view', $data, true);
			
            $view             = 'tur_prestadores/index_view';
            $template         = 'web_template';
            $this->outputview->output_front($view, $template, $data); 
		}       
    }
	
	public function cerrar_sesion(){
        if ($this->session->userdata('usuario') == null){
			redirect('panel_turnero/index'); 
		}else{
            $this->session->unset_userdata('usuario');
			redirect('panel_turnero/index'); 
		}       
    }
	
}