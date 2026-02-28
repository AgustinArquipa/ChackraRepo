<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Firebase extends CI_Controller {

        public function __construct(){
            
            parent::__construct();
            

        }
		

        public function enviarNotificacion($id_receptor, $datos, $tipo_notificacion, $tipo_entidad, $id = NULL, $id_emisor = NULL){
            
            switch($tipo_notificacion){
				case 'pedido_delivery':
					if (!is_null($id)){
						$datos = $this->del_pedidos_model->get($id);    
					}else{
						$datos = array();
					}
				break;
				case 'modificacion_turno':
				case 'reserva_turno':
					if (!is_null($id)){
						$datos = $this->tur_turnos_model->get($id);    
					}else{
						$datos = array();
					}
            }
            
            

            // CAMBIAR ARRIBA CUANDO LA DEJE EN PRODUCCION!
            //$id = '/topics/'.TOPIC;            

            /*$id = 'foSfnTj8Wd0:APA91bGjIdbtJs1ioM3_qBC9w3sd_G-iaoThAZdUdxSTIcMY7sBbFrroW3Uj9TDttn45a5a7F5I11k_WtLyiIM6hdZ2uCfCfsFIMMG0Un5TbEoJ530h3f76_WU9gXgKRar4QMew3vq4n';
            
            $id = 'dqAIJ0teoY0:APA91bGfZN-4YshUnQ5vRwR62RQo4u7BXVoBQ7hElqZYPCUxSbHZWe7J26rF3V4ihiuLm-w4kdJZE1d5QvZXFTCA5nSkbiBIsoRWWg1N_FNHjPoGWHKRELnvh8LJ_94NS5wbEEKogvNq';*/
            
            $url = 'https://fcm.googleapis.com/fcm/send';

            $data = array ("tipos" => array ("notificacion" => $tipo_notificacion, "entidad" => $tipo_entidad), "datos" => $datos);     
            
            $fields = array (            
                'data' => $data,
				'priority' => 'high',
                //'to' => 'fd9eQTRBzrk:APA91bFQQ5R7cXLpfezWTuIewYndZJh-4t4q1yWQr4qvO2fsEn1FK0FsI4JgqtpCgh5QFMfavu3pzZS77fAXfYRH94ui4XDKZZoxfElZa-KYnmuvkQSbD5hMrtLbase1b0OkF9j3Jhxi'
                //'to' => $id_receptor
				'registration_ids' => array (
						$id_receptor
				)
            );

            $fields = json_encode ( $fields );

            $headers = array (
                'Content-Type:application/json',                        
                'Authorization:key=AIzaSyCQLWVaRNrZaq-aUlDCr0q7-29NEE8b8X4' // Cuenta: DEVFIREBASE123
            );

            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

            $result = curl_exec ( $ch );
			
			$datos_guardar = array( 'id_usuarios' => $id_emisor, 'id_receptor' => $id_receptor, 'not_datos' => json_encode($data), 'not_respuesta' => $result); 
			$this->notificaciones_model->save($datos_guardar);

            return $result;

            curl_close ( $ch );           
        }
    }
