<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Profesionales_rubros extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('profesionales_rubros_model');
    }
    
    public function index_get(){
        $rubros = $this->profesionales_rubros_model->get();
        
        /*foreach ($rubros as $row){
            $rubros = $this->profesionales_rubros_model->get($row->id_rubros);
        }*/
        if (! is_null ($rubros)){
            $this->response(array('response' => $rubros), 200);
        }else{
            $this->response(array('error' => 'No hay rubros cargados.'), 400);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(NULL, 400);
        }
        
        $categoria = $this->profesionales_rubros_model->get($id);
        
        if (! is_null ($categoria)){
            $this->response(array('response' => $categoria), 200);
        }else{
            $this->response(array('error' => 'No se encuentra el rubro buscado.'), 400);
        }
    }
}

?>