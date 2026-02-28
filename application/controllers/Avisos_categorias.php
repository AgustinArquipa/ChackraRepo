<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Avisos_categorias extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('avisos_categorias_model');
    }
    
    public function index_get(){
        $categorias = $this->avisos_categorias_model->get();
        
        /*foreach ($rubros as $row){
            $categorias = $this->avisos_categorias_model->get($row->id_rubros);
        }*/
        if (! is_null ($categorias)){
            $this->response(array('response' => $categorias), 200);
        }else{
            $this->response(array('error' => 'No hay categorias cargadas.'), 400);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $categoria = $this->avisos_categorias_model->get($id);
        
        if (! is_null ($categoria)){
            $this->response(array('response' => $categoria), 200);
        }else{
            $this->response(array('error' => 'No se encuentra la categoria buscada.'), 400);
        }
    }
}

?>