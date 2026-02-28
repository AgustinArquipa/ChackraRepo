<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Avisos_subcategorias extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('avisos_subcategorias_model');
    }
    
    public function index_get(){
        $subcategorias = $this->avisos_subcategorias_model->get();
        
        /*foreach ($rubros as $row){
            $subcategorias = $this->avisos_subcategorias_model->get($row->id_rubros);
        }*/
        if (! is_null ($subcategorias)){
            $this->response(array('response' => $subcategorias), 200);
        }else{
            $this->response(array('error' => 'No hay subcategorias cargadas.'), 400);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $subcategoria = $this->avisos_subcategorias_model->get($id);
        
        if (! is_null ($subcategoria)){
            $this->response(array('response' => $subcategoria), 200);
        }else{
            $this->response(array('error' => 'No se encuentra la subcategoria buscada.'), 400);
        }
    }
    
    public function find_categoria_get($id_categoria){
        
        if (! $id_categoria){
            $this->response(NULL, 400);
        }
        
        $subcategorias = $this->avisos_subcategorias_model->get_categoria($id_categoria);
        
        if (! is_null ($subcategorias)){
            $this->response(array('response' => $subcategorias), 200);
        }else{
            $this->response(array('error' => 'No se encuentran subcategorias asociadas a la categoria seleccionada.'), 400);
        }
    }
    
}

?>