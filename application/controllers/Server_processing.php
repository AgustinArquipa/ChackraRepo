<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Server_processing extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('Grocery_CRUD');
		$this->load->library('OutputView');
        $this->load->library('SSP_class');
        
        $this->script_datatables = '"language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        }';
        
        $this->sql_details = array(
            'user' => $this->db->username,
            'pass' => $this->db->password,
            'db'   => $this->db->database,
            'host' => $this->db->hostname
        );
        
        $this->empresa = $this->session->userdata('empresa');
        
        $this->user = $this->ion_auth->user()->row();
	}
    
    ///////////// MODULOS PRINCIPALES /////////////
    
    public function avisos(){
        $this->accion = "avisos";
        $this->tabla_principal = "avisos";
        
        $primaryKey = 'A.id_'.$this->tabla_principal;
    
        $this->tabla = "avisos A 
                LEFT JOIN avisos_categorias AC ON A.id_avisos_categorias = AC.id_avisos_categorias
                LEFT JOIN avisos_subcategorias ASUB ON A.id_avisos_subcategorias = ASUB.id_avisos_subcategorias
                LEFT JOIN usuarios U ON A.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY A.id_avisos';

        $columns = array(
            array( 'db' => 'id_avisos', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'avi_cat_nombre', 'dt' => '2'),
            array( 'db' => 'avi_sub_nombre', 'dt' => '3'),
            array( 'db' => 'avi_titulo', 'dt' => '4'),
            array( 
                'db' => 'id_avisos',            
                'dt' => '5',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	   
                            <li>
                                <a href="#" data-title="Detalle Aviso" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "A.id_avisos, U.username, AC.avi_cat_nombre, ASUB.avi_sub_nombre, A.avi_titulo";
        
        $column_where = array( 'A.id_avisos', 'U.username', 'AC.avi_cat_nombre', 'ASUB.avi_sub_nombre', 'A.avi_titulo');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
	public function notificaciones(){
        $this->accion = "notificaciones";
        $this->tabla_principal = "notificaciones";
        
        $primaryKey = 'N.id_'.$this->tabla_principal;
    
        $this->tabla = "notificaciones N
                LEFT JOIN usuarios U ON N.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_notificaciones', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'not_fecha_alta', 'dt' => '2'),
            array( 
                'db' => 'id_notificaciones',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
								<a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
							</li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "N.id_notificaciones, U.username, N.not_fecha_alta";
        
        $column_where = array( 'N.id_notificaciones', 'U.username', 'N.not_fecha_alta');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
	
    public function mascotas(){
        $this->accion = "mascotas";
        $this->tabla_principal = "mascotas";
        
        $primaryKey = 'M.id_'.$this->tabla_principal;
    
        $this->tabla = "mascotas M
                LEFT JOIN usuarios U ON M.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_mascotas', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'mas_titulo', 'dt' => '2'),
            array( 
                'db' => 'id_mascotas',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "M.id_mascotas, U.username, M.mas_titulo";
        
        $column_where = array( 'M.id_mascotas', 'U.username', 'M.mas_titulo');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function obituarios(){
        $this->accion = "obituarios";
        $this->tabla_principal = "obituarios";
        
        $primaryKey = 'O.id_'.$this->tabla_principal;
    
        $this->tabla = "obituarios O
                LEFT JOIN usuarios U ON O.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_obituarios', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'nombre_completo', 'dt' => '2'),
            array( 
                'db' => 'id_obituarios',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Obituario" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "O.id_obituarios, U.username, O.nombre_completo";
        
        $column_where = array( 'O.id_obituarios', 'U.username', 'O.nombre_completo');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function profesionales(){
        $this->accion = "profesionales";
        $this->tabla_principal = "profesionales";
        
        $primaryKey = 'P.id_'.$this->tabla_principal;
    
        $this->tabla = "profesionales P
                LEFT JOIN usuarios U ON P.id_usuarios = U.id_usuarios
                LEFT JOIN profesionales_rubros PR ON P.id_profesionales_rubros = PR.id_profesionales_rubros";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_profesionales', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'pro_rub_nombre', 'dt' => '2'),
            array( 'db' => 'nombre_completo', 'dt' => '3'),
            array( 'db' => 'mp', 'dt' => '4'),
            array( 
                'db' => 'id_profesionales',            
                'dt' => '5',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Profesional" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "P.id_profesionales, U.username, PR.pro_rub_nombre, P.nombre_completo, P.mp";
        
        $column_where = array( 'P.id_profesionales', 'U.username', 'PR.pro_rub_nombre', 'P.nombre_completo', 'P.mp');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function denuncias(){
        $this->accion = "denuncias";
        $this->tabla_principal = "denuncias";
        
        $primaryKey = 'D.id_'.$this->tabla_principal;
    
        $this->tabla = "denuncias D
                LEFT JOIN usuarios U ON D.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_denuncias', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'den_titulo', 'dt' => '2'),
            array( 
                'db' => 'id_denuncias',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Obituario" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "D.id_denuncias, U.username, D.den_titulo";
        
        $column_where = array( 'D.id_denuncias', 'U.username', 'D.den_titulo');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function comentarios(){
        $this->accion = "comentarios";
        $this->tabla_principal = "comentarios";
        
        $primaryKey = 'C.id_'.$this->tabla_principal;
    
        $this->tabla = "comentarios C
                LEFT JOIN usuarios U ON C.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_comentarios', 'dt' => '0'),
            array( 'db' => 'username', 'dt' => '1'),
            array( 'db' => 'tabla_foranea_nombre', 'dt' => '2'),
            array( 
                'db' => 'id_comentarios',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Comentario" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().'modulos_principales/'.$this->tabla_principal.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "C.id_comentarios, U.username, C.tabla_foranea_nombre";
        
        $column_where = array( 'C.id_comentarios', 'U.username', 'C.tabla_foranea_nombre');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    ///////////// MODULOS GENERALES /////////////

    public function avisos_categorias(){
        $this->accion = "avisos_categorias";
        $this->tabla = "avisos_categorias";
        
        // Table's primary key
        $primaryKey = 'id_'.$this->tabla;

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            
            array( 'db' => 'avi_cat_nombre', 'dt' => '0'),
            array( 
            'db' => $primaryKey, 
            'dt' => '1',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                    <li>
						<a href="'.base_url().'modulos_generales/'.$this->tabla.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
					</li>
					<li>
						<a href="'.base_url().'modulos_generales/'.$this->tabla.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
					</li>
                    <li>
                    	<a href="'.base_url().'modulos_generales/'.$this->tabla.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                    </li>
                    </ul>';
                return $buttons;
            })
        );
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns));

        echo $rta;
    }
    
    ///////////// MODULO TURNERO ///////////// 
    
    public function categorias(){
        $this->accion = "categorias";
        $this->tabla = "tur_categorias";
        $this->modulo = 'modulo_turnero';
        
        // Table's primary key
        $primaryKey = 'id_'.$this->tabla;

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            
            array( 'db' => 'cat_nombre', 'dt' => '0'),
            array( 
            'db' => $primaryKey, 
            'dt' => '1',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                    <li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
					</li>
					<li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
					</li>
                    <li>
                    	<a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                    </li>
                    </ul>';
                return $buttons;
            })
        );
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns));

        echo $rta;
    }
    
    public function subcategorias(){
        $this->accion = "subcategorias";
        $this->tabla_principal = "tur_subcategorias";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'S.id_'.$this->tabla_principal;
    
        $this->tabla = "tur_subcategorias S
                LEFT JOIN tur_categorias C ON S.id_tur_categorias = C.id_tur_categorias";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'cat_nombre', 'dt' => '0'),
            array( 'db' => 'sub_nombre', 'dt' => '1'),
            array( 
                'db' => 'id_tur_subcategorias',            
                'dt' => '2',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "S.id_tur_subcategorias, C.cat_nombre, S.sub_nombre";
        
        $column_where = array( 'S.id_tur_subcategorias', 'C.cat_nombre', 'S.sub_nombre');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function horarios(){
        $this->accion = "horarios";
        $this->tabla_principal = "tur_horarios";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'H.id_'.$this->tabla_principal;
    
        $this->tabla = "tur_horarios H
                LEFT JOIN tur_prestadores P ON H.id_tur_prestadores = P.id_tur_prestadores";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'pre_nombre_completo', 'dt' => '0'),
            array( 'db' => 'hor_dia', 'dt' => '1'),
            array( 'db' => 'hor_inicio', 'dt' => '2'),
            array( 'db' => 'hor_fin', 'dt' => '3'),
            array( 
                'db' => 'id_tur_horarios',            
                'dt' => '4',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "H.id_tur_horarios, P.pre_nombre_completo, H.hor_dia, H.hor_inicio, H.hor_fin";
        
        $column_where = array( 'H.id_tur_horarios', 'P.pre_nombre_completo', 'H.hor_dia', 'H.hor_inicio', 'H.hor_fin');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function servicios(){
        $this->accion = "servicios";
        $this->tabla_principal = "tur_servicios";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'S.id_'.$this->tabla_principal;
    
        $this->tabla = "tur_servicios S
                LEFT JOIN tur_prestadores P ON S.id_tur_prestadores = P.id_tur_prestadores";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_tur_servicios', 'dt' => '0'),
            array( 'db' => 'pre_nombre_completo', 'dt' => '1'),
            array( 'db' => 'ser_nombre', 'dt' => '2'),
            array( 'db' => 'ser_precio', 'dt' => '3'),
            array( 'db' => 'ser_duracion', 'dt' => '4'),
            array( 
                'db' => 'id_tur_servicios',            
                'dt' => '5',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->accion.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "S.id_tur_servicios, P.pre_nombre_completo, S.ser_nombre, S.ser_precio, S.ser_duracion";
        
        $column_where = array( 'S.id_tur_servicios', 'P.pre_nombre_completo', 'S.ser_nombre', 'S.ser_precio', 'S.ser_duracion');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function prestadores(){
        $this->accion = "prestadores";
        $this->tabla_principal = "tur_prestadores";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'P.id_'.$this->tabla_principal;
    
        $this->tabla = "tur_prestadores P
                LEFT JOIN tur_instituciones I ON P.id_tur_instituciones = I.id_tur_instituciones
                LEFT JOIN tur_categorias C ON P.id_tur_categorias = C.id_tur_categorias
                LEFT JOIN tur_subcategorias S ON P.id_tur_subcategorias = S.id_tur_subcategorias
                LEFT JOIN usuarios U ON P.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_tur_prestadores', 'dt' => '0'),
            array( 'db' => 'pre_nombre_completo', 'dt' => '1'),
            array( 
                'db' => 'ins_nombre',            
                'dt' => '2',
                'formatter' => function( $d, $row ) {
                    if ($row['ins_nombre'] == ''){
                        $row['ins_nombre'] = 'Particular';
                    }else{
                        $row['ins_nombre'] = '<strong>'.$row['ins_nombre'].'</strong>';    
                    }
                    return $row['ins_nombre'];
            }),
            array( 'db' => 'cat_nombre', 'dt' => '3'),
            array( 'db' => 'sub_nombre', 'dt' => '4'),
            array( 
                'db' => 'id_tur_prestadores',            
                'dt' => '5',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->accion.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "P.id_tur_prestadores, P.pre_nombre_completo, C.cat_nombre, S.sub_nombre, I.ins_nombre";
        
        $column_where = array( 'P.id_tur_prestadores', 'P.pre_nombre_completo', 'C.cat_nombre', 'S.sub_nombre', 'I.ins_nombre');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function turnos(){
        $this->accion = "turnos";
        $this->tabla_principal = "tur_turnos";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'T.id_'.$this->tabla_principal;
    
        $this->tabla = "tur_turnos T
                LEFT JOIN tur_prestadores P ON T.id_tur_prestadores = P.id_tur_prestadores
                LEFT JOIN tur_servicios S ON T.id_tur_servicios = S.id_tur_servicios
                LEFT JOIN usuarios U ON P.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_tur_turnos', 'dt' => '0'),
            array( 'db' => 'pre_nombre_completo', 'dt' => '1'),
            array( 'db' => 'ser_nombre', 'dt' => '2'),
            array( 'db' => 'tur_fecha', 'dt' => '3'),
            array( 'db' => 'tur_inicio', 'dt' => '4'),
            array( 'db' => 'tur_fin', 'dt' => '5'),
            array( 
                'db' => 'id_tur_turnos',            
                'dt' => '6',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Mascota" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->accion.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "T.id_tur_turnos, P.pre_nombre_completo, S.ser_nombre, T.tur_fecha, T.tur_inicio, T.tur_fin";
        
        $column_where = array( 'T.id_tur_turnos', 'P.pre_nombre_completo', 'S.tur_servicios', 'T.tur_fecha', 'T.tur_inicio', 'T.tur_fin');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
    
    public function instituciones(){
        $this->accion = "instituciones";
        $this->tabla = "tur_instituciones";
        $this->modulo = 'modulo_turnero';
        
        // Table's primary key
        $primaryKey = 'id_'.$this->tabla;

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            
            array( 'db' => 'ins_nombre', 'dt' => '0'),
            array( 
            'db' => $primaryKey, 
            'dt' => '1',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                    <li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
					</li>
					<li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
					</li>
                    <li>
                    	<a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                    </li>
                    </ul>';
                return $buttons;
            })
        );
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns));

        echo $rta;
    }
	
	public function instituciones_usuarios(){
        $this->accion = "tur_instituciones_usuarios";
        $this->tabla_principal = "tur_instituciones_usuarios";
        $this->modulo = 'modulo_turnero';
        
        $primaryKey = 'IU.id_tur_instituciones_usuarios';
    
        $this->tabla = "tur_instituciones_usuarios IU
                LEFT JOIN tur_instituciones I ON IU.id_instituciones = I.id_tur_instituciones";
        
        $group_by = 'GROUP BY '.$primaryKey;
		
        $columns = array(            
            array( 'db' => 'ins_usu_nombre_completo', 'dt' => '0'),
			array( 'db' => 'ins_nombre', 'dt' => '1'),
            array( 
            'db' => 'id_tur_instituciones_usuarios', 
            'dt' => '2',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                    <li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
					</li>
					<li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
					</li>
                    <li>
                    	<a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                    </li>
                    </ul>';
                return $buttons;
            })
        );
		
		$columns_select = "IU.id_tur_instituciones_usuarios, IU.ins_usu_nombre_completo, I.ins_nombre";
        
        $column_where = array( 'IU.id_tur_instituciones_usuarios', 'IU.ins_usu_nombre_completo', 'I.ins_nombre');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
	
	///////////// MODULO DELIVERY ///////////// 
    
    public function del_categorias(){
        $this->accion = "categorias";
        $this->tabla = "del_categorias";
        $this->modulo = 'modulo_delivery';
        
        // Table's primary key
        $primaryKey = 'id_'.$this->tabla;

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            
            array( 'db' => 'cat_nombre', 'dt' => '0'),
            array( 
            'db' => $primaryKey, 
            'dt' => '1',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                    <li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
					</li>
					<li>
						<a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
					</li>
                    <li>
                    	<a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                    </li>
                    </ul>';
                return $buttons;
            })
        );
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns));

        echo $rta;
    }
	
	public function del_horarios(){
        $this->accion = "horarios";
        $this->tabla_principal = "del_horarios";
        $this->modulo = 'modulo_delivery';
        
        $primaryKey = 'H.id_'.$this->tabla_principal;
    
        $this->tabla = "del_horarios H
                LEFT JOIN del_prestadores P ON H.id_del_prestadores = P.id_del_prestadores";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'pre_nombre_completo', 'dt' => '0'),
            array( 'db' => 'hor_dia', 'dt' => '1'),
            array( 'db' => 'hor_inicio', 'dt' => '2'),
            array( 'db' => 'hor_fin', 'dt' => '3'),
            array( 
                'db' => 'id_del_horarios',            
                'dt' => '4',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Horario" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->tabla_principal.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "H.id_del_horarios, P.pre_nombre_completo, H.hor_dia, H.hor_inicio, H.hor_fin";
        
        $column_where = array( 'H.id_del_horarios', 'P.pre_nombre_completo', 'H.hor_dia', 'H.hor_inicio', 'H.hor_fin');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
	
	public function del_prestadores(){
        $this->accion = "prestadores";
        $this->tabla_principal = "del_prestadores";
        $this->modulo = 'modulo_delivery';
        
        $primaryKey = 'P.id_'.$this->tabla_principal;
    
        $this->tabla = "del_prestadores P
                LEFT JOIN del_categorias C ON P.id_del_categorias = C.id_del_categorias
                LEFT JOIN usuarios U ON P.id_usuarios = U.id_usuarios";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_del_prestadores', 'dt' => '0'),
            array( 'db' => 'pre_nombre_completo', 'dt' => '1'),
            array( 'db' => 'cat_nombre', 'dt' => '2'),
            array( 
                'db' => 'id_del_prestadores',            
                'dt' => '3',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Prestador" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->accion.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "P.id_del_prestadores, P.pre_nombre_completo, C.cat_nombre";
        
        $column_where = array( 'P.id_del_prestadores', 'P.pre_nombre_completo', 'C.cat_nombre');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }
	
	public function del_productos(){
        $this->accion = "productos";
        $this->tabla_principal = "del_productos";
        $this->modulo = 'modulo_delivery';
        
        $primaryKey = 'PR.id_'.$this->tabla_principal;
    
        $this->tabla = "del_productos PR
                LEFT JOIN del_prestadores P ON PR.id_del_prestadores = P.id_del_prestadores";
        
        $group_by = 'GROUP BY '.$primaryKey;

        $columns = array(
            array( 'db' => 'id_del_productos', 'dt' => '0'),
            array( 'db' => 'pre_nombre_completo', 'dt' => '1'),
            array( 'db' => 'pro_nombre', 'dt' => '2'),
            array( 'db' => 'pro_precio', 'dt' => '3'),
            array( 
                'db' => 'id_del_productos',            
                'dt' => '4',
                'formatter' => function( $d, $row ) {
                    $buttons='
                        <ul class="tools list-unstyled table-menu">	                    
                            <li>
                                <a href="#" data-title="Detalle Productos" class="detalle_button" data-toggle="modal" data-target="#modal_general" data-id="'.$d.'" data-tabla="'.$this->accion.'"><i class="fa fa-list"></i> Detalle</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                            </li>
                            <li>
                                <a href="'.base_url().$this->modulo.'/'.$this->accion.'/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                            </li>
                        </ul>';
                    return $buttons;
            })
        );
        $columns_select = "PR.id_del_productos, P.pre_nombre_completo, PR.pro_nombre, PR.pro_precio";
        
        $column_where = array( 'PR.id_tur_servicios', 'P.pre_nombre_completo', 'PR.pro_nombre', 'PR.pro_precio');
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select, $group_by, $column_where));

        echo $rta;
    }

    public function usuarios(){
        $this->accion = "usuarios";
        $this->tabla_principal = "usuarios";
        
        // Table's primary key
        $primaryKey = 'id_'.$this->tabla_principal;
        
        $this->tabla = "usuarios U";

        $columns = array( 
            array( 'db' => $primaryKey, 'dt' => '0'),
            array( 'db' => 'id_facebook', 'dt' => '1'),
            array( 'db' => 'username', 'dt' => '2'),
            array( 'db' => 'nombre_completo', 'dt' => '3'),
            array( 
            'db' => $primaryKey, 
            'dt' => '4',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                        <li>
                            <a href="'.base_url().'modulos_principales/usuarios/read/'.$d.'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
                        </li>
                        <li>
                            <a href="'.base_url().'modulos_principales/usuarios/edit/'.$d.'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                        </li>';

                /*if ($this->ion_auth->in_group('admin') || $this->ion_auth->in_group('encargado')){
                    $buttons .= '
                        <li>
                            <a href="'.site_url().'modulos_principales/cumplimientos_asignados/'.$row["id"].'" title="Asignar" ><i class="fa fa-tasks"></i> Asignar</a>
                        </li>';    
                }*/

                $buttons .='
                        <li>
                            <a href="'.base_url().'modulos_principales/usuarios/delete/'.$d.'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                        </li>
                    </ul>';
                return $buttons;
            })
        );
        $columns_select = "U.id_usuarios, U.username, U.id_facebook, U.nombre_completo";
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select));
        
        echo $rta;    
    }
    
    public function users(){
        $this->accion = "users";
        $this->tabla = "users";
        
        // Table's primary key
        $primaryKey = 'id';
        
        $this->tabla = "users U";

        $columns = array( 
            array( 'db' => 'id', 'dt' => '0'),
            array( 'db' => 'apellido', 'dt' => '1'),
            array( 'db' => 'nombre', 'dt' => '2'),
            array( 'db' => 'username', 'dt' => '3'),
            array( 'db' => $primaryKey, 'dt' => '4', 'formatter' => function( $d, $row ) {
                if ($row[4] == 1){
                    $nueva_accion = 'Inactivo';
                    $class = 'activo';
                    $texto = 'Activo';
                }else{
                    $nueva_accion = 'Activo';
                    $class = 'inactivo';
                    $texto = 'Inactivo';
                }
                
                /*$buttons='
                    <a class="'.$class.'" href="'.base_url().'modulos_principales/cambiarEstado/'.$row["id"].'/'.$nueva_accion.'" title="Detalle" class="edit_button">'.$row['active'].'</a>';*/
                return $texto;    
            }),
            array( 
            'db' => $primaryKey, 
            'dt' => '5',
            'formatter' => function( $d, $row ) {
                $buttons='
                    <ul class="tools list-unstyled table-menu">	
                        <li>
                            <a href="'.base_url().'crud/users/read/'.$row["id"].'" title="Detalle" class="edit_button"><i class="fa fa-list"></i> Detalle</a>   
                        </li>
                        <li>
                            <a href="'.base_url().'crud/users/edit/'.$row["id"].'" title="Editar" class="edit_button"><i class="fa fa-pencil"></i> Editar</a> 
                        </li>';

                if ($this->ion_auth->in_group('admin') || $this->ion_auth->in_group('encargado')){
                    $buttons .= '
                        <li>
                            <a href="'.site_url().'modulos_principales/cumplimientos_asignados/'.$row["id"].'" title="Asignar" ><i class="fa fa-tasks"></i> Asignar</a>
                        </li>';    
                }

                $buttons .='
                        <li>
                            <a href="'.base_url().'crud/users/delete/'.$row["id"].'" title="Eliminar" class="delete-row" ><i class="fa fa-trash-o"></i> Eliminar</a>
                        </li>
                    </ul>';
                return $buttons;
            })
        );
        $columns_select = " U.apellido, U.nombre, U.id, U.username, U.active";
        
        $rta = json_encode($this->ssp_class->simple( $_GET, $this->sql_details, $this->tabla, $primaryKey, $columns, $columns_select));
        
        echo $rta;    
    }
	    
}