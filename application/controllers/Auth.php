<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper('language');
		$this->lang->load('auth');
		$this->load->library('OutputView');
        $this->load->library('session');
        $this->load->model("empresas_model");
        $this->load->model("historial_model");
	}

	function index()
	{
		if (!$this->ion_auth->logged_in()){
			redirect('auth/login');
		}else{
            redirect('crud/index');
		}
	}

	public function login()
	{
		$data['redirect'] = site_url('crud/index');
		$view             = 'auth/login';
		$template         = 'auth_template';
		$this->outputview->output_front($view, $template, $data);
	}

	function ajax_login(){
		$remember = (bool) $this->input->post('remember');
        $comisionable = (bool) $this->input->post('modo_comisionable');
	
        $this->session->set_userdata('comisionable', $comisionable);
        
		if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)){
        
            $user = $this->ion_auth->user()->row();

            $datos_historial = array(
                "id_users" => $user->id,
                "tabla" => 'users',
                "nombre_tabla" => 'Usuarios',
                "id_vinculado" => $user->id,
                "accion" => 'Logueo',
                "descripcion" => 'Ingreso al sistema.',
                "fecha" => date('Y-m-d'),
                "hora" => date('H:i:s')
            );
		}else{
			echo "false";
		}
	}
    
    function ajax_login_movil(){
		$remember = (bool) $this->input->post('remember');
        
		if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)){
            $empresa_user = $this->empresas_model->GetEmpresa($this->ion_auth->user()->row()->id_empresas);
            
            $licencia = $this->empresas_model->getUltimaLicencia($this->ion_auth->user()->row()->id_empresas);
            
            $datetime1 = date_create();
            $datetime2 = date_create($licencia->fecha_baja);
            $interval = date_diff($datetime1, $datetime2);
            $empresa_user->licencia_dias = $interval->format('%a');
            $empresa_user->licencia_signo = $interval->format('%R');
            
                
            $this->session->set_userdata('empresa', $empresa_user);
            $user = $this->ion_auth->user()->row();
            
            unset($user->password); //NO LO ENVIO PORQUE ME TRAE PROBLEMAS CON LOS CARACTERES ESPECIALES
            
            $rta = array('user'=>$user, 'empresa'=>$empresa_user);
            
            $rta = json_encode($rta);
            
            if ($empresa_user->licencia_signo == '-'){
                echo $rta;
            }else{
                $datos_historial = array(
                    "id_users" => $user->id,
                    "tabla" => 'users',
                    "nombre_tabla" => 'Usuarios',
                    "id_vinculado" => $user->id,
                    "accion" => 'Logueo',
                    "descripcion" => 'Ingreso al sistema a traves de App.',
                    "fecha" => date('Y-m-d'),
                    "hora" => date('H:i:s')
                );
                
                echo $rta;
                
            }
		}else{
			echo "false";
		}
	}

	public function logout()
	{
        $user = $this->ion_auth->user()->row();
        
		$logout = $this->ion_auth->logout();
        
        $datos_historial = array(
                    "id_users" => $user->id,
                    "tabla" => 'users',
                    "nombre_tabla" => 'Usuarios',
                    "id_vinculado" => $user->id,
                    "accion" => 'Deslogueo',
                    "descripcion" => 'Salida del sistema.',
                    "fecha" => date('Y-m-d'),
                    "hora" => date('H:i:s')
                );

        $this->historial_model->cargar($datos_historial);
        
		redirect('auth/login');
	}

}