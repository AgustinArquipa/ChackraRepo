<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Categorias extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('categorias_model');
    }
    
    public function index_get(){
        $categorias = $this->categorias_model->get();
        
        if (! is_null ($categorias)){
            $this->response(array('response' => $categorias), 200);
        }else{
            $this->response(array('error' => 'No hay categorias cargadas'), 400);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $categoria = $this->categorias_model->get($id);
        
        if (! is_null ($categoria)){
            $this->response(array('response' => $categoria), 200);
        }else{
            $this->response(array('error' => 'No se encuentra la categoria'), 400);
        }
    }
    
    public function find_rubro_get($id_rubro = NULL){
        $categorias = $this->categorias_model->get(NULL, $id_rubro);
        
        if (! is_null ($categorias)){
            $this->response(array('response' => $categorias), 200);
        }else{
            $this->response(array('error' => 'No hay categorias para el rubro seleccionado'), 400);
        }
    }
    
    public function find_facebook_get($id){
        $cliente = $this->comercios_model->get_facebook($id);
        
        if (! $id){
            $this->response(NULL, 400);
        }
        
        if (! is_null ($cliente)){
            $this->response(array('response' => $cliente), 200);
        }else{
            $this->response(array('error' => 'No se encuentra el comercio'), 400);
        }
        
        
    }
    
    public function index_post(){
        
        if (! $this->post('comercio')){
            $this->response(NULL, 400);
        }
        
        $id_comercio = $this->comercios_model->save($this->post('comercio'));
        
        if (! is_null ($id_comercio)){
            $this->response(array('response' => $id_comercio), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function index_put($id){
    
        if (! $this->put('comercio') || ! $id){
            $this->response(NULL, 400);
        }
        
        $update = $this->comercios_model->update($id, $this->put('comercio'));
        
        if (! is_null ($update)){
            $this->response(array('response' => 'Comercio actualizado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }
        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $delete = $this->comercios_model->delete($id);
        
        if (! is_null ($delete)){
            $this->response(array('response' => 'Comercio eliminado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar.'), 400);
        }    
    }
}

?>