<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Clientes extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('clientes_model');
    }
    public function index_get(){
        $clientes = $this->clientes_model->get();
        
        if (! is_null ($clientes)){
            $this->response(array('response' => $clientes), 200);
        }else{
            $this->response(array('error' => 'No hay clientes'), 400);
        }
    }
    
    public function find_get($id){
        $cliente = $this->clientes_model->get($id);
        
        if (! $id){
            $this->response(NULL, 400);
        }
        
        if (! is_null ($cliente)){
            $this->response(array('response' => $cliente), 200);
        }else{
            $this->response(array('error' => 'No se encuentra el cliente'), 400);
        }
        
        
    }
    
    public function index_post(){
        
        if (! $this->post('cliente')){
            $this->response(NULL, 400);
        }
        
        $id_clientes = $this->clientes_model->save($this->post('cliente'));
        
        if (! is_null ($id_clientes)){
            $this->response(array('response' => $id_clientes), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function index_put($id){
    
        if (! $this->post('cliente') || ! $id){
            $this->response(NULL, 400);
        }
        
        $update = $this->clientes_model->update($id, $this->post('cliente'));
        
        if (! is_null ($update)){
            $this->response(array('response' => 'Cliente actualizado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $delete = $this->clientes_model->delete($id);
        
        if (! is_null ($delete)){
            $this->response(array('response' => 'Cliente eliminado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }    
    }
}

?>