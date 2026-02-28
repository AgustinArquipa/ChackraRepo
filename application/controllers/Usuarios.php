<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Usuarios extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('usuarios_model');
		$this->load->model('obras_sociales_model');
    }
    
    public function index_get($limit = 0, $offset = 0){
        $usuarios = $this->usuarios_model->get(NULL, $limit, $offset);
        
        if (! is_null ($usuarios)){
            $this->response(array('response' => $usuarios), 200);
        }else{
            $this->response(array('error' => 'No hay usuarios cargados'), 400);
        }
    }
    
    public function find_get($id){
        $usuario = $this->usuarios_model->get($id);
        
        if (! $id){
            $this->response(NULL, 400);
        }
        
        if (! is_null ($usuario)){
            $this->response(array('response' => $usuario), 200);
        }else{
            $this->response(array('error' => 'No se encuentra el usuario'), 400);
        }
        
        
    }
    
    public function find_facebook_get($id){
        $usuario = $this->usuarios_model->get_facebook($id);
        
        if (! $id){
            $this->response(NULL, 400);
        }
        
        if (! is_null ($usuario)){
            $this->response(array('response' => $usuario), 200);
        }else{
            $this->response(array('error' => 'No se encuentra el usuario'), 400);
        }
        
        
    }
    
    public function face_post($type = 'php'){
        if (! $this->post('usuario')){
            $this->response(NULL, 400);
        }
        
        if ($type == 'json'){
            $usuario = json_decode($this->post('usuario'),true);    
        }else{
            $usuario = $this->post('usuario');
        }
        
        //print_r ($usuario);
        
        if ($usuario['email'] == ''){
            $usuario['email'] = '-';
        }
        
        if ($usuario['foto_perfil'] == ''){
            $usuario['foto_perfil'] = NULL;
        }
        
        $usrFaceBD = $this->usuarios_model->existeFacebookId($usuario['id_facebook']);
        
        if(!$usrFaceBD){
            
            $usrv = $this->usuarios_model->existeEmail($usuario['email']);
            
            if(!$usrv){

                $date = new DateTime();
                $tiempo = $date->getTimestamp();
                
                $data = array(
                    'id_facebook' => $usuario['id_facebook'],
                    'username' => $usuario['username'],
                    'nombre_completo' => '-',
                    'email' => $usuario['email'], 
                    'telefono' => '-',
                    'foto_perfil' => $usuario['foto_perfil'],
                    'id_notificacion' => $usuario['id_notificacion'],
                    'fecha_envio_movil' => $usuario['fecha_envio_movil']
                );
                
                /*
                    {
                        "id_facebook": "999999",
                        "username": "negrohjf85@hotmail.com",
                        "nombre_completo": "-",
                        "email": "negrohjf85@hotmail.com",
                        "telefono": "-",
                        "foto_perfil": "-",
                        "fecha_envio_movil": "2017-10-09 10:15:10"
                    }
                */

                $id_usuario = $this->usuarios_model->save($data);
				
				$url = $usuario['foto_perfil'];
				//$filename = substr($url, strrpos($url, '/') + 1);
				$filename = "user_".$id_usuario.".jpg";			
				file_put_contents('assets/uploads/usuarios/'.$filename, file_get_contents($url));
				
				$data = array(
					'foto_perfil' => $filename
				);

				$this->usuarios_model->enlazarFacebook($data, $id_usuario);

                $this->response(array('response' => array("success" => 2, "message" => 'Se registro el usuario', 'id' => $id_usuario)), 200);

            }else{
                $url = $usuario['foto_perfil'];
				//$filename = substr($url, strrpos($url, '/') + 1);
				$filename = "user_".$usrv->id_usuarios.".jpg";			
				file_put_contents('assets/uploads/usuarios/'.$filename, file_get_contents($url));
				
                $data = array(
                    'id_facebook' => $usuario['id_facebook'],
                    'telefono' => '-',
                    'foto_perfil' => $filename,
					'id_notificacion' => $usuario['id_notificacion']
                );
				
                $this->usuarios_model->enlazarFacebook($data, $usrv->id_usuarios);
                
                $this->response(array('response' => array("success" => 3, "message" => 'Se vinculo la cuenta del usuario', 'id' => $usrv->id_usuarios)), 200);
            }      
        }else{
			$url = $usuario['foto_perfil'];
			//$filename = substr($url, strrpos($url, '/') + 1);
			$filename = "user_".$usrFaceBD->id_usuarios.".jpg";			
			file_put_contents('assets/uploads/usuarios/'.$filename, file_get_contents($url));
			
			$data = array(
				'foto_perfil' => $filename,
				'id_notificacion' => $usuario['id_notificacion']
			);
			
			$this->usuarios_model->enlazarFacebook($data, $usrFaceBD->id_usuarios);
            
            $this->response(array('response' => array("success" => 1, "message" => 'Ya existe Usuario', 'id' => $usrFaceBD->id_usuarios)), 200);
        }
    }
    
    public function new_post($type = 'php'){
        
        if (! $this->post('usuario')){
            $this->response(NULL, 400);
        }
        
        if ($type == 'json'){
            $usuario = json_decode($this->post('usuario'),true);    
        }else{
            $usuario = $this->post('usuario');
        }
        
        $id_usuarios = $this->usuarios_model->save($usuario);
        
        if (! is_null ($id_usuarios)){
            $this->response(array('response' => $id_usuarios), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function update_post($id, $type = 'php'){

        if (! $this->post('usuario') || ! $id){
            $this->response(NULL, 400);
        }
        
        if ($type == 'json'){
            $usuario = json_decode($this->post('usuario'),true);    
        }else{
            $usuario = $this->post('usuario');
        }
		
		//log_message("ERROR", "Datos: ".$usuario);
        
        $update = $this->usuarios_model->update($id, $usuario);
        
        if (! is_null ($update)){
            $this->response(array('response' => 'Usuario actualizado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $delete = $this->usuarios_model->delete($id);
        
        if (! is_null ($delete)){
            $this->response(array('response' => 'Usuario eliminado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar.'), 400);
        }    
    }
	
	public function obras_sociales_get(){
		$os = $this->obras_sociales_model->get();

		if (! is_null ($os)){
			$this->response($os, 200);
		}else{
			$this->response(array(), 200);
		}
	}
}

?>