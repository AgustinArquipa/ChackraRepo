<?php
    defined("BASEPATH") or die("Acceso prohibido");

    class Componentes_model extends CI_Model{
        public function __construct(){
            parent::__construct();
            $this->tabla = 'componentes';
            $this->col_id = 'id_'.$this->tabla;
        }
        
        public function get($id = NULL, $tipo = NULL){
            $this->db->select("*");
            $this->db->from($this->tabla); 
            
            if ($id != NULL){
                $this->db->where($this->col_id, $id);    
            }

            if ($tipo != NULL){
                $this->db->where('com_tipos', $tipo);    
            }
            
            $this->db->order_by('com_nombre', 'ASC');
            
            $consulta = $this->db->get();
            
            
            if ($consulta->num_rows() > 0){
                return $consulta->result_array();
            }
            return NULL;
        }
        
        public function save($datos){
            $this->db->set(
                $datos
            )
            ->insert($this->tabla);
            
            if ($this->db->affected_rows() === 1){
                return $this->db->insert_id();
            }
            return NULL;
        }
        
        public function update($id, $datos){
            if (isset($datos['com_tiempo_apagado'])){
                //CONVIERTO DE MINUTOS A MILISEGUNDOS
                $datos['com_tiempo_apagado'] = $datos['com_tiempo_apagado'] * 60000;
            }
            
            $this->db->set(
                $datos
            )
            ->where($this->col_id, $id)
            ->update($this->tabla);
            
            log_message('ERROR', 'Entra');

            if ($this->db->affected_rows() === 1){
                log_message('ERROR', print_r($this->db->last_query(),1));
                return $this->db->insert_id();
            }
            return NULL;    
        }
        
        public function delete($id){
            $this->db->where($this->col_id,$id)->delete($this->tabla);
            if ($this->db->affected_rows() === 1){
                return TRUE;
            }
            return FALSE;
        }            
    }