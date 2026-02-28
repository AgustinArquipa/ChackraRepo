<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Profesionales extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('profesionales_model');
        $this->load->library('image_moo');
        $this->carpeta = 'profesionales';
    }
    
    public function index_get($rubro = 0, $limit = 0, $offset = 0){
        $cant_total = $this->profesionales_model->getCantTotal();
        $profesionales = $this->profesionales_model->get(NULL, $rubro, $limit, $offset);        
        if (! is_null ($profesionales)){
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $profesionales)), 200);
        }else{
            if ($rubro != 0){
                $this->response(array('response' => 'No hay profesionales cargados en el rubro seleccionado.'), 200);    
            }else{
                $this->response(array('response' => 'No hay profesionales cargados.'), 200);
            }
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Profesional'), 400);
        }
        $profesional = $this->profesionales_model->get($id);
        if (! is_null ($profesional)){
            $this->response(array('response' => $profesional), 200);
        }else{
            $this->response(array('response' => 'No se encuentra el profesional buscado.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){
        if (! $id_usuario){
            $this->response(array('error' => 'Faltan datos: ID del Usuario del Profesional'), 400);
        }
        $profesionales = $this->profesionales_model->get_usuario($id_usuario);
        if (! is_null ($profesionales)){
            $this->response(array('response' => $profesionales), 200);
        }else{
            $this->response(array('response' => 'No se encuentran profesionales asociados al usuario seleccionado.'), 200);
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
                $nombre_unico = uniqid('p_').'.'.$extension;
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
                    $this->image_moo->load($file)->load_watermark('assets/img/logo_qps.png')->resize_crop(200,200)->watermark(5)->save($file_uploaded,true);
                    $response_200['fotos'] = 'Fotos y thumbs cargados con exito.';
                }  
            }  
        }else{
            $this->response(array('error' => 'Oops! No envió fotos para cargar.'), 400);   
        } 
        if ($type == 'json'){
            $profesional = json_decode($this->post('profesional'),true);    
        }else{
            $profesional = $this->post('profesional');
        }
        if (count($imagenesv) > 0){
            $profesional['pro_imagen'] = $imagenesv[0];
        }else{
            $profesional['pro_imagen'] = '';    
        }
        $id_profesional = $this->profesionales_model->save($profesional);
        if (! is_null ($id_profesional)){
            $response_200['message'] = 'Profesional cargado correctamente!.';
            $response_200['id_cargado'] = $id_profesional;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar el profesional.'), 400);
        }
    }
    
    public function update_post($id, $type = 'php'){        
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);            
            $carpeta = 'profesionales';
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
            $profesional = json_decode($this->post('profesional'),true);    
        }else{
            $profesional = $this->post('profesional');
        }
        if (isset($imagenesv)){
            $i = 1;
            foreach($imagenesv as $valor){
                $profesional['url'.$i] = $imagenesv[$i - 1];
                $i++;
            }
        }        
        $id_profesional = $this->profesionales_model->update($profesional);
        if (! is_null ($id_profesional)){
            $this->response(array('response' => $id_profesional), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
        }        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Profesional.'), 400);
        }        
        $delete = $this->profesionales_model->delete($id);
        if (! is_null ($delete)){
            $this->response(array('response' => 'Profesional eliminado.'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el profesional.'), 400);
        }    
    }
}

?>