<?php  
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require_once APPPATH . '/libraries/REST_Controller.php';

    class Turnero extends REST_Controller {
        public function __construct(){
            parent::__construct();
            
            if (isset($_SERVER['HTTP_ORIGIN'])){
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400');    // cache for 1 day
            }
            // Access-Control headers are received during OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') 
            {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                    header("Access-Control-Allow-Methods: GET, POST,OPTIONS");         
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                    header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                exit(0);
            }
            
            $this->load->model('tur_turnos_model');
            $this->load->model('tur_horarios_model');
            $this->load->model('tur_categorias_model');
            $this->load->model('tur_subcategorias_model');
            $this->load->model('tur_prestadores_model');
            $this->load->model('tur_servicios_model');
            $this->load->model('tur_instituciones_model');
			$this->load->model('usuarios_model');

            $this->load->library('image_moo');

            $this->dias = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
        }

        public function prueba_get(){
            switch($_SERVER['REQUEST_METHOD']){
                case 'GET':
                    $result = 'Hola';
                    echo json_encode($result);
                break;
                case 'POST':
                    /*$datas = json_decode(file_get_contents("php://input"));
                    $result = $this->user->save_user($datas);
                    echo json_encode($result);*/
                break;
            }
        }
        
        //TURNOS
            //GET
                public function turnos_get($id_usuario = NULL, $id_prestador = NULL, $estado_prestador = NULL, $estado_cliente = NULL, $fecha = NULL, $tipo = NULL){
                    $turnos = $this->tur_turnos_model->get(NULL, $id_prestador, $fecha, NULL, $id_usuario, $estado_prestador, $estado_cliente, $tipo);
                    
                    if (! is_null ($turnos)){
                        foreach ($turnos as $index => $turno){                        
                            $turno['tur_inicio'] = $this->convertirHora($turno['tur_inicio']);
                            $turno['tur_fin'] = $this->convertirHora($turno['tur_fin']);
                            $turno['tur_fecha'] = date("d-m-Y", strtotime($turno['tur_fecha']));
                            $turnos[$index] = $turno;
                            //log_message('ERROR', print_r($turno,1));
                        }
                    } 
                        
                    
                    //print_r($turnos,1);
                        
                    if (! is_null ($turnos)){
                        $this->response(array('response' => $turnos), 200);
                    }else{
                        $this->response(array('response' => array()), 200);
                    }
                }   
                public function generarGrilla_get($id_prestador, $fecha){
                    $fechav1 = explode('-',$fecha);
                    $diaSemana = $this->diaSemana($fechav1[0], $fechav1[1], $fechav1[2]);
                    $horarios = $this->tur_horarios_model->get(NULL, $id_prestador, $this->dias[$diaSemana]);
                    $turnos = $this->tur_turnos_model->get(NULL, $id_prestador, $fecha);
                    $franjas = array('manana' => array(), 'tarde' => array());
                    //print_r($horarios);
                    foreach ($horarios as $horario){
                        $inicio_horario = $horario['hor_inicio']; 
                        $final_horario = $horario['hor_fin'];
                        $inicio_disponible = $inicio_horario;
                        $fin_disponible = '';
                        $final_turnos = '';
                        if (!is_null($turnos)){
                            foreach ($turnos as $turno){
                                log_message('error', print_r($turnos,1));
                                if (($turno['tur_inicio'] >= $inicio_horario) && ($turno['tur_fin'] <= $final_horario)){
                                    if ($turno['tur_inicio'] != $inicio_disponible) {
                                        $fin_disponible =  $turno['tur_inicio'];
                                        if ($inicio_disponible >= '14:00'){
                                            $franjas['tarde'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                        }else{
                                            $franjas['manana'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                        }

                                        $inicio_disponible = $turno['tur_fin'];
                                        $final_turnos = $turno['tur_fin'];
                                    }else{
                                        $inicio_disponible = $turno['tur_fin'];
                                        $final_turnos = $turno['tur_fin'];
                                    }               
                                }
                            }
                            if ($final_turnos != ''){
                                if($final_turnos != $final_horario){
                                    $inicio_disponible = $final_turnos;
                                    $fin_disponible = $final_horario;
                                    if ($inicio_disponible >= '14:00'){
                                        $franjas['tarde'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                    }else{
                                        $franjas['manana'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                    }
                                }    
                            }else{
                                $inicio_disponible = $inicio_horario;
                                $fin_disponible = $final_horario;
                                if ($inicio_disponible >= '14:00'){
                                    $franjas['tarde'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                }else{
                                    $franjas['manana'][] = array ('inicio' => $this->convertirHora($inicio_disponible), 'fin' => $this->convertirHora($fin_disponible));    
                                }      
                            }  
                        }else{
                            if ($inicio_disponible >= '14:00'){
                                $franjas['tarde'][] = array ('inicio' => $this->convertirHora($inicio_horario), 'fin' => $this->convertirHora($final_horario));     
                            }else{
                                $franjas['manana'][] = array ('inicio' => $this->convertirHora($inicio_horario), 'fin' => $this->convertirHora($final_horario));     
                            }    
                        }
                    }
                    $this->response(array('response' => $franjas), 200);
                }
            //POST
                public function reservarTurno_post($type = 'php'){
                    if (! $this->post('turno')){
                        $this->response(NULL, 400);
                    } 

                    if ($type == 'json'){
                        $turno = json_decode($this->post('turno'),true);    
                    }else{
                        $turno = $this->post('turno');
                    }

                    $datetime1 = new DateTime($turno['tur_fin']);
                    $datetime2 = new DateTime($turno['tur_inicio']);
                    $interval = $datetime1->diff($datetime2);
                    $turno['duracion_min'] = $interval->format('%i');
                    $turno['tur_estado_prestador'] = 'Pendiente';
                    $turno['tur_estado_cliente'] = 'Aprobado';

                    $existe_turno = $this->tur_turnos_model->get(NULL, $turno['id_tur_prestadores'], $turno['tur_fecha'], $turno['tur_inicio']);

                    if (is_null($existe_turno)){
                        $id_turno = $this->tur_turnos_model->save($turno);
                        $user = $this->tur_prestadores_model->get($turno['id_tur_prestadores']);
                        $id_receptor = $user['id_notificacion'];
                        
                        $id_firebase = $this->enviarNotificacion($id_receptor, $turno, 'reserva_turno', 'prestador', $id_turno, $user['id_usuarios']);

                        if (! is_null ($id_turno)){
                            $response_200['id_firebase'] = $id_firebase;
                            $response_200['message'] = 'Turno reservado correctamente!.';
                            $response_200['id_cargado'] = $id_turno;
                            $this->response(array('response' => $response_200), 200);
                        }else{
                            $this->response(array('error' => 'Ha ocurrido un error al intentar reservar el turno.'), 400);
                        }
                    }else{
                        $response_200['message'] = 'Turno No Disponible. Reservado con anterioridad.';
                        $response_200['id_cargado'] = '-';
                        $this->response(array('response' => $response_200), 200);
                    }
                }
                public function cancelarTurno_post($id_turno, $type = 'php'){
                    if (! $this->post('turno')){
                        $this->response(NULL, 400);
                    } 

                    if ($type == 'json'){
                        $turno = json_decode($this->post('turno'),true);    
                    }else{
                        $turno = $this->post('turno');
                    }

                    $existe_turno = $this->tur_turnos_model->get($id_turno);

                    //log_message('error', print_r($existe_turno,1));

                    if (!is_null($existe_turno)){
                        $rta_update = $this->tur_turnos_model->update($id_turno, $turno);

                        if (! is_null ($rta_update)){
                            $response_200['message'] = 'Turno cancelado correctamente!.';
                            $response_200['id'] = $id_turno;
                            $this->response(array('response' => $response_200), 200);
                        }else{
                            $this->response(array('error' => 'Ha ocurrido un error al intentar cancelar el turno.'), 400);
                        }
                    }else{
                        $response_200['message'] = 'El turno no existe en el sistema.';
                        $response_200['id'] = '-';
                        $this->response(array('response' => $response_200), 200);
                    }
                }
            //PUT
                public function turno_put($id, $type = 'php'){                     
                    if (is_null ($id)){
                        $this->response(array('error' => 'Falta el ID del Turno a Actualizar.'), 400);
                    }                    
                    if ($type == 'json'){
                        $turno = json_decode($this->put('turno'),true);    
                    }else{
                        $turno = $this->put('turno');
                    }
					
					$turno_actual = $this->tur_turnos_model->get($id); 
                    
					$enviar = true;
					
                    if (isset($turno['tur_estado_prestador'])){ //EMISOR: PRESTADOR - RECEPTOR: CLIENTE
						if ($turno['tur_estado_prestador'] == 'Eliminado' && $turno_actual['tur_estado_prestador'] == 'Cancelado'){
							$enviar = false;	
						}else if ($turno['tur_estado_prestador'] == 'Eliminado' && ($turno_actual['tur_estado_cliente'] == 'Cancelado' || $turno_actual['tur_estado_cliente'] == 'Eliminado')){
							$enviar = false;	
						}
                        $tipo_entidad = 'cliente'; 
                        $user = $this->usuarios_model->get($turno['id_usuarios']);
                        $id_receptor = $user['id_notificacion'];
                    }else{ //EMISOR: CLIENTE - RECEPTOR: PRESTADOR
						if ($turno['tur_estado_cliente'] == 'Eliminado' && $turno_actual['tur_estado_cliente'] == 'Cancelado'){
							$enviar = false;	
						}else if ($turno['tur_estado_cliente'] == 'Eliminado' && ($turno_actual['tur_estado_prestador'] == 'Cancelado' || $turno_actual['tur_estado_prestador'] == 'Eliminado')){
							$enviar = false;	
						}
                        $tipo_entidad = 'prestador';
                        $user = $this->tur_prestadores_model->get($turno['id_tur_prestadores']);
                        $id_receptor = $user['id_notificacion'];                        
                    }
                    
                    if ($this->tur_turnos_model->update($id, $turno)){
						if ($enviar === true){
							$id_firebase = $this->enviarNotificacion($id_receptor, $turno, 'modificacion_turno', $tipo_entidad, $id, $user['id_usuarios']);	
						}
                        $this->response(array('response' => true), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
                    }        
                }
            
        //CATEGORIAS
            //GET
                public function listadoCategorias_get(){
                    $categorias = $this->tur_categorias_model->get();

                    if (! is_null ($categorias)){
                        $this->response(array('response' => $categorias), 200);
                    }else{
                        $this->response(array('response' => array()), 200);
                    }
                }
        
        //SUBCATEGORIAS
            //GET
                public function listadoSubCategorias_get($id_categoria = NULL){
                    $subcategorias = $this->tur_subcategorias_model->get(NULL, $id_categoria);

                    if (! is_null ($subcategorias)){
                        $this->response(array('response' => $subcategorias), 200);
                    }else{
                        $this->response(array('response' => array()), 200);
                    }
                }

        //PRESTADORES  
            //GET
                public function detallePrestador_get($id_prestador = NULL, $tipo = NULL){
                    $prestador = $this->tur_prestadores_model->get($id_prestador, NULL, NULL, NULL, NULL, NULL, $tipo);

                    if (! is_null ($prestador)){
                        $prestador;
                        $horarios = $this->tur_horarios_model->get(NULL, $id_prestador, NULL, 'simple');  
                        $servicios = $this->tur_servicios_model->get(NULL, $id_prestador, 'simple');  

                        $prestador['horarios'] = $horarios;
                        $prestador['servicios'] = $servicios;
                    }

                    if (! is_null ($prestador)){
                        $this->response(array('response' => $prestador), 200);
                    }else{
                        $objeto_vacio = new stdClass();
                        $this->response(array('response' => $objeto_vacio), 200);
                    } 
                }
                public function prestadores_get($id_categorias = NULL, $id_subcategorias = NULL, $id_usuario = NULL, $buscar = NULL, $id_institucion = NULL, $tipo = NULL){ //$tipo = simple, admin
                    $prestadores = $this->tur_prestadores_model->get(NULL, $id_categorias, $id_subcategorias, $id_usuario, $buscar, $id_institucion, $tipo);

                    if (! is_null ($prestadores)){
						if ($id_institucion != NULL && $id_institucion != '-'){
							$subcategorias = $this->tur_prestadores_model->get_subcategorias($id_institucion);	
							$this->response(
								array('response' => 
									  array('prestadores' => $prestadores, 'subcategorias' => $subcategorias),
									 ), 200);
						}else{
							$this->response(array('response' => $prestadores), 200); 	
						}
                    }else{     
						if ($id_institucion != NULL && $id_institucion != '-'){
							$obj = new stdClass();
							$this->response(array('response' => $obj), 200);	
						}else{
							$this->response(array('response' => array()), 200);	
						}
                    }
                }
        
                public function listadoPrestadoresxCategorias_get($id_categorias = NULL){
                    $prestadores = $this->tur_prestadores_model->get(NULL, $id_categorias);

                    if (! is_null ($prestadores)){
                        $this->response(array('response' => $prestadores), 200);
                    }else{
                        $this->response(array('response' => array()), 200); 
                    }
                }    
                public function listadoPrestadoresFiltrado_get($id_subcategorias = NULL){
                    $prestadores = $this->tur_prestadores_model->get(NULL, NULL, $id_subcategorias);

                    if (! is_null ($prestadores)){
                        $this->response(array('response' => $prestadores), 200);
                    }else{
                        $this->response(array('response' => array()), 200); 
                    }
                }
            //POST
                public function prestador_post($type = 'php'){  
                    if ($this->post('prestador') !== NULL){
                        //log_message('error', print_r($_FILES,1));
                        if (isset($_FILES)){
                            $files = $_FILES;
                            $cpt = count($_FILES);
                            $carpeta = 'prestadores';
                            $config['upload_path'] = 'assets/uploads/turnero/'.$carpeta;
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
                                $ruta_base = "assets/uploads/turnero/".$carpeta."/";
                                $ruta = $ruta_base . $nombre_unico;
                                $imagenesv[$i] = $nombre_unico;
                                if (!move_uploaded_file($nombre_temporal, $ruta)) {
                                    //fwrite($file, "NO - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                                    //$error = array('error' => $this->upload->display_errors());                    
                                    $this->response(array('error' => 'Oops! Ocurrio un error al cargar fotos.'), 400);
                                }else{
                                    /* CREO EL THUMB CORRESPONDIENTE */
                                    $file = $ruta_base.$imagenesv[$i]; 
                                    $file_uploaded = $ruta_base.'thumb-600-400-'.$nombre_unico;
                                    $this->image_moo->load($file)->resize_crop(600,400)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);                    
                                    $file_uploaded = $ruta_base.'thumb-250-'.$nombre_unico;
                                    $this->image_moo->load($file)->resize_crop(250,250)->save($file_uploaded,true);
                                    $response_200['fotos'] = 'Fotos y thumbs cargados con exito.';
                                }  
                            }
                        }else{
                            $this->response(array('error' => 'Oops! No envió fotos para cargar.'), 400);   
                        }  

                        if ($type == 'json'){
                            $prestador = json_decode($this->post('prestador'),true);    
                        }else{
                            $prestador = $this->post('prestador');
                        }

                        if (isset($imagenesv)){
                            $prestador['pre_foto'] = $imagenesv[0];
                            $prestador['pre_foto_portada'] = $imagenesv[1];
                        }    
                        
                        //log_message('error', print_r($prestador,1)); 

                        $id_prestador = $this->tur_prestadores_model->save($prestador);        

                        if (! is_null ($id_prestador)){
                            $response_200['message'] = 'Prestador cargado correctamente!.';
                            $response_200['id_cargado'] = $id_prestador;
                            $this->response(array('response' => $response_200), 200);
                        }else{
                            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar el prestador.'), 400);
                        }      
                    }else{
                        $this->response(array('error' => 'Faltan datos para la carga del prestador.'), 400);      
                    }                      
                }
                public function prestador_actualizar_post($id_prestador, $type = 'php'){
                    if ($type == 'json'){
                        $prestador = json_decode($this->post('prestador'),true);    
                    }else{
                        $prestador = $this->post('prestador');
                    }

                    if (isset($_FILES)){
                        $files = $_FILES;
                        $cpt = 2; //count($_FILES);
                        $carpeta = 'prestadores';
                        $config['upload_path'] = 'assets/uploads/turnero/'.$carpeta;
                        $config['allowed_types'] = 'gif|jpg|png';
                        $config['max_size'] = '2000';
                        $config['max_width'] = '2024';
                        $config['max_height'] = '2008';
                        $imagenesv = array();
                        for($i=0; $i<$cpt; $i++){
                            if(isset($files[$i])){
                                $nombre = $files[$i]['name'];
                                $nombre_temporal = $files[$i]['tmp_name'];
                                $imagen = $nombre;
                                $extension = pathinfo($imagen, PATHINFO_EXTENSION);
                                $nombre_unico = uniqid('a_').'.'.$extension;
                                $config['file_name'] = $nombre_unico;
                                $ruta_base = "assets/uploads/turnero/".$carpeta."/";
                                $ruta = $ruta_base . $nombre_unico;
                                switch ($i){
                                    case 0:
                                        $tipo_foto = "pre_foto";
                                    break;
                                    case 1:
                                        $tipo_foto = "pre_foto_portada";
                                    break;     
                                }                            
                                $prestador[$tipo_foto] = $nombre_unico;
                                if (!move_uploaded_file($nombre_temporal, $ruta)) {
                                    //fwrite($file, "NO - ".$imagen.'-'.$nombre_unico . PHP_EOL);
                                    //$error = array('error' => $this->upload->display_errors());                    
                                    $this->response(array('error' => 'Oops! Ocurrio un error al cargar fotos.'), 400);
                                }else{
                                    /* CREO EL THUMB CORRESPONDIENTE */
                                    $file = $ruta_base.$nombre_unico; 
                                    $file_uploaded = $ruta_base.'thumb-600-400-'.$nombre_unico;
                                    $this->image_moo->load($file)->resize_crop(600,400)->filter(IMG_FILTER_GAUSSIAN_BLUR)->save($file_uploaded,true);                    
                                    $file_uploaded = $ruta_base.'thumb-250-'.$nombre_unico;
                                    $this->image_moo->load($file)->resize_crop(250,250)->save($file_uploaded,true);
                                    $response_200['fotos'] = 'Fotos y thumbs cargados con exito.';
                                }    
                            }
                        }
                    }
                    
                    $this->tur_prestadores_model->update($id_prestador, $prestador);

                    if (! is_null ($id_prestador)){
                        $this->response(array('response' => $id_prestador), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
                    }        
                }
                public function prestador_put($id_prestador, $type = 'php'){
                    if (is_null ($id_prestador)){
                        $this->response(array('error' => 'Falta el ID del Prestador a Editar.'), 400);
                    } 
                    
                    if ($type == 'json'){
                        $prestador = json_decode($this->put('prestador'),true);    
                    }else{
                        $prestador = $this->put('prestador');
                    }

                    $this->tur_prestadores_model->update($id_prestador, $prestador);

                    if (! is_null ($id_prestador)){
                        $this->response(array('response' => $id_prestador), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
                    }        
                }

        //HORARIOS
            //GET
                public function horarios_get($id_prestador = NULL, $tipo = ''){
                    $horarios = $this->tur_horarios_model->get(NULL, $id_prestador, NULL, 'simple');
                    
                    $horarios_rta = array();
                    
                    //LIMPIO LA RESPUESTA PARA QUE CRISTIAN PUEDA TOMARLO DE LA MISMA MANERA QUE LO ENVIA...
                    /*if ($tipo == 'agrupado'){
                        foreach($horarios as $row){
                            unset ($row['id_tur_horarios']);
                            unset ($row['id_tur_prestadores']);
                            unset ($row['hor_fecha_alta']); 
                            $dia = $row['hor_dia'];
                            unset ($row['hor_dia']);
                            $horarios_rta[$dia][] = $row;                            
                        }                             
                    }else{
                        foreach($horarios as $row){
                            unset ($row['id_tur_horarios']);
                            unset ($row['id_tur_prestadores']);
                            unset ($row['hor_fecha_alta']);
                            $horarios_rta[] =$row;
                        }    
                    }*/
                    
                    //LIMPIO LA RESPUESTA PARA QUE CRISTIAN PUEDA TOMARLO DE LA MISMA MANERA QUE LO ENVIA...
                    foreach($horarios as $index => $value){
                        //unset ($row['id_tur_horarios']);
                        unset ($horarios[$index]['id_tur_prestadores']);
                        unset ($horarios[$index]['hor_fecha_alta']);
                        if ($index > 0){
                            if ($dia_base == $horarios[$index]['hor_dia']){
                                $horarios[$index]['hor_dia'] = '';    
                            }else{
                                $dia_base = $horarios[$index]['hor_dia'];    
                            }
                        }else{
                            $dia_base = $horarios[$index]['hor_dia'];
                        }
                        $horarios_rta[] = $horarios[$index];
                    }   
                    
                                        
                    if (! is_null ($horarios_rta)){
                        $this->response(array('response' => $horarios_rta), 200);
                    }else{
                        $this->response(array('response' => 'No hay horarios cargados'), 200);
                    }
                }      
            //POST
                /*
                    horario: {
                        "id_tur_prestadores": "1",
                        "horarios": [{
                                "hor_dia": "Lunes",
                                "hor_inicio": "10:00",
                                "hor_fin": "12:00"
                            }, {
                                "hor_dia": "Lunes",
                                "hor_inicio": "16:00",
                                "hor_fin": "20:00"
                            },
                            {
                                "hor_dia": "Lunes",
                                "hor_inicio": "22:00",
                                "hor_fin": "23:00"
                            }
                        ]
                    }
                */
                public function horario_post($type = 'php'){ 
                    if ($this->post('horario') !== NULL){
                        if ($type == 'json'){
                            $horario = json_decode($this->post('horario'),true);    
                        }else{
                            $horario = $this->post('horario');
                        }
                        
                        $rta_ids = array();

                        foreach ($horario['horarios'] as $row){
                            $row['id_tur_prestadores'] = $horario['id_tur_prestadores'];
                            $rta_ids[] = $this->tur_horarios_model->save($row);    
                        }  

                        if (! empty ($rta_ids)){
                            $response_200['message'] = 'Horarios cargados correctamente!.';
                            $response_200['id_cargado'] = implode(", ", $rta_ids);
                            $this->response(array('response' => $response_200), 200);
                        }else{
                            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar los horarios.'), 400);
                        }        
                    }else{
                        $this->response(array('error' => 'Faltan datos para la carga de los horarios.'), 400);    
                    }

                } 
            //DELETE
                public function horario_delete($id_horario){
                    if (! $id_horario){
                        $this->response(NULL, 400);
                    }

                    $delete = $this->tur_horarios_model->delete($id_horario);

                    if (! is_null ($delete)){
                        $this->response(array('response' => 'Horario eliminado.'), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el horario.'), 400);
                    }    
                }
        
        //SERVICIOS
            //GET
                public function servicios_get($id_prestador = NULL){
                    $servicios = $this->tur_servicios_model->get(NULL, $id_prestador, 'simple');

                    if (! is_null ($servicios)){
                        $this->response(array('response' => $servicios), 200);
                    }else{
                        $this->response(array('response' => 'No hay servicios cargados'), 200);
                    }
                }  
            //POST        
                /*servicio:{
                    "id_tur_prestadores": "1",
                    "servicios": [{
                        "ser_nombre": "Nombre del Servicio",
                        "ser_precio": "120.00",
                        "ser_duracion": "45",
                        "ser_descripcion": "Bla bla bla"
                    },{
                        "ser_nombre": "Nombre del Servicio",
                        "ser_precio": "120.00",
                        "ser_duracion": "45",
                        "ser_descripcion": "Bla bla bla"
                    }]
                }*/
                public function servicio_post($type = 'php'){ 
                    if ($this->post('servicio') !== NULL){
                        if ($type == 'json'){
                            $servicio = json_decode($this->post('servicio'),true);    
                        }else{
                            $servicio = $this->post('servicio');
                        }
                        
                        $rta_ids = array();

                        foreach ($servicio['servicios'] as $row){
                            $row['id_tur_prestadores'] = $servicio['id_tur_prestadores'];
                            $rta_ids[] = $this->tur_servicios_model->save($row);    
                        }  

                        if (! empty ($rta_ids)){
                            $response_200['message'] = 'Servicios cargados correctamente!.';
                            $response_200['id_cargado'] = implode(", ", $rta_ids);
                            $this->response(array('response' => $response_200), 200);
                        }else{
                            $this->response(array('error' => 'Ha ocurrido un error al intentar guardar los servicios.'), 400);
                        }        
                    }else{
                        $this->response(array('error' => 'Faltan datos para la carga de servicios.'), 400);    
                    }

                }   
            //DELETE
                public function servicio_delete($id_servicio){
                    if (! $id_servicio){
                        $this->response(NULL, 400);
                    }

                    $delete = $this->tur_servicios_model->delete($id_servicio);

                    if (! is_null ($delete)){
                        $this->response(array('response' => 'Servicio eliminado.'), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar eliminar el servicio.'), 400);
                    }    
                } 
			//PUT
				public function servicio_put($id_servicio, $type = 'php'){
                    if (is_null ($id_servicio)){
                        $this->response(array('error' => 'Falta el ID del Servicio a Editar.'), 400);
                    } 
                    
                    if ($type == 'json'){
                        $servicio = json_decode($this->put('servicio'),true);    
                    }else{
                        $servicio = $this->put('servicio');
                    }

                    $this->tur_servicios_model->update($id_servicio, $servicio);

                    if (! is_null ($id_servicio)){
                        $this->response(array('response' => $id_servicio), 200);
                    }else{
                        $this->response(array('error' => 'Ha ocurrido un error al intentar actualizar.'), 400);
                    }        
                }
		
		//INSTITUCIONES
			//GET
				public function instituciones_get($id_institucion = NULL, $buscar = NULL){
                    $instituciones = $this->tur_instituciones_model->get($id_institucion, $buscar);

                    if (! is_null ($instituciones)){
                        $this->response($instituciones, 200);
                    }else{
                        $this->response(array(), 200);
                    }
                }
            
    }