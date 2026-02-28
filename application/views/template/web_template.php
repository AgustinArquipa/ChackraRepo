<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Chacra Experimental</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="<?php echo base_url('assets/frontend/lib/bootstrap/css/bootstrap.min.css') ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/style.css') ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/css/icomoon-social.css') ?>">
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600,800' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="<?php echo base_url('assets/css/leaflet.css') ?>" />
		<!--[if lte IE 8]>
		    <link rel="stylesheet" href="<?php echo base_url('assets/css/leaflet.ie.css') ?>" />
		<![endif]-->
		<link rel="stylesheet" href="<?php echo base_url('assets/css/main.css') ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/css/camera.css') ?>">
        <link rel="stylesheet" href="<?php echo base_url('assets/css/font-awesome.min.css') ?>">
        <script src="<?php echo base_url('assets/js/modernizr-2.6.2-respond-1.1.0.min.js') ?>"></script>
		<link rel="stylesheet" href="<?php echo base_url('assets/frontend/lib/jquery-confirm/jquery-confirm.min.css') ?>">
		<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/dropzone.css') ?>">		
		<!-- JavaScript -->
		<script src="<?php echo base_url('assets/frontend/js/alertify.min.js') ?>"></script>
		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/alertify.min.css') ?>"/>
    </head>
    <body>
		<div class="container-fluid">
			<div class="row ">
				<div class="col-md-12">
					<!--<button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modal_configuracion">
						Configuracion
					</button>
					<a href="http://localhost:3000" class="btn btn-primary float-right">
						Ir al Panel
					</a>-->
					<div class="dropdown float-right mr-4">
						<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Opciones
						</button>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">						
							<a class="dropdown-item" href="http://localhost:3000"><strong>Panel General</strong></a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item" href="<?= base_url("/");  ?>">Componentes</a>
							<a class="dropdown-item" href="<?= base_url("web/horarios");  ?>">Horarios</a>
							<a class="dropdown-item" href="<?= base_url("web/mediciones");  ?>">Mediciones</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item" href="<?= base_url("web/exportaciones");  ?>">Exportaciones</a>
						</div>
					</div>

				</div>
			</div>
		</div>
        <header class="container" style="margin-top:20px">
            <div class="row">
				<div class="col-md-12  text-center">
					<img src="<?= base_url('assets/frontend/img/logo_chacra.png') ?>" style="width:300px">    
				</div>
            </div>
        </header>
        <?= $page; ?>
		<div class="modal fade" id="modal_gral" tabindex="-1" role="dialog" aria-labelledby="modal_gral" aria-hidden="true" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Cargando...</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Cargando ...
					</div>
					
				</div>
			</div>
		</div>
        <div class="footer container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="footer-copyright">2019 - Un desarrollo de HandTec - ReproiSa</div>
				</div>
			</div> 
	    </div>
        <script src="<?php echo base_url('assets/js/jquery-1.10.1.js') ?>"></script>
        <script>window.jQuery || document.write('<script src="js/jquery-1.9.1.min.js"><\/script>')</script>
		<script src="<?php echo base_url('assets/frontend/lib/bootstrap/js/popper.min.js') ?>"></script>
        <script src="<?php echo base_url('assets/frontend/lib/bootstrap/js/bootstrap.min.js') ?>"></script>
		
        <script src="<?php echo base_url('assets/frontend/js/leaflet.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/jquery.fitvids.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/jquery.sequence-min.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/jquery.bxslider.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/main-menu.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/template.js') ?>"></script>        
        <script src="<?php echo base_url('assets/js/jquery.mobile.customized.min.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/jquery.easing.1.3.js') ?>"></script>
        <script src="<?php echo base_url('assets/js/camera.js') ?>"></script>    
        <script src="<?php echo base_url('assets/js/bootbox.min.js') ?>"></script>		
		<script src="<?php echo base_url('assets/frontend/js/dropzone.js') ?>"></script>        
		<script>
			var base_url = '<?php echo base_url() ?>';
		</script>
		<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/jquery-ui.css') ?>">
		<script src="<?php echo base_url('assets/frontend/js/jquery-ui.js') ?>"></script>		
		<script src="<?php echo base_url('assets/frontend/lib/jquery-confirm/jquery-confirm.min.js') ?>"></script>		
		<script src="<?php echo base_url('assets/frontend/js/language.js') ?>"></script>		
		<script src="<?php echo base_url('assets/js/jquery.validate.js') ?>"></script>		
		<script src="<?php echo base_url('assets/frontend/js/scripts.js') ?>"></script>

		<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

		<script type="text/javascript" src="https://raw.githubusercontent.com/moment/moment/develop/locale/es.js"></script>

		<?php
			if (isset($js_nombre)){
		?>
				<script src="<?php echo base_url('assets/frontend/js/'.$js_nombre) ?>"></script>
		<?php
			}
		?>

		<script type="text/javascript">
			$(document).ready(function(){
				$('#importacion').submit(function(e){
					e.preventDefault(); 
					if($("#file").val() != ''){
						$.ajax({
							url:'<?php echo base_url('ajax/importar');?>',
							type:"post",
							data:new FormData(this),
							processData:false,
							contentType:false,
							cache:false,
							async:false,
							success: function(data){
								alert("Archivo con Horarios importado con éxito.");
								$("#pnl_listado_horarios").html(data);
							}
						});
					}else{
						alert('Debe elegir un Archivo Excel (.xlsx) para importar.');
					}
				});
			});

			
		</script>		
		
    </body>
</html>