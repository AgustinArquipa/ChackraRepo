<?php  
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require_once APPPATH . '/libraries/REST_Controller.php';

    class Delivery extends REST_Controller {
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
            $this->load->model('del_prestadores_model');
            $this->load->model('del_categorias_model');
            $this->load->model('del_productos_model');
            $this->load->model('del_pedidos_model');
            $this->load->model('del_pedidos_detalles_model');            
			$this->load->model('del_horarios_model');
            $this->load->model('usuarios_model');

            $this->load->library('image_moo');

            $this->dias = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
        }
        
        //PRESTADORES  
            //GET
                public function prestador_get($id_prestador){
                    $prestador = $this->del_prestadores_model->get($id_prestador);

                    if (! is_null ($prestador)){
                        $prestador;
                        $productos = $this->del_productos_model->get(NULL, $id_prestador, 'simple');
						$horarios = $this->del_horarios_model->get(NULL, $id_prestador, NULL, 'simple');
						if (is_null($productos)){
							$productos = array();	
						}						
						if (is_null($horarios)){
							$horarios = array();	
						}
                        $prestador['productos'] = $productos;
						$prestador['horarios'] = $horarios;
                    }

                    if (! is_null ($prestador)){
                        $this->response($prestador, 200);
                    }else{
                        $objeto_vacio = new stdClass();
                        $this->response($objeto_vacio, 200);
                    } 
                }
        
                public function prestadores_get($id_categorias = NULL, $id_usuario = NULL, $buscar = NULL){
                    $prestadores = $this->del_prestadores_model->get(NULL, $id_categorias, $id_usuario, $buscar);

                    if (! is_null ($prestadores)){
                        $this->response($prestadores, 200);
                    }else{
                        $this->response(array(), 200);  
                    }
                }
		
				public function prestador_post($type = 'php'){
					if ($this->post('prestador') !== NULL){
						//log_message('error', print_r($_FILES,1));
						if (isset($_FILES) && count($_FILES) > 0){
							$files = $_FILES;
							$cpt = count($_FILES);
							$carpeta = 'prestadores';
							$config['upload_path'] = 'assets/uploads/delivery/'.$carpeta.'/';
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
								$ruta_base = $config['upload_path'];
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
							$this->response('Oops! No envió fotos para cargar.', 400);   
						}  

						if ($type == 'json'){
							$nuevo_registro = json_decode($this->post('prestador'),true);    
						}else{
							$nuevo_registro = $this->post('prestador');
						}

						if (isset($imagenesv) && count($imagenesv) > 0){
							$nuevo_registro['pre_foto'] = $imagenesv[0];
							$nuevo_registro['pre_foto_portada'] = $imagenesv[1];
						}

						//log_message('error', print_r($prestador,1)); 

						$id = $this->del_prestadores_model->save($nuevo_registro);        

						if (! is_null ($id)){
							$response_200['message'] = 'Prestador cargado correctamente!.';
							$response_200['id_cargado'] = $id;
							$this->response($response_200, 200);
						}else{
							$this->response('Ha ocurrido un error al intentar guardar el prestador.', 400);
						}      
					}else{
						$this->response('Faltan datos para la carga del prestador.', 400);      
					}	
				}
		
				public function prestador_put($id, $type = 'php'){                     
                    if (is_null ($id)){
                        $this->response(array('error' => 'Falta el ID del Prestador a Actualizar.'), 400);
                    }                    
                    if ($type == 'json'){
                        $prestador = json_decode($this->put('prestador'),true);    
                    }else{
                        $prestador = $this->put('prestador');
                    }
										
					if ($this->del_prestadores_model->update($id, $prestador)){
						$this->response(array('response' => true), 200);
                    }else{
                        $this->response(array('response' => false), 400);
                    }        
                }
        
        //CATEGORIAS
            //GET
                public function categorias_get(){
                    $categorias = $this->del_categorias_model->get();

                    if (! is_null ($categorias)){
                        $this->response($categorias, 200);
                    }else{
                        $this->response(array(), 200);
                    }
                }
        
		//PRODUCTOS
		
			//POST
				public function producto_post($type = 'php'){
					if ($this->post('producto') !== NULL){
						//log_message('error', print_r($_FILES,1));
						if (isset($_FILES) && count($_FILES) > 0){
							$files = $_FILES;
							$cpt = count($_FILES);
							$carpeta = 'productos';
							$config['upload_path'] = 'assets/uploads/delivery/'.$carpeta.'/';
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
								$ruta_base = $config['upload_path'];
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
							$this->response('Oops! No envió fotos para cargar.', 400);   
						}  

						if ($type == 'json'){
							$producto = json_decode($this->post('producto'),true);    
						}else{
							$producto = $this->post('producto');
						}

						if (isset($imagenesv) && count($imagenesv) > 0){
							$producto['pro_imagen'] = $imagenesv[0];
						}

						//log_message('error', print_r($prestador,1)); 

						$id_producto = $this->del_productos_model->save($producto);        

						if (! is_null ($id_producto)){
							$response_200['message'] = 'Producto cargado correctamente!.';
							$response_200['id_cargado'] = $id_producto;
							$this->response($response_200, 200);
						}else{
							$this->response('Ha ocurrido un error al intentar guardar el producto.', 400);
						}      
					}else{
						$this->response('Faltan datos para la carga del producto.', 400);      
					}	
				}
				 
        //PEDIDOS
			//GET
                public function pedidos_get($id_prestador = NULL, $id_usuarios = NULL, $fecha = NULL, $pedido = NULL){
                    $pedidos = $this->del_pedidos_model->get(NULL, $id_prestador, $id_usuarios, $fecha, $pedido);

                    if (! is_null ($pedidos)){
                        $this->response($pedidos, 200);
                    }else{
                        $this->response(array(), 200);
                    }
                }		
				public function pedido_get($id_pedido= NULL){
                    $pedido = $this->del_pedidos_model->get($id_pedido);

                    if (! is_null ($pedido)){
						$detalle = $this->del_pedidos_detalles_model->get(NULL, $id_pedido);
						if (is_null($detalle)){
							$detalle = array();	
						}
                        $pedido['detalle'] = $detalle;
						
                        $this->response($pedido, 200);
                    }else{
                        $this->response(array(), 200);
                    }
                }
            //POST
                public function pedido_post($type = 'php'){
                    if (!$this->post('pedido')){
                        $this->response(NULL, 400);
                    }else if (!$this->post('pedido_detalle')){
                        $this->response(NULL, 400);    
                    }
                                                               
                    if ($type == 'json'){
                        $pedido = json_decode($this->post('pedido'),true);    
                    }else{
                        $pedido = $this->post('pedido');
                    }
					
					
                    
                    $id_pedido = $this->del_pedidos_model->save($pedido);
                                                               
                    if ($type == 'json'){
                        $pedido_detalle = json_decode($this->post('pedido_detalle'),true);    
                    }else{
                        $pedido_detalle = $this->post('pedido_detalle');
                    } 
                    
                    $id_pedido_detallev = array();
                    
                    //log_message('ERROR', print_r($pedido_detalle,1));
                    
                    foreach ($pedido_detalle as $row){
						if (isset($row['imagen'])){
							unset($row['imagen']);
						}
						
						if (isset($row['pro_nombre'])){
							unset($row['pro_nombre']);
						}
						
                        //log_message('ERROR', print_r($row,1));
                        $row['id_del_pedidos'] = $id_pedido;
                        $id_pedido_detallev[] = $this->del_pedidos_detalles_model->save($row);    
                    }
                    
                    $response_200['message'] = 'Pedido cargado correctamente!.';
                    $response_200['id_cargado'] = $id_pedido;
					
					$prestador = $this->del_prestadores_model->get($pedido['id_del_prestadores']);
					$cliente = $this->usuarios_model->get($pedido['id_usuarios']);
					$response_200['id_firebase'] = $this->enviarNotificacion($prestador['id_notificacion'], $pedido, 'pedido_delivery', 'prestador', $id_pedido, $cliente['id_usuarios']);
					
                    $this->response($response_200, 200);
                }
			//PUT
				public function pedido_put($id, $type = 'php'){                     
                    if (is_null ($id)){
                        $this->response(array('error' => 'Falta el ID del Pedido a Actualizar.'), 400);
                    }                    
                    if ($type == 'json'){
                        $pedido = json_decode($this->put('pedido'),true);    
                    }else{
                        $pedido = $this->put('pedido');
                    }
										
					if ($this->del_pedidos_model->update($id, $pedido)){
						$pedido = $this->del_pedidos_model->get($id);
						
						$prestador = $this->del_prestadores_model->get($pedido['id_del_prestadores']);
						$cliente = $this->usuarios_model->get($pedido['id_usuarios']);

						switch($pedido['ped_estado']){
							case 'Cancelado por Usuario':
							case 'Pedido':
								$receptor = $prestador['id_notificacion'];
								$tipo_entidad = 'prestador';
								$emisor = $cliente['id_usuarios'];
								break;
							case 'Cancelado por Prestador':
							case 'Preparando':
							case 'Enviado':
								$receptor = $cliente['id_notificacion'];
								$tipo_entidad = 'cliente';
								$emisor = $prestador['id_usuarios'];								
						}					

						$response_200['id_firebase'] = $this->enviarNotificacion($receptor, $pedido, 'pedido_delivery', $tipo_entidad, $id, $emisor);
						
						$this->response(array('response' => true), 200);
                    }else{
                        $this->response(array('response' => false), 400);
                    }        
                }
		
		//HORARIOS
            //GET
                public function horarios_get($id_prestador = NULL, $tipo = ''){
                    $horarios = $this->del_horarios_model->get(NULL, $id_prestador, NULL, 'simple');
                    
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
                        unset ($horarios[$index]['id_del_prestadores']);
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
                        $this->response($horarios_rta, 200);
                    }else{
                        $this->response('No hay horarios cargados', 200);
                    }
                }      
            //POST
                /*
                    horario: {
                        "id_del_prestadores": "1",
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
                            $nuevo_registro = json_decode($this->post('horario'),true);    
                        }else{
                            $nuevo_registro = $this->post('horario');
                        }
                        
                        $rta_ids = array();

                        foreach ($nuevo_registro['horarios'] as $row){
                            $row['id_del_prestadores'] = $nuevo_registro['id_del_prestadores'];
                            $rta_ids[] = $this->del_horarios_model->save($row);    
                        }  

                        if (! empty ($rta_ids)){
                            $response_200['message'] = 'Horarios cargados correctamente!.';
                            $response_200['id_cargado'] = implode(", ", $rta_ids);
                            $this->response($response_200, 200);
                        }else{
                            $this->response('Ha ocurrido un error al intentar guardar los horarios.', 400);
                        }        
                    }else{
                        $this->response('Faltan datos para la carga de los horarios.', 400);    
                    }

                } 
            //DELETE
                public function horario_delete($id_horario){
                    if (! $id_horario){
                        $this->response(NULL, 400);
                    }

                    $delete = $this->del_horarios_model->delete($id_horario);

                    if (! is_null ($delete)){
                        $this->response('Horario eliminado.', 200);
                    }else{
                        $this->response('Ha ocurrido un error al intentar eliminar el horario.', 400);
                    }    
                }
    }