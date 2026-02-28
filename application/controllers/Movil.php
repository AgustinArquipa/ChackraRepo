<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Movil extends CI_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model("comercios_model");
    }
        
    //URL: http://xxxxxxxx/guiameweb/movil/listadocomercios/[localidades]/[rubros]/[categorias]/[cantidad]/[desde]
    public function ListadoComercios($localidad = 0, $rubro = 0, $categoria = 0, $limit = '', $offset = ''){
        if ($rubro != 0){
            $comerciosv = $this->comercios_model->getxRubro($rubro, $localidad, $limit, $offset);     
        }else if ($categoria != 0){
            $comerciosv = $this->comercios_model->getxCategoria($categoria, $localidad, $limit, $offset);  
        }else{
            $comerciosv = $this->comercios_model->get(NULL, $limit, $offset, $localidad); 
        }
        
        if($comerciosv !== false){
            if ($rubro != 0){
                if ($localidad != 0){
                    $mensaje = 'Listado Completo de Comercios por Localidad: '.$localidad.' y Rubro: '. $rubro;    
                }else{
                    $mensaje = 'Listado Completo de Comercios por Rubro: '. $rubro;    
                }                
            }else if ($categoria != 0){
                if ($localidad != 0){
                    $mensaje = 'Listado Completo de Comercios por Localidad: '.$localidad.' y Categoria: '. $categoria;    
                }else{
                    $mensaje = 'Listado Completo de Comercios por Categoria: '. $categoria;   
                }                 
            }else{
                
                if ($localidad != 0){
                    $mensaje = 'Listado Completo de Comercios por Localidad: '.$localidad;    
                }else{
                    $mensaje = 'Listado Completo de Comercios';
                } 
            }
            
            $response["success"] = 1;
            $response["message"] = $mensaje;
            $response['datos']['comercios'] = $comerciosv;   
        }else{
            if ($tipo != ''){
                $mensaje = 'No se encontraron comercios (tipo '.$tipo.') cargados';
            }else{
                $mensaje = 'No se encontraron Comercios cargados';   
            }
            $response["success"] = 0;
            $response["message"] = $mensaje;
            $response['datos'] = false;             
        }
        echo json_encode($response, true);
    }
    
    
    public function CargarFotos(){
        $ruta = "assets/uploads/inspecciones/" . basename( $_FILES['fotoUp']['name']);

        $file = fopen("archivo.txt", "a");



        if(move_uploaded_file($_FILES['fotoUp']['tmp_name'], $ruta))
        fwrite($file, "SI - ".$ruta . PHP_EOL);
        else
        fwrite($file, "NO - ".$ruta . PHP_EOL);

        fclose($file);
    }
    
    //Fotos de Inspecciones: http://safetynova.com/safetynova/safetynova-app/movil/subirfotos/inspecciones/10
    //Firmas: http://safetynova.com/safetynova/safetynova-app/movil/subirfotos/firmas/10.1
    public function SubirFotos($carpeta, $id){
        $response = array();
        
        if (isset($_FILES['fotoUp'])){
            $file = fopen("archivo.txt", "a");
        
            $config['upload_path'] = 'assets/uploads/'.$carpeta;
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = '2000';
            $config['max_width'] = '2024';
            $config['max_height'] = '2008';
            $imagen = $_FILES["fotoUp"]['name'];
            $extension = pathinfo($imagen, PATHINFO_EXTENSION);
            switch($carpeta){
                case 'notas':
                    $nombre_unico = "1.".$extension;    
                break;
                case 'firmas':
                    $nombre_unico = $id.'.'.$extension;
                break;
                default:
                    $nombre_unico = uniqid('m_').'.'.$extension;          
            }
                        
            $config['file_name'] = $nombre_unico;

            $ruta = "assets/uploads/".$carpeta."/" . $nombre_unico;
            
            switch ($carpeta){
                case 'inspecciones':
                case 'notas':
                    $data = array(
                        'id_inspecciones' => $id,
                        'url' => $nombre_unico,
                        'priority' => 0
                    ); 
                break;
                case 'firmas':
                    $idv = explode('.', $id);
                    $data = array(
                        'id_inspecciones' => $idv[0],
                        'orden' => $idv[1],
                        'url' => $nombre_unico,
                        'priority' => 0
                    );    
                break;
                    
            }
            

            if (!move_uploaded_file($_FILES['fotoUp']['tmp_name'], $ruta)) {
                fwrite($file, "NO - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                $error = array('error' => $this->upload->display_errors());

                $response["success"] = 0;
                //$response["cod"] = 3;
                $response["message"] = 'Oops! Ocurrio un error al cargar fotos a la inspeccion.';

            } else {
                fwrite($file, "SI - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                //$this->_create_thumbnail($file_info['file_name']);
                //$data = array('upload_data' => $this->upload->data());
                $this->imagenes_model->cargar($data, $carpeta); 

                $response["success"] = 1;
                //$response["cod"] = 1;
                $response["message"] = 'Fotos cargada correctamente! ';
            }    
        }else{
            $response["success"] = 0;
            //$response["cod"] = 2;
            $response["message"] = 'Oops! No envió fotos para cargar.';    
        }
        
        
        echo json_encode($response);
        
    }
    
    public function cargarInspecciones(){        
        $response = array();
        
        //$file = fopen("archivo.txt", "a");
        
        //fwrite($file, print_r($this->input->post("respuesta_movil"),true));
        
        if ($this->input->post("respuesta_movil")){
            $json = $this->input->post("respuesta_movil"); 
            $obj_php = json_decode($json);

            $datosv['id_users'] = $obj_php->respuesta_movil[0]->inspeccion[0]->valor;
            $datosv['id_empresas'] = $obj_php->respuesta_movil[0]->inspeccion[1]->valor;
            $datosv['id_formularios'] = $obj_php->respuesta_movil[0]->inspeccion[2]->valor;
            $datosv['titulo'] = $obj_php->respuesta_movil[0]->inspeccion[3]->valor;
            $datosv['responsable'] = $obj_php->respuesta_movil[0]->inspeccion[4]->valor;
            $datosv['posx'] = $obj_php->respuesta_movil[0]->inspeccion[5]->valor;
            $datosv['posy'] = $obj_php->respuesta_movil[0]->inspeccion[6]->valor;
            $datosv['ins_fecha_creacion'] = date("Y-m-d h:i:s");

            $id_inspeccion = $this->inspecciones_model->cargarInspeccionMovil($datosv);

            if ($id_inspeccion !== false) {

                $datos_formulariov = $obj_php->respuesta_movil[1]->datos_formulario;

                $this->inspecciones_model->cargarDatosInspeccion($id_inspeccion, $datos_formulariov, 'movil');
                
                $respuestasv = $obj_php->respuesta_movil[2]->respuestas;

                $this->inspecciones_model->cargarRespuestasInspeccion($id_inspeccion, $respuestasv, 'movil');
                
                $response["success"] = 1;
                $response["message"] = $id_inspeccion;
                
            } else {
                $response["success"] = 0;
                $response["message"] = "Oops! Ocurrio un error al crear la inspeccion.";                
            }
            
            

            /*$rta = 'Id Empresa: '.$id_empresa;
            $rta .= '<br>';
            $rta .= 'Id Formulario: '.$id_formulario;
            $rta .= '<br>';    
            $rta .= 'Titulo: '.$titulo;
            $rta .= '<br><br>';

            $datos_formulariov = $obj_php->respuesta_movil[1]->datos_formulario;

            foreach ($datos_formulariov as $clave => $valor){
                $campo = $valor->id . ": " . $valor->valor."<br>";
                $rta .= $campo;
            }

            $respuestasv = $obj_php->respuesta_movil[2]->respuestas;

            foreach ($respuestasv as $clave => $valor){
                $campo = $valor->id . ": " . $valor->valor."<br>";
                $rta .= $campo;
            }

            $file = 'json_rta.php';
            file_put_contents($file, $rta);*/

        }else{
            $response["success"] = 0;
            $response["message"] = "Oops! Faltan datos para continuar.";    
        }
        
        echo json_encode($response);

        /*if($this->input->post("inspeccion")){
            parse_str($this->input->post("inspeccion"), $inspeccionv);

            $id_inspeccion = $this->inspecciones_model->cargarInspeccionMovil($inspeccionv);

            $datos_inspeccion = $this->input->post('datos_formulario');

            $this->inspecciones_model->cargarDatosInspeccion($id_inspeccion, $datos_inspeccion);

            $respuestas_inspeccion = $this->input->post('respuestas');

            $this->inspecciones_model->cargarRespuestasInspeccion($id_inspeccion, $respuestas_inspeccion);

            $data = array(
                "id_inspeccion"   => $id_inspeccion
            );

            echo json_encode($data);
        }else{
            echo false;
        }*/
    }
    
    
    public function cargarNotaRapida(){        
        $response = array();
        
        //$file = fopen("archivo.txt", "a");
        
        //fwrite($file, print_r($this->input->post("respuesta_movil"),true));
        
        if ($this->input->post("respuesta_movil")){
            $json = $this->input->post("respuesta_movil"); 
            
            $obj_php = json_decode($json);
            
            $datosv['texto'] = $obj_php->nota_rapida->valor;
            
            $id_nota_rapida = $this->inspecciones_model->cargarNotaRapida($datosv);

            if ($id_nota_rapida !== false) {

        
                $response["success"] = 1;
                $response["message"] = $id_nota_rapida;
                
            } else {
                $response["success"] = 0;
                $response["message"] = "Oops! Ocurrio un error al crear la inspeccion.";                
            }
            
            

            /*$rta = 'Id Empresa: '.$id_empresa;
            $rta .= '<br>';
            $rta .= 'Id Formulario: '.$id_formulario;
            $rta .= '<br>';    
            $rta .= 'Titulo: '.$titulo;
            $rta .= '<br><br>';

            $datos_formulariov = $obj_php->respuesta_movil[1]->datos_formulario;

            foreach ($datos_formulariov as $clave => $valor){
                $campo = $valor->id . ": " . $valor->valor."<br>";
                $rta .= $campo;
            }

            $respuestasv = $obj_php->respuesta_movil[2]->respuestas;

            foreach ($respuestasv as $clave => $valor){
                $campo = $valor->id . ": " . $valor->valor."<br>";
                $rta .= $campo;
            }

            $file = 'json_rta.php';
            file_put_contents($file, $rta);*/

        }else{
            $response["success"] = 0;
            $response["message"] = "Oops! Faltan datos para continuar.";    
        }
        
        echo json_encode($response);

        /*if($this->input->post("inspeccion")){
            parse_str($this->input->post("inspeccion"), $inspeccionv);

            $id_inspeccion = $this->inspecciones_model->cargarInspeccionMovil($inspeccionv);

            $datos_inspeccion = $this->input->post('datos_formulario');

            $this->inspecciones_model->cargarDatosInspeccion($id_inspeccion, $datos_inspeccion);

            $respuestas_inspeccion = $this->input->post('respuestas');

            $this->inspecciones_model->cargarRespuestasInspeccion($id_inspeccion, $respuestas_inspeccion);

            $data = array(
                "id_inspeccion"   => $id_inspeccion
            );

            echo json_encode($data);
        }else{
            echo false;
        }*/
    }
    
}