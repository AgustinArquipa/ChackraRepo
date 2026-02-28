<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pdfs extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('inspecciones_model');
        $this->load->model('imagenes_model');
        
        $this->empresa = $this->session->userdata('empresa');
    }
    
    public function index(){
        //$data['provincias'] llena el select con las provincias españolas
        $data['inspecciones'] = $this->inspecciones_model->getInspecciones();
        //cargamos la vista y pasamos el array $data['provincias'] para su uso
        $this->load->view('pdfs_view', $data);
    }

    public function generar($id_inspeccion = '') {
        // VALORES POR DEFECTO: tcpdf_config.php de libraries/config
        
        $this->load->library('Pdf');
        $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Safety Control');
        $pdf->SetTitle('Ejemplo de inspeccion con Safety Web');
        $pdf->SetSubject('Tutorial TCPDF');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        if ($id_inspeccion == ''){
            $id_inspeccion = $this->input->post('inspeccion');    
        }
        
        $inspeccion_seleccionada = $this->inspecciones_model->getDetalle($id_inspeccion);
        
        
        /****************** ENCABEZADO *****************************/
        
            if ($inspeccion_seleccionada->logo != ''){
                if (file_exists('assets/img/empresas/'.$inspeccion_seleccionada->logo)){
                    $logo = 'assets/img/empresas/'.$inspeccion_seleccionada->logo;
                } else {
                    $logo = '';
                }    
            }else{
                $logo = '';
            }
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' '.$id_inspeccion, PDF_HEADER_STRING, array(0, 0 ,0), array(0, 0, 0), $logo);
            
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        
        /*************************************************************/
                
        $pdf->setFooterData($tc = array(0, 64, 0), $lc = array(0, 64, 128));
        
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setFontSubsetting(true);

        $pdf->SetFont('helvetica', '', 8, '', true);

        $pdf->setCellHeightRatio(1);
        
        $pdf->AddPage();

        $html = '';
        $html .= "<style type=text/css>";
        $html .= "th{color: #fff; font-weight: bold; background-color: #222}";
        $html .= "td{background-color: #AAC7E3; color: #fff}";
        $html .= "h2{float:left}";
        $html .= "h3{line-height:50px; margin: 0px; padding:0}";
        $html .= "span{color: green}";
        $html .= ".encabezado_empresa{font-size:14px; line-height:20px;}";
        $html .= ".foto{height:100px;}";
        $html .= ".form-group{width:50%; float:left}";
        $html .= "span.ok{color: green}";
        $html .= "</style>";
        
        $idFormulario = $inspeccion_seleccionada->id_formularios;
        $categorias = $this->inspecciones_model->getCategorias($idFormulario);
        
        $respuestas = array();
        foreach ($categorias as $row) {
            $respuestas[$row->id_form_categorias] = $this->inspecciones_model->getRespuestasxCategoria($id_inspeccion, $row->id_form_categorias);    
        }

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        
        /************************* DATOS GRALES INSPECCION **************************/
        $datos_tipos = $this->inspecciones_model->GetDatosTipos($id_inspeccion);

        $tbl = '';
        
        $observaciones = '';
        
        if ($datos_tipos !== false){
            $tbl = '<h1>Titulo: <span class="titulo">'.$inspeccion_seleccionada->titulo.'</span></h1>';
            $tbl .= '<h2>Tipo de Inspeccion: <span> '.$inspeccion_seleccionada->ins_tip_nombre.'</span></h2>';
            
            $tbl .= '
                <table cellspacing="0" cellpadding="1" border="0">
            ';
            
            $i = 1;
            
            foreach ($datos_tipos as $row) {
                if ($row->nombre != 'Observaciones'){
                    if ($i % 2 == 0){
                        $tbl .= '                                   
                                <td><strong>'.$row->nombre.': </strong><span>'. $row->valor .'</span></td>
                            </tr>
                        ';    
                    }else{
                        $tbl .= '                                   
                            <tr>
                                <td><strong>'.$row->nombre.': </strong><span>'. $row->valor .'</span></td>
                        ';    
                    } 
                    $i++;
                }else{
                    $observaciones = $row->valor;     
                }
            }
            
            if (($i-1) % 2 != 0){
                $tbl .= '
                            <td></td>
                        </tr>
                ';    
            }
            
            $tbl .= '
                </table>
            ';
        }
        
        //echo $tbl;
        $pdf->writeHTML($tbl, true, false, false, false, '');
        
        /***********************************************************************************/
        
        $tbl_completo = '';
        $tbl_izq = '';
        $tbl_der = '';
        $tbl_fila = '';
        $tbl_filas = '';
        
        switch($inspeccion_seleccionada->id_formularios){
            case 1: //VIALES   
                $tbl_completo .= "<style type=text/css>";
                $tbl_completo .= "span.ok{color: green}";
                $tbl_completo .= "span.r{color: red}";
                $tbl_completo .= "span.n/a{color: brown}";
                $tbl_completo .= "</style>";
                
                foreach ($categorias as $row) {
                    switch($row->id_form_categorias){
                        case 1:
                        case 2:
                        case 3:
                        case 10:
                            $tbl_izq .= '
                                <table cellspacing="0" cellpadding="2" border="1">
                            ';
                            $tbl_izq .= '<tr><td colspan="2"><h3 style="margin:0; padding:0; line-height:10px">'.$row->nombre.'</h3></td></tr>';
                            if ($respuestas[$row->id_form_categorias]){
                                foreach ($respuestas[$row->id_form_categorias] as $row1) {
                                    $tbl_izq .= '<tr><td width="80%"><label>'.$row1->nombre.': </label></td><td width="20%" style="text-align: center"><span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span></td></tr>';
                                }
                            }
                            $tbl_izq .= '
                                </table>
                            ';
                            switch($row->id_form_categorias){
                                case 2:
                                    $tbl_izq .= '<br><br>';    
                                break;
                            }
                        break;
                        case 4:
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                            $tbl_der .= '
                                <table cellspacing="0" cellpadding="2" border="1">
                            ';
                            $tbl_der .= '<tr><td colspan="2"><h3 style="margin:0; padding:0; line-height:10px">'.$row->nombre.'</h3></td></tr>';
                            if ($respuestas[$row->id_form_categorias]){
                                foreach ($respuestas[$row->id_form_categorias] as $row1) {
                                    $tbl_der .= '<tr><td width="80%"><label>'.$row1->nombre.': </label></td><td width="20%" style="text-align: center"><span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span></td></tr>';
                                }
                            }
                            $tbl_der .= '
                                </table>
                            ';  
                            switch($row->id_form_categorias){
                                case 4:
                                case 5:
                                    $tbl_der .= '<br><br>';    
                                break;
                            }
                        break;
                    }

                }

                $tbl_completo .= '
                    <table cellspacing="0" cellpadding="1" border="0">
                        <tr>
                            <td>
                                '.$tbl_izq.'
                            </td>
                            <td>
                                '.$tbl_der.'
                            </td>
                        </tr>
                    </table>
                ';  
                $pdf->writeHTML($tbl_completo, true, false, false, false, '');
            break;
            case 2: //CONTRATISTAS
                
                $tbl_completo .= "<style type=text/css>";
                $tbl_completo .= "span.ok{color: green}";
                $tbl_completo .= "span.r{color: red}";
                $tbl_completo .= "span.n/a{color: brown}";
                $tbl_completo .= "</style>";
                
                $tbl_izq .= '
                                <table cellspacing="0" cellpadding="2" border="1" style="border-collapse: collapse;">
                            ';
                $tbl_der .= '
                                <table cellspacing="0" cellpadding="2" border="1" style="border-collapse: collapse; ">
                            ';
                foreach ($categorias as $row) {
                    switch($row->id_form_categorias){
                        case 16:
                        case 17:
                            $tbl_izq .= '<tr>
                                            <td colspan="2"><h3 style="margin:0; padding:0; line-height:10px">'.$row->nombre.'</h3></td>
                                        </tr>';
                            if ($respuestas[$row->id_form_categorias]){
                                foreach ($respuestas[$row->id_form_categorias] as $row1) {
                                    $tbl_izq .= '<tr>
                                                    <td width="80%"><label>'.$row1->nombre.': </label></td><td width="20%" style="text-align: center"><span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span></td>
                                                </tr>';
                                }
                            }
                            switch($row->id_form_categorias){
                                case 2:
                                    $tbl_izq .= '<br><br>';    
                                break;
                            }
                        break;
                        case 21:                            
                            $tbl_der .= '<tr>
                                            <td colspan="2"><h3 style="margin:0; padding:0; line-height:10px">'.$row->nombre.'</h3></td>
                                        </tr>';
                            if ($respuestas[$row->id_form_categorias]){
                                foreach ($respuestas[$row->id_form_categorias] as $row1) {
                                    $tbl_der .= '<tr>
                                                    <td width="80%"><label>'.$row1->nombre.': </label></td>
                                                    <td width="20%" style="text-align: center"><span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span></td>
                                                </tr>';
                                }
                            } 
                            switch($row->id_form_categorias){
                                case 4:
                                case 5:
                                    $tbl_der .= '<br><br>';    
                                break;
                            }
                        break;
                        case 11:
                        case 12:
                        case 13:
                        case 14:
                        case 15:
                        case 18:
                        case 19:
                        case 20:
                            $tbl_fila .= '<tr>
                                            <td colspan="2"><h3 style="margin:0; padding:0; line-height:10px">'.$row->nombre.'</h3></td>
                                        </tr>';
                            if ($respuestas[$row->id_form_categorias]){
                                foreach ($respuestas[$row->id_form_categorias] as $row1) {
                                    $tbl_fila .= '<tr>
                                                    <td width="80%"><label>'.$row1->nombre.': </label></td><td width="20%" style="text-align: center"><span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span></td>
                                                </tr>';
                                }
                            } 
                            switch($row->id_form_categorias){
                                case 15:
                                    $tbl_fila .= '
                                                </table>
                                                <p>
                                                    <table cellspacing="0" cellpadding="2" border="0" style="border-collapse: collapse; padding:0;">
                                                        <tr style="padding:0; margin:0;">
                                                            <td style="padding:0; margin:0;">[COL_IZQ]</td>
                                                            <td style="padding:0; margin:0;">[COL_DER]</td>
                                                        </tr>
                                                    </table>
                                                </p>
                                                <table cellspacing="0" cellpadding="2" border="1" style="border-collapse: collapse; padding:0">';    
                                break;
                            }
                        break;
                    }

                }  
                $tbl_izq .= '
                                </table>
                            '; 
                $tbl_der .= '
                                </table>
                            '; 
                $tbl_completo .= '
                    <table cellspacing="0" cellpadding="2" border="1" style="border-collapse: collapse; padding:0; margin:0">
                        [CONTENIDO_TABLA]
                    </table>
                '; 
                
                $tbl_completo = str_replace("[CONTENIDO_TABLA]", $tbl_fila, $tbl_completo);
                $tbl_completo = str_replace("[COL_IZQ]", $tbl_izq, $tbl_completo);
                $tbl_completo = str_replace("[COL_DER]", $tbl_der, $tbl_completo);
                //echo $tbl_completo;
                $pdf->writeHTML($tbl_completo, true, false, false, false, '');
            break;
            case 3: //MAQUINARIAS AGRICOLAS
                $tbl_completo .= "<style type=text/css>";
                $tbl_completo .= "span.bien{color: green}";
                $tbl_completo .= "span.reg.{color: #ffcc00}";
                $tbl_completo .= "span.mal{color: red}";
                $tbl_completo .= "</style>";                
                
                $i = 1;
                foreach ($categorias as $row) {
                    $tbl_filas .= '<tr><td colspan="3"><h3 style="margin:0; padding:0; text-align:center">'.$row->nombre.'</h3></td></tr>';
                    if ($respuestas[$row->id_form_categorias]){
                        foreach ($respuestas[$row->id_form_categorias] as $row1) {
                            $tbl_filas .= '
                                            <tr>
                                                <td width="5%" style="text-align: center">
                                                    '.$i.'    
                                                </td>
                                                <td width="85%">
                                                    <label>'.$row1->nombre.': </label>       
                                                </td>
                                                <td width="10%" style="text-align: center">
                                                    <span class="'.strtolower($row1->valor).'">'.$row1->valor.'</span>
                                                </td>
                                            </tr>';
                            $i++;
                        }
                    }
                }

                $tbl_completo .= '
                    <table cellspacing="0" cellpadding="2" border="1">
                        '.$tbl_filas.'
                    </table>
                ';  
                $pdf->writeHTML($tbl_completo, true, false, false, false, '');
            break;
                
        }
        
        if ($observaciones != ''){
            $tbl = '<table cellspacing="0" cellpadding="2" border="1">
                        <tr>
                            <td><strong>Observaciones:</strong></td>
                        </tr> 
                        <tr>
                            <td>'.$observaciones.'</td>
                        </tr>    
                    </table>';
            $pdf->writeHTML($tbl, true, false, false, false, '');
        }
        
        /****************** FIRMAS DE RESPONSABLES **************************/
        
            $responsables_string = $inspeccion_seleccionada->responsable;

            $find   = ';';
            $responsablesv = explode($find, $responsables_string);  
            unset($responsablesv[count($responsablesv) - 1]);
            
            $tbl = '<h4>Firmas de Responsables</h4>';
            if (count($responsablesv) > 0){        
                $tbl .= '
                    <table cellspacing="0" cellpadding="1" border="1">
                        <tr>
                ';

                $i_res = 1;
                foreach ($responsablesv as $row) {
                    $responsablev = explode(" / ", $row);  
                    $tbl .= '<td style="text-align: center">
                                <strong>'.$responsablev[1].'</strong><br>
                                <img src="assets/uploads/firmas/'.$id_inspeccion.'.'.$i_res.'.jpg" width="150" height="100"><br>
                                <label>'.$responsablev[0].'</label>
                            </td>';
                    $i_res++;
                } 

                $tbl .= '
                        </tr>
                    </table>
                ';                
            }else{
                $tbl .= '<p>Esta inspección no tiene firmas asociadas.</p>';    
            }
            $pdf->writeHTML($tbl, true, false, false, false, '');    
        
        /*************************************************************************/

        // add a page
        $pdf->AddPage();
        
        $imagenesv = $this->imagenes_model->lista($id_inspeccion);
        
    
        $html = '<h1>Fotos de la Inspeccion</h1>';
        
        //https://tcpdf.org/examples/example_009/
        
        $i_img = 1;
        if ($imagenesv !== false){
            
            $html .= '<table>
                    <tr>';
            
            foreach ($imagenesv as $row) {
                $html .= '<td align="center">';
                $html .= '<img src="assets/uploads/inspecciones/'.$row->url.'" class="foto" style="height:250px">';
                $html .= '</td>';
                if ($i_img % 2 == 0){
                    $html .= '</tr>';
                    $html .= '<tr>';
                }    
                $i_img++;
                
                //$pdf->Image('assets/uploads/inspecciones/'.$row->url, '', 35, 170, 120, 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);
            }    
            if (($i_img-1) % 2 != 0){
                $html .= '<td></td></tr>';
            }else{
                
            }
            $html .= '</table>';
            
            $html = str_replace("<tr></table>", "</table>", $html);
        }else{
            $html .= '<p>No se encontraron fotos cargadas para esta inspección.</p>';    
        }
        
        //echo $html;
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        
        
        
        $nombre_archivo = utf8_decode("Localidades de .pdf");
        $pdf->Output($nombre_archivo, 'I');
    }
}