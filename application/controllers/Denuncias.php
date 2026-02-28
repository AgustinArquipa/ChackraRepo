<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Denuncias extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('denuncias_model');
        $this->load->library('image_moo');
        $this->carpeta = 'denuncias';
    }
    
    public function index_get($limit = 0, $offset = 0){
        $cant_total = $this->denuncias_model->getCantTotal();
        $denuncias = $this->denuncias_model->get(NULL, $limit, $offset);        
        if (! is_null ($denuncias)){
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $denuncias)), 200);
        }else{
            $this->response(array('response' => 'No hay denuncias cargadas.'), 200);
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response('Faltan datos: ID de la Denuncia', 400);
        }        
        $denuncia = $this->denuncias_model->get($id);
        if (! is_null ($denuncia)){
            $this->response(array('response' => $denuncia), 200);
        }else{
            $this->response(array('response' => 'No se encuentra la denuncia buscada.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){        
        if (! $id_usuario){
            $this->response('Faltan datos: ID del Usuario de la Denuncia', 400);
        }
        $denuncias = $this->denuncias_model->get_usuario($id_usuario);
        if (! is_null ($denuncias)){
            $this->response(array('response' => $denuncias), 200);
        }else{
            $this->response(array('response' => 'No se encuentran denuncias asociadas al usuario seleccionado.'), 200);
        }
    }
    
    public function new_post($type = 'php'){        
        //log_message('error', print_r($_FILES,1));        
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);            
            $carpeta = 'denuncias';
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
                $nombre_unico = uniqid('d_').'.'.$extension;
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
            $denuncia = json_decode($this->post('denuncia'),true);    
        }else{
            $denuncia = $this->post('denuncia');
        }        
        if (count($imagenesv) > 0){
            $denuncia['den_imagen'] = $imagenesv[0];
        }else{
            $denuncia['den_imagen'] = '';   
        }        
        $id_denuncia = $this->denuncias_model->save($denuncia);                
        if (! is_null ($id_denuncia)){
            $response_200['message'] = 'Denuncia cargada correctamente!.';
            $response_200['id_cargado'] = $id_denuncia;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar la denuncia.'), 400);
        }        
    }
    
    public function update_post($id, $type = 'php'){        
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);            
            $carpeta = 'denuncias';
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
            $denuncia = json_decode($this->post('denuncia'),true);    
        }else{
            $denuncia = $this->post('denuncia');
        }
        if (isset($imagenesv)){
            $i = 1;
            foreach($imagenesv as $valor){
                $denuncia['url'.$i] = $imagenesv[$i - 1];
                $i++;
            }
        }        
        $id_denuncia = $this->denuncias_model->update($denuncia);
        if (! is_null ($id_denuncia)){
            $this->response(array('response' => $id_denuncia), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar la denuncia.'), 400);
        }        
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID de las Denuncia.'), 400);
        }        
        $delete = $this->denuncias_model->delete($id);
        if (! is_null ($delete)){
            $this->response(array('response' => 'Denuncia eliminada.'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar la denuncia.'), 400);
        }    
    }
}
?>