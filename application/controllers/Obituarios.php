<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Obituarios extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('obituarios_model');
        $this->load->library('image_moo');
        $this->carpeta = 'obituarios';
    }
    
    public function index_get($limit = 0, $offset = 0){
        $cant_total = $this->obituarios_model->getCantTotal();
        $obituarios = $this->obituarios_model->get(NULL, $limit, $offset);        
        if (! is_null ($obituarios)){
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $obituarios)), 200);
        }else{
            $this->response(array('response' => 'No hay obituarios cargados.'), 200);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Obituario'), 400);
        }        
        $obituario = $this->obituarios_model->get($id);
        if (! is_null ($obituario)){
            $this->response(array('response' => $obituario), 200);
        }else{
            $this->response(array('response' => 'No se encuentra el obituario buscado.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){        
        if (! $id_usuario){
            $this->response(array('error' => 'Faltan datos: ID del Usuario del Obituario'), 400);
        }
        $obituarios = $this->obituarios_model->get_usuario($id_usuario);
        if (! is_null ($obituarios)){
            $this->response(array('response' => $obituarios), 200);
        }else{
            $this->response(array('response' => 'No se encuentran obituarios asociadps al usuario seleccionado.'), 200);
        }
    }
    
    public function new_post($type = 'php'){        
        //log_message('error', print_r($_FILES,1));
        if (isset($_FILES)){
            $files = $_FILES;
            $cpt = count($_FILES);            
            $carpeta = 'obituarios';
            $config['upload_path'] = 'assets/uploads/'.$carpeta;
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = '2000';
            $config['max_width'] = '2024';
            $config['max_height'] = '2008';            
            $imagenesv = array();            
            for($i=0; $i<$cpt; $i++){
                $nombre = $files[$i]['name'];
                $nombre_temporal = $files[$i]['tmp_name'];                
                $imagen = $nombre;
                $extension = pathinfo($imagen, PATHINFO_EXTENSION);
                $nombre_unico = uniqid('o_').'.'.$extension;
                $config['file_name'] = $nombre_unico;
                $ruta_base = "assets/uploads/".$this->carpeta."/";
                $ruta = $ruta_base . $nombre_unico;
                $imagenesv[$i] = $nombre_unico;
                if (!move_uploaded_file($nombre_temporal, $ruta)) {
                    //fwrite($file, "NO - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                    //$error = array('error' => $this->upload->display_errors());                    
                    $this->response(array('error' => 'Oops! Ocurrio un error al cargar fotos.'), 400);
                }else{
                    /* CREO EL THUMB CORRESPONDIENTE */
                    $file = $ruta_base.$imagenesv[$i]; 
                    $file_uploaded = $ruta_base.'thumb-50-'.$nombre_unico;
                    $this->image_moo->load($file)->resize_crop(50,50)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);                    
                    $file_uploaded = $ruta_base.'thumb-200-'.$nombre_unico;
                    $this->image_moo->load($file)->resize_crop(200,200)->save($file_uploaded,true);
                    $response_200['fotos'] = 'Fotos y thumbs cargados con exito.';
                }  
            }
        }else{
            $this->response(array('error' => 'Oops! No envió fotos para cargar.'), 400);   
        }       
        if ($type == 'json'){
            $obituario = json_decode($this->post('obituario'),true);    
        }else{
            $obituario = $this->post('obituario');
        }
        if (count($imagenesv) > 0){
            $obituario['obi_imagen'] = $imagenesv[0];
        }else{
            $obituario['obi_imagen'] = '';    
        }
        $id_obituario = $this->obituarios_model->save($obituario);
        if (! is_null ($id_obituario)){
            $response_200['message'] = 'Obituario cargado correctamente!.';
            $response_200['id_cargado'] = $id_obituario;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar el obituario.'), 400);
        }
    }
    
    
    public function update_post($id, $type = 'php'){        
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);            
            $carpeta = 'obituarios';
            $config['upload_path'] = 'assets/uploads/'.$carpeta;
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = '2000';
            $config['max_width'] = '2024';
            $config['max_height'] = '2008';            
            $imagenesv = array();            
            for($i=0; $i<$cpt; $i++){
                $nombre = $files[$i]['name'];
                $nombre_temporal = $files[$i]['tmp_name'];                
                $imagen = $nombre;
                $extension = pathinfo($imagen, PATHINFO_EXTENSION);
                $nombre_unico = uniqid('m_').'.'.$extension;
                $config['file_name'] = $nombre_unico;
                $ruta = "assets/uploads/".$carpeta."/" . $nombre_unico;
                $imagenesv[$i] = $nombre_unico;
                if (!move_uploaded_file($nombre_temporal, $ruta)) {
                    //fwrite($file, "NO - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                    //$error = array('error' => $this->upload->display_errors());                    
                    $this->response(array('error' => 'Oops! Ocurrio un error al cargar fotos.'), 400);
                }  
            }  
        }else{
            $this->response(array('error' => 'Oops! No envió fotos para cargar.'), 400);   
        }         
        if ($type == 'json'){
            $obituario = json_decode($this->post('obituario'),true);    
        }else{
            $obituario = $this->post('obituario');
        }
        if (isset($imagenesv)){
            $i = 1;
            foreach($imagenesv as $valor){
                $obituario['url'.$i] = $imagenesv[$i - 1];
                $i++;
            }
        }        
        $id_obituario = $this->obituarios_model->update($obituario);
        if (! is_null ($id_obituario)){
            $this->response(array('response' => $id_obituario), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar el obituario.'), 400);
        }        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Obituario'), 400);
        }        
        $delete = $this->obituarios_model->delete($id);
        if (! is_null ($delete)){
            $this->response(array('response' => 'Obituario eliminado'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el obituario.'), 400);
        }    
    }
}
?>