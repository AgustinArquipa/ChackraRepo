<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Avisos extends REST_Controller {

    private $crud = null;
    
    public function __construct(){
        parent::__construct();
        $this->load->library('Grocery_CRUD');
        $this->load->model('avisos_model');
        $this->load->model('imagenes_model');
        $this->load->library('image_moo');
        $this->carpeta = 'avisos';
    }
    
    public function index_get($categoria = 0, $subcategoria = 0, $limit = 0, $offset = 0){
        $cant_total = $this->avisos_model->getCantTotal();
        $avisos = $this->avisos_model->get(NULL, $categoria, $subcategoria, $limit, $offset); 
        //log_message('error', 'Avisos: '.$avisos);
        $i = 0;
        if (! is_null ($avisos)){
            foreach ($avisos as $row){
                $imagenes = $this->imagenes_model->get_id_tabla_foranea($this->carpeta, $row['id_avisos']);
                $avisos[$i]['imagenes'] = $imagenes;
                $i++;
            }    
        }        
        if (! is_null ($avisos)){
            $this->response(array('response' => array('cantidad' => $cant_total, 'listado' => $avisos)), 200);
        }else{
            if ($categoria != 0){
                $this->response(array('response' => 'No hay avisos cargados en la categoria seleccionada.'), 200);    
            }else if ($subcategoria != 0){
                $this->response(array('response' => 'No hay avisos cargados en la subcategoria seleccionada.'), 200);       
            }else{
                $this->response(array('response' => 'No hay avisos cargados.'), 200);
            }
        }
    }
    
    public function find_get($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Aviso.'), 400);
        }
        $aviso = $this->avisos_model->get($id);
        if (! is_null ($aviso)){
            $imagenes = $this->imagenes_model->get_id_tabla_foranea($this->carpeta, $aviso['id_avisos']);
            $aviso['imagenes'] = $imagenes;
            $this->response(array('response' => $aviso), 200);
        }else{
            $this->response(array('response' => 'No se encuentra el aviso buscado.'), 200);
        }
    }
    
    public function find_usuario_get($id_usuario){
        if (! $id_usuario){
            $this->response(array('error' => 'Faltan datos: ID del Usuario del Aviso.'), 400);
        }
        
        $avisos = $this->avisos_model->get_usuario($id_usuario);
        
        $i = 0;
        if (! is_null ($avisos)){
            foreach ($avisos as $row){
                $imagenes = $this->imagenes_model->get_id_tabla_foranea($this->carpeta, $row['id_avisos']);
                $avisos[$i]['imagenes'] = $imagenes;
                $i++;
            }    
        } 
        
        if (! is_null ($avisos)){
            $this->response(array('response' => $avisos), 200);
        }else{
            $this->response(array('response' => 'No se encuentran avisos asociados al usuario seleccionado.'), 200);
        }
    }
    
    public function new_post($type = 'php'){        
        //log_message('error', print_r($_FILES,1)); 
        if (isset($_FILES)){
            $files = $_FILES;
            $cpt = count($_FILES);
            $carpeta = 'avisos';
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
                $nombre_unico = uniqid('a_').'.'.$extension;
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
            $aviso = json_decode($this->post('aviso'),true);    
        }else{
            $aviso = $this->post('aviso');
        }
        $id_aviso = $this->avisos_model->save($aviso);
        if (isset($imagenesv)){
            foreach($imagenesv as $valor){
                $datos_imagen = array (
                    'tabla_foranea_nombre' => 'avisos',
                    'id_tabla_foranea'  => $id_aviso,
                    'ima_nombre'    => $valor,
                    'fecha_envio_movil' => $this->fechaMySQL($aviso['fecha_envio'])
                );
                $id_imagen = $this->imagenes_model->save($datos_imagen);
            }
        }        
        if (! is_null ($id_aviso)){
            $response_200['message'] = 'Aviso cargado correctamente!.';
            $response_200['id_cargado'] = $id_aviso;
            $this->response(array('response' => $response_200), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar el aviso.'), 400);
        }        
    }  
    
    public function update_post($id, $type = 'php'){
        if (isset($_FILES)){
            $files = $_FILES;        
            $cpt = count($_FILES);
            $carpeta = 'avisos';
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
            $aviso = json_decode($this->post('aviso'),true);    
        }else{
            $aviso = $this->post('aviso');
        }
        $id_aviso = $this->avisos_model->update($aviso);
        if (isset($imagenesv)){
            foreach($imagenesv as $valor){
                $datos_imagen = array (
                    'tabla_foranea_nombre' => 'avisos',
                    'id_tabla_foranea'  => $id_aviso,
                    'ima_nombre'    => $valor,
                    'fecha_envio_movil' => $aviso['fecha_envio']
                );
                $id_imagen = $this->imagenes_model->save($datos_imagen);
            }
        }
        if (! is_null ($id_aviso)){
            $this->response(array('response' => $id_aviso), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
        }
    }
    
    public function index_delete($id){
        if (! $id){
            $this->response(array('error' => 'Faltan datos: ID del Aviso.'), 400);
        }
        $delete = $this->avisos_model->delete($id);
        if (! is_null ($delete)){
            $this->response(array('response' => 'Aviso eliminado.'), 200);
        }else{
            $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el aviso.'), 400);
        }    
    }
}

?>