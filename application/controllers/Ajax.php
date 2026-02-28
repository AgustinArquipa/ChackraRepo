<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Ajax extends Firebase {	
	function __construct(){
		parent::__construct();
		$this->load->helper('language');
		$this->load->helper('form');
		$this->lang->load('auth');
		$this->load->library('OutputView');
		$this->load->library('session');
		$this->load->library('image_moo');
		$this->load->model('componentes_model');
		$this->load->model('horarios_model');
		$this->load->model('acciones_model');
		$this->load->model('mediciones_model');
	}

	function postExportar(){
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 30000);
		
		parse_str($this->input->post("datos"), $datos);
		//log_message('ERROR', $this->input->post("datos"));

		$rangov = explode(" - ", $datos['daterange']);
		$inicio_limpio = str_replace('/', '-', $rangov[0] );
		$inicio = date("Y-m-d H:i:s", strtotime($inicio_limpio)) ;
		$fin_limpio = str_replace('/', '-', $rangov[1] );
		$fin = date("Y-m-d H:i:s", strtotime($fin_limpio)) ;

		if ($datos['componente_sensores'] == ''){
			$id_sensor = NULL;
		}else{
			$id_sensor = $datos['componente_sensores'];
		}

		//log_message('ERROR', $rangov[0] .' - '.$inicio);
		//log_message('ERROR', $rangov[1] .' - '.$fin);
		
		$sensor = $datos['componente_sensores'];

		$mediciones = $this->mediciones_model->get(NULL, $id_sensor, $inicio, $fin);

		//log_message('ERROR', print_r($mediciones, 1));
		
        $fileName = 'mediciones.xlsx';  
        $spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);

		$encabezado1 = array(
			'fill' => array(
				'type' => Fill::FILL_SOLID,
				'color' => array('rgb' => '#E5E4E2' )
			),
			'font'  => array(
				'bold'  =>  true,
				'size'	=>	20
			)
		);
		$encabezado2 = array(
			'fill' => array(
				'type' => Fill::FILL_SOLID,
				'color' => array('rgb' => '#E5E4E2' )
			),
			'font'  => array(
				'bold'  =>  true,			
			)
		);
		$alta = array(
			'font'  => array(
				'color' => array('rgb' => 'FF0000')				
			)	
		);
		$baja = array(
			'font'  => array(
				'color' => array('rgb' => '063971')				
			)	
		);
		$normal = array(
			'font'  => array(
				'color' => array('rgb' => '2d572c')				
			)	
		);

		$sheet->mergeCells('A1:H1');
		$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A1:H1')->applyFromArray($encabezado1);
		
		$sheet->setCellValue('A1', 'REPORTE DE MEDICIONES');

		$sheet->mergeCells('B2:C2');
		$sheet->mergeCells('E2:F2');
		$sheet->setCellValue('A2', 'Desde:');
		$sheet->setCellValue('D2', 'Hasta:');
		$sheet->getStyle('A2')->applyFromArray($encabezado2);
		$sheet->getStyle('D2')->applyFromArray($encabezado2);
		$sheet->setCellValue('B2', $rangov[0]);
		$sheet->setCellValue('E2', $rangov[1]);

		$nro_row_titulo = 5;
		$sheet->getStyle('A'.$nro_row_titulo.':H'.$nro_row_titulo)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A'.$nro_row_titulo.':H'.$nro_row_titulo)->applyFromArray($encabezado2);
		$sheet->setCellValue('A'.$nro_row_titulo, 'ID');
		$sheet->setCellValue('B'.$nro_row_titulo, 'Fecha');  
		$sheet->setCellValue('C'.$nro_row_titulo, 'Hora'); 
        $sheet->setCellValue('D'.$nro_row_titulo, 'Componente');
        $sheet->setCellValue('E'.$nro_row_titulo, 'Med. Temperatura');
        $sheet->setCellValue('F'.$nro_row_titulo, 'Med. Humedad');
        $sheet->setCellValue('G'.$nro_row_titulo, 'Temp. Limites');
		$sheet->setCellValue('H'.$nro_row_titulo, 'Hum. Limites'); 		
			 
		$sheet->mergeCells('A3:B3');
		$sheet->setCellValue('A3', 'Cant. Total de Registros: ');
		$sheet->getStyle('A3:B3')->applyFromArray($encabezado2);

		$rows = 6;
		if (is_array($mediciones) && count($mediciones) > 0){			
			$sheet->setCellValue('C3', count($mediciones));
			
			foreach ($mediciones as $val){				
				$sheet->setCellValue('A' . $rows, $val['id_mediciones']);
				$sheet->setCellValue('B' . $rows, date("d/m/Y", strtotime($val['med_fecha_alta'])));
				$sheet->setCellValue('C' . $rows, date("H:i", strtotime($val['med_fecha_alta'])));
				$sheet->setCellValue('D' . $rows, $val['com_nombre']);
				$sheet->setCellValue('E' . $rows, $val['med_temperatura']);
				$sheet->setCellValue('F' . $rows, $val['med_humedad']);
				$sheet->setCellValue('G' . $rows, '[' . $val['med_temperatura_limite_inferior'] . ' - ' . $val['med_temperatura_limite_superior'] . ']');
				$sheet->setCellValue('H' . $rows, '[' . $val['med_humedad_limite_inferior'] . ' - ' . $val['med_humedad_limite_superior'] . ']');
				
				if ($val['med_temperatura_estado'] == 'alta'){
					$sheet->getStyle('E' . $rows)->applyFromArray($alta);	
				}else if ($val['med_temperatura_estado'] == 'baja'){
					$sheet->getStyle('E' . $rows)->applyFromArray($baja);	
				}else{
					$sheet->getStyle('E' . $rows)->applyFromArray($normal);	
				}

				if ($val['med_humedad_estado'] == 'alta'){
					$sheet->getStyle('F' . $rows)->applyFromArray($alta);	
				}else if ($val['med_humedad_estado'] == 'baja'){
					$sheet->getStyle('F' . $rows)->applyFromArray($baja);	
				}else{
					$sheet->getStyle('F' . $rows)->applyFromArray($normal);	
				}

				$sheet->getStyle('B'.$rows)->getAlignment()->setHorizontal('center');
				$sheet->getStyle('C'.$rows)->getAlignment()->setHorizontal('center');
				$sheet->getStyle('E'.$rows)->getAlignment()->setHorizontal('center');
				$sheet->getStyle('F'.$rows)->getAlignment()->setHorizontal('center');
				$sheet->getStyle('G'.$rows)->getAlignment()->setHorizontal('center');
				$sheet->getStyle('H'.$rows)->getAlignment()->setHorizontal('center');

				$rows++;
			}
		}else{			
			$sheet->setCellValue('C3', 0);

			$sheet->mergeCells('A'. $rows.':H'. $rows);
			$sheet->getStyle('A' . $rows)->getAlignment()->setHorizontal('center');
			$sheet->setCellValue('A' . $rows, 'SIN DATOS PARA MOSTRAR');	
		}
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save("assets/uploads/".$fileName);
		echo base_url("assets/uploads/".$fileName);
	}

	function getListadoComponentes(){
		if ($this->input->is_ajax_request()){
			if ($this->input->post("tipo") !== NULL){
				$tipo = $this->input->post("tipo"); 
			}
			
			$data['componentes'] = $this->componentes_model->get(NULL, $tipo);
			echo $this->load->view('componentes/listado_'.$tipo.'_view', $data, true);
		}
	}

	function getListadoHorarios(){
		if ($this->input->is_ajax_request()){
			if ($this->input->post("componente") !== NULL){
				$id_componente = $this->input->post("componente"); 
			}

			$data['componente'] = $this->componentes_model->get($id_componente);
						
			$data['horarios'] = $this->horarios_model->get(NULL, NULL, $id_componente);
			echo $this->load->view('horarios/listado_view', $data, true);
		}
	}

	function getListadoMediciones(){
		if ($this->input->is_ajax_request()){
			if ($this->input->post("componente") !== NULL){
				$id_componente = $this->input->post("componente"); 
			}
			$data['componente'] = $this->componentes_model->get($id_componente);
			$data['componente_accionadores'] = $this->componentes_model->get(NULL, 'Accionadores');
			$data['componente_sensores'] = $this->componentes_model->get(NULL, 'Sensores');
			$listado = '';

			if ($this->input->post("tipo") == 'Accionadores'){
				$data['acciones'] = $this->acciones_model->get(NULL,$id_componente);	
				$listado = $this->load->view('acciones/listado_view', $data, true);	
			}else if($this->input->post("tipo") == 'Sensores'){
				$data['mediciones'] = $this->mediciones_model->get(NULL, $id_componente);	
				$listado = $this->load->view('mediciones/listado_view', $data, true);
			}
			
			echo  $listado;
		}
	}

	function getFormComponente(){
		if ($this->input->is_ajax_request()){ 
			$id_componente = $this->input->post("id");
			$data['componente'] = $this->componentes_model->get($id_componente);
			echo $this->load->view('componentes/form_view', $data, true);
		}
	}

	function putComponente(){
		if ($this->input->is_ajax_request()){ 
			parse_str ($this->input->post("data"), $datos);
			if ($datos['id_componentes'] != ''){
				$rta_update = $this->componentes_model->update($datos['id_componentes'], $datos);

				if ($rta_update == 0){
					$rta = '<div class="alert alert-success text-center" role="alert">Datos de Componente Actualizados correctamente.</div>';
				}else{
					$rta = '<div class="alert alert-warning text-center" role="alert">No se actualizaron los Datos del Componente.</div>';
				}

				echo $rta;
			}
		}
	}

	function importar(){
		$config['upload_path']="assets/uploads/importaciones";
		$config['allowed_types']='xlsx|txt';
		$config['encrypt_name'] = TRUE;
		 
		$this->load->library('upload',$config);
		if($this->upload->do_upload("file")){
			$data = array('upload_data' => $this->upload->data());
  
			$title= $this->input->post('title');
			$id_componente= $this->input->post('componente');
			$filename = $data['upload_data']['file_name']; 
			 
			//$result= $this->upload_model->save_upload($title,$image);
  
			$this->load->library('Excel');
			$file = APPPATH.'../assets/uploads/importaciones/'.$filename;
			$obj = PHPExcel_IOFactory::load($file);
			$cells = $obj->getActiveSheet()->getCellCollection();
			$header = array();
			$datos = array();
			foreach ($cells as $cell){
				$column = $obj->getActiveSheet()->getCell($cell)->getColumn();
				$row = $obj->getActiveSheet()->getCell($cell)->getRow();
				$cellObj = $obj->getActiveSheet()->getCell($cell);
				$data_value = $cellObj->getValue();

				// Si la celda contiene un valor numerico que representa una hora de Excel,
				// convertirlo a formato legible (PHPExcel almacena horas como fraccion del dia)
				if ($row > 1 && is_numeric($data_value) && $data_value > 0 && $data_value < 1) {
					$data_value = date("H:i:s", PHPExcel_Shared_Date::ExcelToPHP($data_value));
				}

				if ($row == 1){
					$header[$row][$column] = $data_value;
					$indices[$column] = $data_value;
				}else{
					$datos[$row][$indices[$column]] = $data_value;
				}
			}
			$primary_key = '';
  
			//log_message('ERROR', print_r($datos, 1));
			foreach ($datos as $row){
				if (isset($row['hor_fecha']) && $row['hor_fecha'] != ''){
					if (isset($row['hora_inicio']) && $row['hora_inicio'] != ''){
						//log_message('ERROR', print_r($row,1));
						$time = strtotime($row['hora_inicio']);
						$hora = date("H", $time);
						$minuto = date("i", $time);
						$segundo = date("s", $time);
						$nuevos_datos = array(
							"hor_fecha" => $row['hor_fecha'],
							"hor_hora" => $hora,
							"hor_minutos" => $minuto,
							"hor_segundos" => $segundo,
							"id_componentes" => $id_componente,
							"id_importaciones" => $primary_key
						);
						log_message('debug', print_r($nuevos_datos,1));
						$horario = $this->horarios_model->get(NULL, $this->fechaMySQL($row['hor_fecha']), $id_componente);

						if (is_null($horario)){
							log_message('debug', "CARGA");
							$this->horarios_model->save($nuevos_datos);
						}else{
							$this->horarios_model->update($horario[0]['id_horarios'], $nuevos_datos);
						}						
					}
				}			
			}  
			$data['componente'] = $this->componentes_model->get($id_componente);
						
			$data['horarios'] = $this->horarios_model->get(NULL, NULL, $id_componente);

			echo $this->load->view('horarios/listado_view', $data, true);  
		}		
	}
}