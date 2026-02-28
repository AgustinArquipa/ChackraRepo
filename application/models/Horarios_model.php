<?php
    defined("BASEPATH") or die("Acceso prohibido");

    class Horarios_model extends CI_Model{
        public function __construct(){
            parent::__construct();
            $this->tabla = 'horarios';
            $this->col_id = 'id_'.$this->tabla;
        }
        
        public function get($id = NULL, $fecha = NULL, $id_componente = NULL){
            $this->db->select("*");
            $this->db->from($this->tabla); 
            
            if ($id != NULL){
                $this->db->where($this->col_id, $id);    
            }

            if ($fecha != NULL){
                $this->db->where('hor_fecha', $fecha);    
            }

            if ($id_componente != NULL){
                $this->db->where('id_componentes', $id_componente);    
            }
            
            $this->db->order_by('hor_fecha', 'DESC');
            
            $consulta = $this->db->get();
            
            log_message('debug', print_r($this->db->last_query(),1));
            
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
            $this->db->set(
                $datos
            )
            ->where($this->col_id, $id)
            ->update($this->tabla);
            
            if ($this->db->affected_rows() === 1){
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