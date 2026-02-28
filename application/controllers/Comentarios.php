<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Comentarios extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('comentarios_model');
        $this->carpeta = 'comentarios';
    }
    
    public function index_get($tabla, $id, $limit = 0, $offset = 0){
        $cant_total = $this->comentarios_model->getCantTotal($tabla, $id);
        $comentarios = $this->comentarios_model->get(NULL, $tabla, $id, $limit, $offset);
        
        if (! is_null ($comentarios)){
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $comentarios)), 200);
        }else{
            $this->response(array('response' => 'No hay comentarios cargados.'), 200);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $comentario = $this->comentarios_model->get($id);
        
        if (! is_null ($comentario)){
            $this->response(array('response' => $comentario), 200);
        }else{
            $this->response(array('response' => 'No se encuentra el comentario buscado.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){
        
        if (! $id_usuario){
            $this->response(NULL, 400);
        }
        
        $comentarios = $this->comentarios_model->get_usuario($id_usuario);
        
        if (! is_null ($comentarios)){
            $this->response(array('response' => $comentarios), 200);
        }else{
            $this->response(array('response' => 'No se encuentran comentarios asociados al usuario seleccionado.'), 200);
        }
    }
    
    public function new_post($type = 'php'){        
        //log_message('error', print_r($_FILES,1));        
                
        if ($type == 'json'){
            $comentario = json_decode($this->post('comentario'),true);    
        }else{
            $comentario = $this->post('comentario');
        }
        
        $id_comentario = $this->comentarios_model->save($comentario);
        
        if (! is_null ($id_comentario)){
            $response_200['comentario'] = 'Comentario cargado correctamente!.';
            $response_200['id_cargado'] = $id_comentario;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar el comentario.'), 400);
        }
        
    }
    
    
    public function update_post($id, $type = 'php'){
        
        if ($type == 'json'){
            $comentario = json_decode($this->post('comentario'),true);    
        }else{
            $comentario = $this->post('comentario');
        }
        
        $id_comentario = $this->comentarios_model->update($comentario);

        if (! is_null ($id_comentario)){
            $this->response(array('response' => $id_comentario), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar el comentario.'), 400);
        }
        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $delete = $this->comentarios_model->delete($id);
        
        if (! is_null ($delete)){
            $this->response(array('response' => 'Comentario eliminado.'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el comentario.'), 400);
        }    
    }
}

?>