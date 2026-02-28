<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crud extends CI_Controller {
 
    function __construct()
    {
		parent::__construct();
		$this->load->library('Grocery_CRUD');
		$this->load->library('OutputView');
        $this->load->library('session');
        $this->load->model("empresas_model");
        $this->load->model("inspecciones_model");
        $this->load->model("formularios_model");
        $this->load->model('cumplimientos_asignados_model');
        $this->load->model('historial_model');
        $this->load->model('notas_rapidas_model');
        
        $this->script_datatables = '"language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        }';
    }
 	
    public function index()
    {
        
        //echo CI_VERSION;
        
		//if (!$this->ion_auth->is_admin())
        if (!$this->ion_auth)
		{
			return show_error('Debes estar identificado en el sistema.');
		}
		else
		{
            if($this->ion_auth->is_admin() || $this->ion_auth->in_group('inspector') || $this->ion_auth->in_group('encargado')){
                $template = 'admin_template'; 
                $data['judul'] = '';
                $view = 'escritorio_view';
                $data['settings'] = $this->db->get('settings', 1)->row();
                $data['title'] = $data['settings']->nombre;
                $data['version'] = $data['settings']->version; 
                $data['extra'] = '';
                $this->outputview->output_admin($view, $template, $data); 
            }else{
                return show_error('No tienes permisos');     
            }
			
		}
	}
    
    

    //USERS MANAGEMENT
    public function users()
    {
    	$crud = new Grocery_CRUD();

    	$crud->set_table('users');
    	$crud->set_subject('Usuarios');
    	$crud->columns('apellido', 'nombre', 'username', 'active')
            ->display_as('username','Nombre Usuario')
            ->display_as('groups','Grupos')
            ->display_as('active','Estado')
            ->display_as('apellido','Apellidos')
            ->display_as('nombre','Nombres')
            ->display_as('telefono','Telefono')
            ->display_as('id_provincias','Provincia')
            ->display_as('id_localidades','Localidad')
            ->display_as('id_empresas','Empresa')
            ->display_as('last_login','Ultimo Login')
            ->display_as('old_password','Clave Anterior (Cambiar: reset)')
            ->display_as('new_password','Nueva Clave');
        
        
    	if ($this->uri->segment(3) !== 'read')
		{
            $crud->add_fields('username','apellido', 'nombre', 'email', 'telefono', 'groups' , 'password', 'password_confirm', 'id_provincias', 'id_localidades');
			$crud->edit_fields('username', 'apellido', 'nombre',  'email', 'telefono', 'groups' , 'last_login','old_password','new_password', 'id_provincias', 'id_localidades');
            
            $crud->set_relation('id_provincias','provincias','{pro_nombre}');
            
            $crud->callback_add_field('id_localidades', array($this, 'cbklocalidades'));
            $crud->callback_edit_field('id_localidades', array($this, 'cbklocalidades'));
            
		}else{
			$crud->set_read_fields('username', 'apellido','nombre', 'email', 'telefono','groups', 'last_login', 'id_provincias', 'id_localidades');
		}
		$crud->set_relation_n_n('groups', 'users_groups', 'groups', 'user_id', 'group_id', 'name');

		//VALIDATION
		$crud->required_fields('username', 'apellido', 'nombre', 'email', 'telefono', 'password', 'password_confirm', 'id_provincias', 'id_localidades');
		$crud->set_rules('email', 'E-mail', 'required|valid_email');
		$crud->set_rules('telefono', 'telefono', 'required|numeric');
		$crud->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
   		$crud->set_rules('new_password', 'New password', 'min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

		//FIELD TYPES
		$crud->change_field_type('last_login', 'readonly');
		$crud->change_field_type('password', 'password');
		$crud->change_field_type('password_confirm', 'password');
		$crud->change_field_type('old_password', 'password');
		$crud->change_field_type('new_password', 'password');

		//CALLBACKS
		$crud->callback_insert(array($this, 'create_user_callback'));
		$crud->callback_update(array($this, 'edit_user_callback'));
		$crud->callback_field('last_login',array($this,'last_login_callback'));
		$crud->callback_column('active',array($this,'active_callback'));
        //$crud->callback_after_insert(array($this, 'users_after_insert'));

		//VIEW
		$output = $crud->render();
        
        //COMBO LOCALIDADES
        $datos = array(
            //GET THE STATE OF THE CURRENT PAGE - E.G LIST | ADD
            'estado' =>  $crud->getState(),
            //SETUP YOUR DROPDOWNS
            //Parent field item always listed first in array, in this case countryID
            //Child field items need to follow in order, e.g idprovincia then idlocalidad
            'combos' => array('id_provincias','id_localidades'),
            //SETUP URL POST FOR EACH CHILD
            //List in order as per above
            'url' => array('', site_url().'modulos_principales/buscarlocalidades/'),
            //LOADER THAT GETS DISPLAYED NEXT TO THE PARENT DROPDOWN WHILE THE CHILD LOADS
            'icon_ajax' => base_url().'ajax-loader.gif'
        );
        $output->combo_localidades = $datos;
        
		$data['judul'] = 'Usuarios';
		$data['crumb'] = array( 'Usuarios' => '' );
        $data['script_datatables'] = $this->script_datatables;
        $data['extra'] = '';

		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
    }
    
    function cbklocalidades(){

        //creamos el combo
        $combo = '<select name="id_localidades" class="chosen-select" data-placeholder="Seleccionar localidad" style="width: 300px; display: none;">';

        $fincombo = '</select>';

        $id_usuario = $this->uri->segment(4);

        $crud = new Grocery_CRUD();
        $estado = $crud->getState();

        if(isset($id_usuario) && $estado == "edit") {
            //consultamos la provincia y la localidad actual del inmueble
            $this->db->select('id_provincias, id_localidades')
                ->from('users')
                ->where('id', $id_usuario);


            $db = $this->db->get();
            $row = $db->row(0);

            $id_provincia = $row->id_provincias;
            $id_localidad = $row->id_localidades;
            
            //Cargamos el combo con todas las localidades de la pronvincia

            $this->db->select('*')
                ->from('localidades')
                ->where('id_provincias', $id_provincia);

            $db = $this->db->get();
            
            //Si ecnontramos el id de localidad actual lo ponemos como selecionado
            //sino seguimos cargando las demas localidades
            foreach($db->result() as $row):
                if($row->id_localidades == $id_localidad) {
                    $combo .= '<option value="'.$row->id_localidades.'" selected="selected">'.$row->loc_nombre.'</option>';
                } else {
                    $combo .= '<option value="'.$row->id_localidades.'">'.$row->loc_nombre.'</option>';
                }
            endforeach;
            
            //Devolvemos el combo cargado

            return $combo.$fincombo;
        } else {
            return $combo.$fincombo;
        }
    }

	public function groups() {
		$crud = new Grocery_CRUD();

		$crud->set_table('groups');
		$crud->set_subject('Grupos');
        $crud->columns('name', 'description')
        ->display_as('name','Nombre')
        ->display_as('description','Descripcion');
            

		//VIEW
		$output = $crud->render();
		$data['judul'] = 'Grupos';
		$data['crumb'] = array( 'Grupos' => '' );
        $data['script_datatables'] = $this->script_datatables;
        $data['extra'] = '';
            
		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
	}

	function active_callback($value, $row)
	{
		if ($value == 1) {
			$val = 'Activo';
		}else{
			$val = 'Inactivo';
		}
		return "<a href='".site_url('crud/activate/'.$row->id.'/'.$value)."'>$val</a>";
	}

	function activate($id, $value)
	{
		if ($value == 1) {
			$this->ion_auth->deactivate($id);
		}else{
			$this->ion_auth->activate($id);
		}

		redirect('crud/users');
	}

	function last_login_callback($value = '', $primary_key = null)
	{
		$value = date('l Y/m/d H:i', $value);
	    return $value;
	}

	function delete_user($primary_key) {
		if ($this->ion_auth_model->delete_user($primary_key)) {
			return true;
		} else {
			return false;
		}
	}

	function edit_user_callback($post_array, $primary_key) {
        

		$identity = $post_array[$this->config->item('identity', 'ion_auth')];
		$groups   = $post_array['groups'];
		$old 	  = $post_array['old_password'];
		$new 	  = $post_array['new_password'];
		$data     = array(
					'username'   => $post_array['username'],
					'email'      => $post_array['email'],
					'telefono'      => $post_array['telefono'],
					'nombre' => $post_array['nombre'],
					'apellido'  => $post_array['apellido'],
                    'id_provincias' => $post_array['id_provincias'],
                    'id_localidades' => $post_array['id_localidades'],
                    'id_empresas' => $post_array['id_empresas']
				);
        error_log($old);
		if ($old != '') {
            if ($old == 'reset'){
                $change = $this->ion_auth->reset_password($identity, $new); 
                error_log('Valor '.$change);
            }else{
                $change = $this->ion_auth->update($primary_key, $data) && $this->ion_auth->change_password($identity, $old, $new) && $this->ion_auth->remove_from_group('', $primary_key) && $this->ion_auth->add_to_group($groups, $primary_key);    
            } 
        }else{
			$change = $this->ion_auth->update($primary_key, $data) && $this->ion_auth->remove_from_group('', $primary_key) && $this->ion_auth->add_to_group($groups, $primary_key);
		};

		if ($change) {
			return true;
		}else{
			return false;
		}
	}

	function create_user_callback($post_array, $primary_key = null) {		

        
		$username = $post_array['username'];
		$password = $post_array['password'];
		$email    = $post_array['email'];
		$group 	  = $post_array['groups'];
		$data     = array(
					'telefono'      => $post_array['telefono'],
					'nombre' => $post_array['nombre'],
					'apellido'  => $post_array['apellido'],
                    'id_provincias' => $post_array['id_provincias'],
                    'id_localidades' => $post_array['id_localidades']
				);

		$id = $this->ion_auth->register($username, $password, $email, $data, $group);
        
        $datos_historial = array(
            "id_users" => $id,
            "tabla" => 'users',
            "nombre_tabla" => 'Usuarios',
            "id_vinculado" => $id,
            "accion" => 'Inserción',
            "descripcion" => 'Creacion de nueva cuenta de Usuario. Id vinculado: '.$id.' (id_users)',
            "fecha" => date('Y-m-d'),
            "hora" => date('H:i:s')
        );

        $this->historial_model->cargar($datos_historial);
 
        return true;
	}

	//CRUD SETTINGS HERE
	public function settings()
	{
		$crud = new Grocery_CRUD();

		$crud->set_table('settings');
		$crud->set_subject('Configuracion');
		$crud->set_field_upload('logo','assets/img/logo');
		$crud->columns('logo','nombre','desarrollador','domicilio','skin');
		$crud->unset_add();
		$crud->unset_delete();
		$crud->unset_export();
		$crud->unset_print();

		$output = $crud->render();
		$data['judul'] = "Configuracion";
		$data['crumb'] = array( 'Configuracion' => '' );
        $data['extra'] = '';
        $data['script_datatables'] = $this->script_datatables;

		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
	}

	public function header_menu()
	{
		$crud = new Grocery_CRUD();

		$crud->set_table('header_menu');
		$crud->set_subject('Menu Principal');
		$crud->display_as('sort','Orden')
        ->display_as('header','Nombre');
        
		$crud->set_relation_n_n('Accesos', 'groups_header', 'groups', 'id_header_menu', 'id_groups', 'name');
		$crud->add_action('Menu', 'fa fa-plus-circle', '', '',array($this,'link_menu'));
		$crud->order_by('sort','ASC');
		$crud->unset_read();
        
		$output = $crud->render();
		$data['judul'] = "Menu Principal";
		$data['crumb'] = array( 'Menu Principal' => '' );
        $data['script_datatables'] = $this->script_datatables;

		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
	}

	function link_menu($primary_key, $row)
	{
	    return site_url('crud/menu').'/'.$primary_key;
	}

	public function menu($id_header_menu)
	{
		$crud = new Grocery_CRUD();

		$crud->set_table('menu');
		$crud->set_subject('Menu');
		$crud->where('level_one','0');
		$crud->where('level_two','0');
		$crud->where('id_header_menu',$id_header_menu);
		$crud->change_field_type('id_header_menu','invisible');
        
        $crud->display_as('sort','Orden')
        ->display_as('label','Nombre');

		$crud->order_by('sort','ASC');
		$crud->set_relation_n_n('Accesos', 'groups_menu', 'groups', 'id_menu', 'id_groups', 'name');
		$crud->unset_columns('level_one','level_two','icon','menu_id','id_header_menu');
		$crud->unset_read();
		$crud->unset_fields('level_one','level_two');
		$crud->add_action('Sub menu', 'fa fa-plus-circle', '', '',array($this,'link_sub_menu'));
	    $crud->callback_before_insert(array($this,'call_header_menu'));
		$crud->callback_after_delete(array($this,'menu_after_delete'));

		$output = $crud->render();
		$data['script'] = "$('#menu-menu').addClass('active');";
		$data['script_grocery'] = "$('a[href=\"#hidden\"]').replaceWith('<span style=\"color:#777\"><i class=\"fa fa-circle\"></i> Sub menu</span>')";
		$output->data = $data;
		$data['judul'] = "Menu";
		$data['crumb'] = array( 'Menu Principal' => 'crud/header_menu',
								'Menu' => ''
							  );
        $data['script_datatables'] = $this->script_datatables;
        $data['extra'] = '';
        
		$template = 'admin_template';
		$view = 'grocery';
        $this->outputview->output_admin($view, $template, $data, $output);
	}

	function call_header_menu($post_array) 
	{
		$post_array['id_header_menu'] = $this->uri->segment(3);
		return $post_array;
	}   

	function menu_after_delete($primary_key)
	{
		$this->db->where('level_one', $primary_key);
		return $this->db->delete('menu');
	}

	function link_sub_menu($primary_key, $row)
	{
		if ($row->url == "#") {
			$url = site_url('crud/sub_menu').'/'.$row->id_header_menu.'/'.$primary_key;
		}else{
			$url = "#hidden";
		}
	    return $url;
	}

	public function sub_menu($id_header_menu, $level_one)
	{
		$crud = new Grocery_CRUD();

		$crud->set_table('menu');
		$crud->set_subject('Sub Menu');
		$crud->where('level_one', $level_one);
		$crud->where('level_two','0');
		$crud->change_field_type('id_header_menu','invisible');
		$crud->change_field_type('level_one','invisible');
        
        $crud->display_as('sort','Orden')
        ->display_as('label','Nombre');
        
		$crud->order_by('sort','ASC');
		$crud->set_relation_n_n('Accesos', 'groups_menu', 'groups', 'id_menu', 'id_groups', 'name');
		$crud->unset_columns('level_one','level_two','icon','menu_id','id_header_menu');
		$crud->unset_read();
		$crud->unset_fields('level_two');
		$crud->add_action('Sub menu 2', 'fa fa-plus-circle', '', '',array($this,'link_sub_menu_2'));
	    $crud->callback_before_insert(array($this,'call_sub_menu'));
		$crud->callback_after_delete(array($this,'sub_menu_after_delete'));

		$output = $crud->render();
		$data['script'] = "$('#menu-menu').addClass('active');";
		$data['script_grocery'] = "$('a[href=\"#hidden\"]').replaceWith('<span style=\"color:#777\"><i class=\"fa fa-circle\"></i> Sub menu 2</span>')";		
		$output->data = $data;
		$data['judul'] = "Sub menu";
		$data['crumb'] = array( 
						'Menu Principal' => 'crud/header_menu',
						'Menu' => 'crud/menu/'.$id_header_menu,
						'Sub menu' => ''
					  );
        $data['script_datatables'] = $this->script_datatables;

		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
	}

	function call_sub_menu($post_array) 
	{
		$post_array['id_header_menu'] = $this->uri->segment(3);
		$post_array['level_one'] = $this->uri->segment(4);
		return $post_array;
	}  

	function sub_menu_after_delete($primary_key)
	{
		$this->db->where('level_one', $primary_key);
		return $this->db->delete('menu');
	}

	function link_sub_menu_2($primary_key, $row)
	{
		if ($row->url == "#") {
			$url = site_url('crud/sub_menu_2').'/'.$row->id_header_menu.'/'.$row->level_one.'/'.$primary_key;
		}else{
			$url = "#hidden";
		}
	    return $url;
	}

	public function sub_menu_2($id_header_menu, $level_one, $level_two)
	{
		$crud = new Grocery_CRUD();

		$crud->set_table('menu');
		$crud->set_subject('Sub Menu 2');
		$crud->where('level_one', $level_one);
		$crud->where('level_two', $level_two);
		$crud->change_field_type('id_header_menu','invisible');
		$crud->change_field_type('level_one','invisible');
		$crud->change_field_type('level_two','invisible');

		$crud->order_by('sort','ASC');
		$crud->set_relation_n_n('Akses', 'groups_menu', 'groups', 'id_menu', 'id_groups', 'name');
		$crud->unset_columns('level_one','level_two','icon','menu_id','id_header_menu');
		$crud->unset_read();
	    $crud->callback_before_insert(array($this,'call_sub_menu_2'));

		$output = $crud->render();
		$data['script'] = "$('#menu-menu').addClass('active');";
		$output->data = $data;
		$data['judul'] = "Sub menu 2";
		$data['crumb'] = array( 
						'Menu Principal' => 'crud/header_menu',
						'Menu' => 'crud/menu/'.$id_header_menu, 
						'Sub menu' => 'crud/sub_menu/'.$id_header_menu.'/'.$level_one,
						'Sub menu 2' => ''
					  );
        $data['script_datatables'] = $this->script_datatables;

		$template = 'admin_template';
		$view = 'grocery';
		$this->outputview->output_admin($view, $template, $data, $output);
	}

	function call_sub_menu_2($post_array) 
	{
		$post_array['id_header_menu'] = $this->uri->segment(3);
		$post_array['level_one'] = $this->uri->segment(4);
		$post_array['level_two'] = $this->uri->segment(5);
		return $post_array;
	} 

}