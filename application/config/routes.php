<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'web/administracion';
$route['404_override'] = 'error';
$route['translate_uri_dashes'] = FALSE;

$route['server_processing/fichas_detalles/(:num)'] = "server_processing/fichas_detalles/$1";

/*
| -------------------------------------------------------------------------
| API REST
| -------------------------------------------------------------------------
*/

/* AVISOS */
    $route['avisos/(:num)/(:num)/(:num)/(:num)']['get'] = 'avisos/index/$1/$2/$3/$4';
    $route['aviso/(:num)']['get'] = 'avisos/find/$1';
    $route['avisos/usuario/(:num)']['get'] = 'avisos/find_usuario/$1';
    $route['avisos/(:any)']['post'] = 'avisos/new/$1';
    $route['aviso/(:num)/(:any)']['post'] = 'avisos/update/$1/$2';
    $route['avisos/(:num)']['delete'] = 'avisos/index';

/* AVISOS CATEGORIAS */
    $route['avisos_categorias']['get'] = 'avisos_categorias/index';
    $route['aviso_categoria/(:num)']['get'] = 'avisos_categorias/find/$1';

/* AVISOS SUBCATEGORIAS */
    $route['avisos_subcategorias']['get'] = 'avisos_subcategorias/index';
    $route['aviso_subcategoria/(:num)']['get'] = 'avisos_subcategorias/find/$1';
    $route['avisos_subcategorias/categoria/(:num)']['get'] = 'avisos_subcategorias/find_categoria/$1';

/* DENUNCIAS */
    $route['denuncias/(:num)/(:num)']['get'] = 'denuncias/index/$1/$2';
    $route['denuncia/(:num)']['get'] = 'denuncias/find/$1';
    $route['denuncias/usuario/(:num)']['get'] = 'denuncias/find_usuario/$1';
    $route['denuncias/(:any)']['post'] = 'denuncias/new/$1';
    $route['denuncia/(:num)/(:any)']['post'] = 'denuncias/update/$1/$2';
    $route['denuncias/(:num)']['delete'] = 'denuncias/index';

/* MASCOTAS */
    $route['mascotas/(:num)/(:num)']['get'] = 'mascotas/index/$1/$2';
    $route['mascota/(:num)']['get'] = 'mascotas/find/$1';
    $route['mascotas/usuario/(:num)']['get'] = 'mascotas/find_usuario/$1';
    $route['mascotas/(:any)']['post'] = 'mascotas/new/$1';
    $route['mascota/(:num)/(:any)']['post'] = 'mascotas/update/$1/$2';
    $route['mascotas/(:num)']['delete'] = 'mascotas/index';

/* OBITUARIOS */
    $route['obituarios/(:num)/(:num)']['get'] = 'obituarios/index/$1/$2';
    $route['obituario/(:num)']['get'] = 'obituarios/find/$1';
    $route['obituarios/usuario/(:num)']['get'] = 'obituarios/find_usuario/$1';
    $route['obituarios/(:any)']['post'] = 'obituarios/new/$1';
    $route['obituario/(:num)/(:any)']['post'] = 'obituarios/update/$1/$2';
    $route['obituarios/(:num)']['delete'] = 'obituarios/index';

/* PROFESIONALES */
    $route['profesionales/(:num)/(:num)/(:num)']['get'] = 'profesionales/index/$1/$2/$3';
    $route['profesional/(:num)']['get'] = 'profesionales/find/$1';
    $route['profesionales/usuario/(:num)']['get'] = 'profesionales/find_usuario/$1';
    $route['profesionales/(:any)']['post'] = 'profesionales/new/$1';
    $route['profesional/(:num)/(:any)']['post'] = 'profesionales/update/$1/$2';
    $route['profesionales/(:num)']['delete'] = 'profesionales/index';

/* PROFESIONALES RUBROS */
    $route['profesionales_rubros']['get'] = 'profesionales_rubros/index';
    $route['profesional_rubro/(:num)']['get'] = 'profesionales_rubros/find/$1';

/* COMENTARIOS */
    $route['comentarios/(:any)/(:num)/(:num)/(:num)']['get'] = 'comentarios/index/$1/$2/$3/$4';
    $route['comentario/(:num)']['get'] = 'comentarios/find/$1';
    $route['comentarios/usuario/(:num)']['get'] = 'comentarios/find_usuario/$1';
    $route['comentarios/(:any)']['post'] = 'comentarios/new/$1';
    $route['comentario/(:num)/(:any)']['post'] = 'comentarios/update/$1/$2';
    $route['comentarios/(:num)']['delete'] = 'comentarios/index';



/* USUARIOS */
    $route['usuarios/(:num)/(:num)']['get'] = 'usuarios/index/$1/$2';
    $route['usuario/(:num)']['get'] = 'usuarios/find/$1';
    $route['usuario/facebook/(:num)']['get'] = 'usuarios/find_facebook/$1';
    $route['usuarios/(:any)']['post'] = 'usuarios/new/$1';
    $route['usuario/(:num)/(:any)']['post'] = 'usuarios/update/$1/$2';
    $route['usuarios/(:num)']['delete'] = 'usuarios/index';
    $route['usuario/facebook/(:any)']['post'] = 'usuarios/face/$1';

/* TURNERO */
    $route['turnero/reservarturno/(:any)']['post'] = 'turnero/reservarturno/$1';
    $route['turnero/turnos/(:any)']['get'] = 'turnero/turnos/$1';
    $route['turnero/turnos_filtrados/(:any)']['get'] = 'turnero/listadoprestadoresfiltrado/$1';


/* DELIVERY */
    $route['delivery/categorias']['get'] = 'delivery/categorias';

    $route['delivery/prestador/(:num)']['get'] = 'delivery/prestador/$1';
    $route['delivery/prestadores/(:num)/(:num)/(:any)']['get'] = 'delivery/prestadores/$1/$2/$3';
	$route['delivery/prestador/(:any)']['post'] = 'delivery/prestador/$1';

 	$route['delivery/pedido/(:num)']['get'] = 'delivery/pedido/$1';
	$route['delivery/pedidos/(:num)/(:num)/(:any)']['get'] = 'delivery/pedidos/$1/$2/$3';
    $route['delivery/pedido/(:any)']['post'] = 'delivery/pedido/$1';
	$route['delivery/producto/(:any)']['post'] = 'delivery/producto/$1';