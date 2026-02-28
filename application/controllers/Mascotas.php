<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Mascotas extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('mascotas_model');
        $this->load->model('comentarios_model');
        $this->load->library('image_moo');
        $this->carpeta = 'mascotas';
    }
    
    public function index_get($limit = 0, $offset = 0){
        $cant_total = $this->mascotas_model->getCantTotal();
        $mascotas = $this->mascotas_model->get(NULL, $limit, $offset);        
        if (! is_null ($mascotas)){
            for($i=0; $i < count($mascotas); $i++){
                $mascotas[$i]['comentarios_totales'] = $this->comentarios_model->getCantTotal($this->carpeta, $mascotas[$i]['id_mascotas']); 
                
                if (is_null($mascotas[$i]['comentarios_totales'])){
                    $mascotas[$i]['comentarios_totales'] = 0;
                }
            }
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $mascotas)), 200);
        }else{
            $this->response(array('response' => 'No hay mascotas cargadas.'), 200);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response('Faltan datos: ID de la Mascota', 400);
        }        
        $mascota = $this->mascotas_model->get($id);
        if (! is_null ($mascota)){
            $this->response(array('response' => $mascota), 200);
        }else{
            $this->response(array('response' => 'No se encuentra la mascota buscada.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){        
        if (! $id_usuario){
            $this->response('Faltan datos: ID del Usuario de la Mascota', 400);
        }        
        $mascotas = $this->mascotas_model->get_usuario($id_usuario);
        if (! is_null ($mascotas)){
            $this->response(array('response' => $mascotas), 200);
        }else{
            $this->response(array('response' => 'No se encuentran mascotas asociadas al usuario seleccionado.'), 200);
        }
    }
    
    public function new_post($type = 'php'){        
        //log_message('error', print_r($_FILES,1));  
        if (isset($_FILES)){
            $files = $_FILES;
            $cpt = count($_FILES);            
            $config['upload_path'] = 'assets/uploads/'.$this->carpeta;
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
            $mascota = json_decode($this->post('mascota'),true);    
        }else{
            $mascota = $this->post('mascota');
        }        
        if (count($imagenesv) > 0){
            $mascota['mas_imagen'] = $imagenesv[0];
        }else{
            $mascota['mas_imagen'] = '';   
        }        
        $id_mascota = $this->mascotas_model->save($mascota);
        if (! is_null ($id_mascota)){
            $response_200['message'] = 'Mascota cargada correctamente!.';
            $response_200['id_cargado'] = $id_mascota;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar la mascota.'), 400);
        }        
    }    
    
    public function update_post($id, $type = 'php'){        
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);            
            $carpeta = 'mascotas';
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
            $mascota = json_decode($this->post('mascota'),true);    
        }else{
            $mascota = $this->post('mascota');
        }
        if (isset($imagenesv)){
            $i = 1;
            foreach($imagenesv as $valor){
                $mascota['url'.$i] = $imagenesv[$i - 1];
                $i++;
            }
        }        
        $id_mascota = $this->mascotas_model->update($mascota);
        if (! is_null ($id_mascota)){
            $this->response(array('response' => $id_mascota), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar la mascota.'), 400);
        }
        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID de la Mascota.'), 400);
        }        
        $delete = $this->mascotas_model->delete($id);
        if (! is_null ($delete)){
            $this->response(array('response' => 'Mascota eliminada.'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar la mascota.'), 400);
        }    
    }
}

?>