<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Web extends CI_Controller {

    function __construct(){
      parent::__construct();
      $this->load->helper('form');
      $this->load->library('session'); 
      $this->load->library('OutputView');       
      $this->load->library('ion_auth');
      $this->load->library('pagination');      		
      $this->load->library('firebase');
      $this->load->model('componentes_model');
      $this->load->model('mediciones_model');
        
      //$this->load->model('importaciones_model');	
      $this->fb = new Firebase(array(
        'app_path'   => '',
        'app_key'  => '',
      ));		
    }

    function exportar(){
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        $fileName = 'employee.xlsx';  
        $mediciones = $this->mediciones_model->get();
        log_message('ERROR', print_r($mediciones,1));
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->getStyle('A1:D1')->applyFromArray(
          array(
             'fill' => array(
                 'type' => Fill::FILL_SOLID,
                 'color' => array('rgb' => '#E5E4E2' )
             ),
             'font'  => array(
                 'bold'  =>  true
             )
          )
        );
    
        $sheet->setCellValue('B1', 'Componente');
        $sheet->setCellValue('C1', 'Med. Temperatura');
        $sheet->setCellValue('D1', 'Med. Humedad');
        $sheet->setCellValue('E1', 'Temp. Inferior');
        $sheet->setCellValue('F1', 'Temp. Superior');       
        $rows = 2;
        /*foreach ($mediciones as $val){
            $sheet->setCellValue('A' . $rows, $val['id_mediciones']);
            $sheet->setCellValue('B' . $rows, $val['id_componentes']);
            $sheet->setCellValue('C' . $rows, $val['med_temperatura']);
            $sheet->setCellValue('D' . $rows, $val['med_humedad']);
        $sheet->setCellValue('E' . $rows, $val['med_temperatura_limite_inferior']);
            $sheet->setCellValue('F' . $rows, $val['med_temperatura_limite_superior']);
            $rows++;
        }*/
        /*$writer = new Xlsx($spreadsheet);
        $writer->save("assets/uploads/".$fileName);
        header("Content-Type: application/vnd.ms-excel");
        redirect(base_url()."assets/uploads/".$fileName); */
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="webstats.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
 
// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

    }

    function index(){
        
      $data['js_nombre'] = 'administracion_camaras.js';
            
      $data['redirect'] = site_url('panel_turnero/index');
      $view             = 'importaciones/form_view.php';
      $template         = 'web_template';
      $this->outputview->output_front($view, $template, $data);
    }

    function horarios(){
        
      $subdata['componentes'] = $this->componentes_model->get(NULL,'Accionadores');
      $data['form_horario'] = $this->load->view('horarios/form_view', $subdata, true);

      $data['js_nombre'] = 'administracion_horarios.js';
      $data['redirect'] = '#';
      $view             = 'horarios/index_view.php';
      $template         = 'web_template';
      $this->outputview->output_front($view, $template, $data);
    }

    function mediciones(){
        
      $data['componentes_accionadores'] = $this->componentes_model->get(NULL,'Accionadores');
      $data['componentes_sensores'] = $this->componentes_model->get(NULL,'Sensores');

      $data['js_nombre'] = 'administracion_mediciones.js';
      $data['redirect'] = '#';
      $view             = 'mediciones/index_view.php';
      $template         = 'web_template';
      $this->outputview->output_front($view, $template, $data);
    }


    function exportaciones(){
        
      $data['componentes_sensores'] = $this->componentes_model->get(NULL,'Sensores');

      $data['js_nombre'] = 'exportaciones.js';
      $data['redirect'] = '#';
      $view             = 'exportaciones/index_view.php';
      $template         = 'web_template';
      $this->outputview->output_front($view, $template, $data);
    }

    public function identificacion(){
      if ($this->session->userdata('usuario') != null){
        redirect('panel_turnero/index'); 
      }else{
              $data['redirect'] = site_url('panel_turnero/index');
              $view             = 'tur_instituciones_usuarios/login_view';
              $template         = 'web_template';
              $this->outputview->output_front($view, $template, $data); 
      }       
    }

    public function administracion(){
        //if ($this->session->userdata('usuario') != null){
		    //	  redirect('panel_turnero/index'); 
        //}else{
            
            //print_r($camaras);

            $data['js_nombre'] = 'administracion_camaras.js';
            
            $data['redirect'] = site_url('panel_turnero/index');
            $view             = 'administracion_view';
            $template         = 'web_template';
            $this->outputview->output_front($view, $template, $data); 
		  //}       
    }    
}
