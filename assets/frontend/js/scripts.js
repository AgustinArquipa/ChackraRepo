function HorariosServiciosAjax(id_tur_prestador){
	//alert(id_tur_prestador);
	$.ajax({
		url: base_url + "ajax/getHorariosServiciosBE",
		type: "POST",
		data: {
			id_tur_prestador: id_tur_prestador
		},
		beforeSend: function (rta){
			$('tr').removeClass('seleccionado');
			$('#horarios-header-'+id_tur_prestador).addClass('seleccionado');
			$('#horarios-'+id_tur_prestador).html('<div class="col-md-12 text-center"><img src="'+base_url+'/assets/frontend/img/loading.gif"><br>Cargando...</div>');

		},
		success: function (rta) {	
			$('#horarios-'+id_tur_prestador).html(rta);
		}
	})
}

function cargarHorariosServicios(){
	$('.collapse').on('show.bs.collapse', function () {
		$('.collapse.show').collapse('hide');
		var $this = $(this);
		var id_tur_prestador = $this.data('id_tur_prestador');

		HorariosServiciosAjax(id_tur_prestador);
	});	
	
	$('.collapse').on('hide.bs.collapse', function () {
		$('.prestadores_listado tr').removeClass('seleccionado');
	});	
	$('[data-type="tooltip"]').tooltip();
}

function cargarPrestador(){
	var formPrestador = $('#form_prestador');
	
	formPrestador.on('submit',function(event){
		event.preventDefault();
	});

	formPrestador.validate( {
		ignore: "", //PARA VALIDAR INPUT HIDDEN
		rules: {
			pre_foto: "required",
			pre_foto_portada: "required",
			id_tur_categorias: "required",
			id_tur_subcategorias: "required",
			pre_nombre_completo: "required",
			pre_domicilio: "required"
		},
		messages: {
			pre_foto: "Debe ingresar una foto de perfil",
			pre_foto_portada: "Debe ingresar una foto de portada",
			id_tur_categorias: "Seleccione una Categoria",
			id_tur_subcategorias: "Seleccione una Subcategoria",
			pre_nombre_completo: "Ingrese Nombre Completo",
			pre_domicilio: "Ingrese Domicilio"
		},
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
			error.addClass( "help-block" );

			// Add `has-feedback` class to the parent div.form-group
			// in order to add icons to inputs
			element.parents( ".cont_input" ).addClass( "has-feedback" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}

			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !element.next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
			}
		},
		success: function ( label, element ) {
			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !$( element ).next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( $( element ) );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
		},
		unhighlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
		},
		submitHandler : function(form) {
			event.preventDefault();
			$('#pnl_tabla_domicilios').html('<div class="text-center"><img src="' + base_url + 'assets/img/loading.gif"/></div>');

			$("#btnLoading").addClass('disabled');
			$("#btnLoading").html('<img src="' + base_url + 'assets/svg/loading-spin.svg" alt=""> ');

			$.ajax({
				url      : $("#form_prestador").attr('action'),
				type     : 'POST',
				data     : {
					datos: $("#form_prestador").serialize(),
				},
				beforeSend: function(){
					$('#modal_gral').modal('toggle');
				},
				success  : function(data) {
					//alert (data);
					var rta = JSON.parse(data);
					$('#pnl_tabla_prestadores').fadeIn(1000).html(rta['listado']);
					$('.prestadores_listado tbody tr.header').last().effect("highlight", {color:"#28a745"}, 3000);
					$("#form_prestador")[0].reset();
					$('span.glyphicon-ok').removeClass( "glyphicon-ok" );
					$('span.glyphicon-remove').removeClass( "glyphicon-remove" );
				}
			});
		}
	} );	
}

function cargarServicio($id_prestador){
	var formServicio = $('#form_servicio');
	
	formServicio.on('submit',function(event){
		event.preventDefault();
	});
	
	formServicio.validate( {
		rules: {
			ser_nombre: "required",
			ser_precio: "required",
			ser_duracion: "required",
			ser_descripcion: "required"
		},
		messages: {
			ser_nombre: "Ingrese nombre del Servicio",
			ser_precio: "Ingrese un precio",
			ser_duracion: "Ingrese duracion del Servicio",
			ser_descripcion: "Ingrese Descripcion del Servicio"
		},
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
			error.addClass( "help-block" );

			// Add `has-feedback` class to the parent div.form-group
			// in order to add icons to inputs
			element.parents( ".cont_input" ).addClass( "has-feedback" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}

			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !element.next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
			}
		},
		success: function ( label, element ) {
			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !$( element ).next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( $( element ) );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
		},
		unhighlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
		},
		submitHandler : function(form) {
			event.preventDefault();

			$("#btnLoading").addClass('disabled');
			$("#btnLoading").html('<img src="' + base_url + 'assets/svg/loading-spin.svg" alt=""> ');

			$.ajax({
				url      : formServicio.attr('action'),
				type     : 'POST',
				data     : {
					datos: formServicio.serialize()
				},
				beforeSend: function(){
					$('#modal_gral').modal('toggle');
					$('#horarios-'+$id_prestador).html('<div class="col-md-12 text-center"><img src="'+base_url+'/assets/frontend/img/loading.gif"><br>Cargando...</div>');
				},
				success  : function(data) {
					var rta = JSON.parse(data);
					$('#horarios-'+$id_prestador).html(rta['listado']);
					$('.servicios_listado li:last-child').effect("highlight", {color:"#28a745"}, 3000);
					formServicio[0].reset();
					$('span.glyphicon-ok').removeClass( "glyphicon-ok" );
					$('span.glyphicon-remove').removeClass( "glyphicon-remove" );
				}
			});
		}
	} );
	
}

function cargarHorario($id_prestador){
	var formHorario = $('#form_horario');
	
	formHorario.on('submit',function(event){
		event.preventDefault();
	});
	
	formHorario.validate( {
		rules: {
			/*ser_nombre: "required",
			ser_precio: "required",
			ser_duracion: "required",
			ser_descripcion: "required"*/
		},
		messages: {
			/*ser_nombre: "Ingrese nombre del Servicio",
			ser_precio: "Ingrese un precio",
			ser_duracion: "Ingrese duracion del Servicio",
			ser_descripcion: "Ingrese Descripcion del Servicio"*/
		},
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
			error.addClass( "help-block" );

			// Add `has-feedback` class to the parent div.form-group
			// in order to add icons to inputs
			element.parents( ".cont_input" ).addClass( "has-feedback" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}

			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !element.next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
			}
		},
		success: function ( label, element ) {
			// Add the span element, if doesn't exists, and apply the icon classes to it.
			if ( !$( element ).next( "span" )[ 0 ] ) {
				$( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( $( element ) );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
		},
		unhighlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".cont_input" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
		},
		submitHandler : function(form) {
			event.preventDefault();
			
			$("#btnLoading").addClass('disabled');
			$("#btnLoading").html('<img src="' + base_url + 'assets/svg/loading-spin.svg" alt=""> ');

			$.ajax({
				url      : formHorario.attr('action'),
				type     : 'POST',
				data     : {
					datos: formHorario.serialize()
				},
				beforeSend: function(){
					$('#modal_gral').modal('toggle');
					$('#horarios-'+$id_prestador).html('<div class="col-md-12 text-center"><img src="'+base_url+'/assets/frontend/img/loading.gif"><br>Cargando...</div>');
				},
				success  : function(data) {
					var rta = JSON.parse(data);
					$('#horarios-'+$id_prestador).html(rta['listado']);
					$('.horarios_listado li:last-child').effect("highlight", {color:"#28a745"}, 3000);
					formHorario[0].reset();
					$('span.glyphicon-ok').removeClass( "glyphicon-ok" );
					$('span.glyphicon-remove').removeClass( "glyphicon-remove" );
				}
			});
		}
	} );
	
}

$(document).ready(function(){ 

	$('[data-type="tooltip"]').tooltip();
	
	$("#modal_gral").on("hidden.bs.modal", function (event) {
		var modal = $(this);
		modal.find('.modal-header').html('Cargando...');
		modal.find('.modal-body').html('Cargando información...');		
	});
	
	$('#modal_gral').on('show.bs.modal', function (event) {  
			$(this).find('.modal-content').css({
				width: '100%'
			});
			var button = $(event.relatedTarget);
			var tabla = button.data('tabla');
			var titulo = button.data('titulo');
			var id_dependiente = button.data('id');
			var accion = button.data('accion');
			
			var modal = $(this);
			modal.find('.modal-body').html('Cargando información...')
			$.ajax({
				url: base_url + "ajax/getFormBE",
				type: "POST",
				data: {
					tabla: tabla,
					id_dependiente: id_dependiente,
					accion: accion
				},
				success: function (rta) {
					modal.find('.modal-body').html(rta)
					modal.find('.modal-header').html('<h4>'+titulo+'</h4> <button type="button" class="close" data-dismiss="modal">&times;</button>');
					
					switch(tabla){
						case 'tur_prestadores':
							cargarPrestador();	
						break;
						case 'tur_servicios':
							cargarServicio(id_dependiente);	
						break;
						case 'tur_horarios':
							cargarHorario(id_dependiente);
							$("#slider-range").slider({
								range: true,
								min: 0,
								max: 1440,
								step: 15,
								values: [540, 1020],
								slide: function (e, ui) {
									var hours1 = Math.floor(ui.values[0] / 60);
									var minutes1 = ui.values[0] - (hours1 * 60);

									if (hours1.length == 1) hours1 = '0' + hours1;
									if (minutes1.length == 1) minutes1 = '0' + minutes1;
									if (minutes1 == 0) minutes1 = '00';
									/*if (hours1 >= 12) {
										if (hours1 == 12) {
											hours1 = hours1;
											minutes1 = minutes1 + " PM";
										} else {
											hours1 = hours1 - 12;
											minutes1 = minutes1 + " PM";
										}
									} else {
										hours1 = hours1;
										minutes1 = minutes1 + " AM";
									}
									if (hours1 == 0) {
										hours1 = 12;
										minutes1 = minutes1;
									}*/



									$('.slider-time').html(hours1 + ':' + minutes1);
									$('#hora1').val(hours1);
									$('#minuto1').val(minutes1);

									var hours2 = Math.floor(ui.values[1] / 60);
									var minutes2 = ui.values[1] - (hours2 * 60);

									if (hours2.length == 1) hours2 = '0' + hours2;
									if (minutes2.length == 1) minutes2 = '0' + minutes2;
									if (minutes2 == 0) minutes2 = '00';
									/*if (hours2 >= 12) {
										if (hours2 == 12) {
											hours2 = hours2;
											minutes2 = minutes2 + " PM";
										} else if (hours2 == 24) {
											hours2 = 11;
											minutes2 = "59 PM";
										} else {
											hours2 = hours2 - 12;
											minutes2 = minutes2 + " PM";
										}
									} else {
										hours2 = hours2;
										minutes2 = minutes2 + " AM";
									}*/

									$('.slider-time2').html(hours2 + ':' + minutes2);
									$('#hora2').val(hours2);
									$('#minuto2').val(minutes2);
								}
							});
						break;
					}
					
					Dropzone.autoDiscover = false;
					var filePerfil = new Array;
					var filePortada = new Array;
					var i = 0;
					var j =0;
					//var nombre_file_perfil = '';
					try {
						var dz_perfil = new Dropzone("#dz_perfil" , {
							paramName: "file",
							maxFilesize: .5, // MB
							maxFiles: 1,
							addRemoveLinks : true,
							dictDefaultMessage :
								'<div class="lds-dual-ring"></div><span class="bigger-150 bolder">',
							dictResponseError: 'Error al intentar subir!',
							init: function () {
								if (accion == 'editar'){
									$.ajax({
										type: 'GET',
										url: base_url+'ajax/getfiles/prestadores/'+id_dependiente+'/perfil',
										success: function(data){
											console.log(data);
											if (data.length >= 1){
												$.each(data, function(key,value) {
													nombre_file_perfil = value.name;
													var mockFile = { name: value.name, size: value.size };
													dz_perfil.options.addedfile.call(dz_perfil, mockFile);
													dz_perfil.files.push(mockFile);
													dz_perfil.options.thumbnail.call(dz_perfil, mockFile, base_url+"assets/uploads/turnero/prestadores/"+value.name);
													$('#pre_foto').val(value.name);
													filePerfil[i] = {"serverFileName" : value.name, "fileName" : value.name, "fileId" : i };	
													i++;
												});
											}	
										},
										complete: function(data){
											if (i == 0){
												$("#dz_perfil").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');		
											}	
										}
									});
									console.log(filePerfil);
								}else{
									$("#dz_perfil").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');
								}
							}
						});
						dz_perfil.on("success", function(file, serverFileName) {
							filePerfil[i] = {"serverFileName" : serverFileName, "fileName" : file.name, "fileId" : i };	
							i++;
							$('#pre_foto').val(serverFileName);
							$('#pre_foto').valid();
							if (accion == 'editar'){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/changeimagen/perfil',
									data: {id: id_dependiente, nombre_file: serverFileName},
									success: function(data){
										
									}
								});	
							}
							
						});
						dz_perfil.on("removedfile", function(file) {
							//console.log(file);
							var ruta = $('#dz_perfil #ruta').val();
							//alert (ruta);
							var name = file.name; 
							var rmvFile = "";
							console.log(filePerfil);
							for(f=0;f<filePerfil.length;f++){
								if (filePerfil[f] !== ''){
									if(filePerfil[f].fileName == file.name){
										rmvFile = filePerfil[f].serverFileName;
									}	
								}								
							}
							
							//console.log(rmvFile);
							if (rmvFile){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/fileRemove',
									data: {name: rmvFile, ruta: ruta},
									success: function(data){
										$('#pre_foto').val('');
										$('#pre_foto').valid();
										$("#dz_perfil").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');
									}
								});
							}
							if (accion == 'editar'){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/changeimagen/perfil',
									data: {id: id_dependiente, nombre_file: ''},
									success: function(data){
										
									}
								});	
							}
						});
						dz_perfil.on("addedfile", function (file) {
							var ruta = $('#dz_perfil #ruta').val();
							//console.log(file);
							if (this.files.length > 1) {
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/fileRemove',
									data: {name: this.files[0].name, ruta: ruta},
									sucess: function(data){
										$('#pre_foto').val('');										
									}
								});
								this.removeFile(this.files[0]);  
							}
							
							
						});
						
						var dz_portada = new Dropzone("#dz_portada" , {
							paramName: "file", // The name that will be used to transfer the file
							maxFilesize: .5, // MB
							maxFiles: 1,
							addRemoveLinks : true,
							dictDefaultMessage :
								'<div class="lds-dual-ring"></div><span class="bigger-150 bolder">',
							dictResponseError: 'Error al intentar subir!',
							init: function () {
								if (accion == 'editar'){
									$.ajax({
										type: 'GET',
										url: base_url+'ajax/getfiles/prestadores/'+id_dependiente+'/portada',
										success: function(data){
											console.log(data);
											if (data.length >= 1){
												$.each(data, function(key,value) {
													nombre_file_portada = value.name;
													var mockFile = { name: value.name, size: value.size };
													dz_portada.options.addedfile.call(dz_portada, mockFile);
													dz_portada.files.push(mockFile);
													dz_portada.options.thumbnail.call(dz_portada, mockFile, base_url+"assets/uploads/turnero/prestadores/"+value.name);
													$('#pre_foto_portada').val(value.name);
													filePortada[j] = {"serverFileName" : value.name, "fileName" : value.name, "fileId" : j };	
													j++;
												});	
											}	
										},
										complete: function(data){
											if (j == 0){
												$("#dz_portada").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');		
											}	
										}
									});	
								}else{
									$("#dz_portada").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');
								}
								
							}
						});
						dz_portada.on("success", function(file, serverFileName) {
							filePortada[j] = {"serverFileName" : serverFileName, "fileName" : file.name,"fileId" : j};	
							j++;	
							$('#pre_foto_portada').val(serverFileName);
							$('#pre_foto_portada').valid();
							if (accion == 'editar'){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/changeimagen/portada',
									data: {id: id_dependiente, nombre_file: serverFileName},
									success: function(data){
										
									}
								});	
							}
						});
						dz_portada.on("removedfile", function(file) {
							var ruta = $('#dz_portada #ruta').val();
							var name = file.name; 
							var rmvFile = "";
							for(f=0;f<filePortada.length;f++){
								if(filePortada[f].fileName == file.name){
									rmvFile = filePortada[f].serverFileName;
								}
							}
							if (rmvFile){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/fileRemove',
									data: {name: rmvFile, ruta: ruta},
									success: function(data){
										$('#pre_foto_portada').val('');
										$('#pre_foto_portada').valid();
										$("#dz_portada").find('div.dz-message').find('span').html('<i class=" fa fa-caret-right red"></i> Arrastre la/s imagen/es que desea subir o haga click acá para elegir imagen/es y subirla/s</span>\
										<span class="smaller-80 grey">(or click)</span> <br /> \
										<i class="upload-icon fa fa-cloud-upload blue fa-3x"></i>');
									}
								});
							}	
							if (accion == 'editar'){
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/changeimagen/portada',
									data: {id: id_dependiente, nombre_file: ''},
									success: function(data){
										
									}
								});	
							}
							
						 });
						dz_portada.on("addedfile", function (file) {
							var ruta = $('#dz_portada #ruta').val();
							//console.log(this.files);
							if (this.files.length > 1) {
								$.ajax({
									type: 'POST',
									url: base_url + 'ajax/fileRemove',
									data: {name: this.files[0].name, ruta: ruta},
									sucess: function(data){
										$('#pre_foto_portada').val('');
									}
								});

								this.removeFile(this.files[0]);                      
							}
						});						
					} catch(e) {
						//alert ('Error Dropzone');
					}
				}
			});
		})

    $("#provincias").on("change", function(){
        //obtenemos la id de la provincia seleccionada
        var provinciaSelected = $( "#provincias option:selected").attr("value");
        //hacemos la petición via get contra home/getAjaxPoblacion pasando la provincia
        $.get("<?php echo base_url('web/getAjaxLocalidades') ?>", {"provincia":provinciaSelected}, function(data)
        {
            //parseamos el json y recorremos
            var localidades = "<option value=''>Seleccione una Localidad</>";
            var localidad = JSON.parse(data);
            for(datos in localidad.localidades)
            {

                localidades += '<option value="'+localidad.localidades[datos].id_localidades+'">'+
                localidad.localidades[datos].loc_nombre + ' (' + localidad.localidades[datos].cantidad + ')</option>';

            }
            //populamos el desplegable poblaciones con las poblaciones obtenidas
            $('select#localidades').html(localidades);
        });

    });

    $("#localidades").on("change", function(){
        var localidadSelected = $( "#localidades option:selected").attr("value");
    });

    $('#form_contacto').on('submit', function(e){
        var formulario = $('#form_contacto').serialize();
        e.preventDefault();
        $.get("<?php echo base_url('web/getAjaxContacto') ?>", {'form': formulario}, function(data){
            console.log(data);
            if (data == 1){
                setTimeout(function(){ 
                    bootbox.alert("<strong>Consulta Enviada!</strong> <p>A la brevedad responderemos su inquietud. Muchas gracias.</p>", function() { });
                    $('#form_contacto')[0].reset();
                }, 1000);                         
            }
        });
    });
	
	cargarHorariosServicios();
}); 

$(document).on("click",".btn_cancelar_extra",function() {
	var id = $(this).data('id');
	var tabla = $(this).data('tabla');
	var titulo = $(this).data('titulo');
	var id_prestador = $(this).data('id_prestador');
	$.confirm({
		title: 'Cancelacion de ' + titulo,
		content: 'Esta seguro que desea eliminar el '+ titulo +'?',
		buttons: {
			confirm:{ 
				text: 'Si',
				action: function () {
					$.ajax({
						url: base_url + 'ajax/cancelarExtra',
						type: "POST",
						data: {
							id: id,
							tabla: tabla
						},
						success: function (data) {
							var rta = JSON.parse(data);
							$('#horarios-'+id_prestador).html(rta['listado']);
							
							//alert (rta);
							alertify.success(titulo + ' eliminado correctamente!');
							HorariosServiciosAjax(id_prestador);
						}
					});
				}				
			},
			cancel: { 
				text: 'No'
			}
		}
	});
});

$(document).on("click",".btn_cancelar",function() {
    var id_turno = $(this).data('id');
	$.confirm({
		title: 'Cancelacion de Turno',
		content: 'Esta seguro que desea cancelar el Turno?',
		buttons: {
			confirm:{ 
				text: 'Si',
				action: function () {
					$.ajax({
						url: base_url + 'ajax/cancelarTurno',
						type: "POST",
						data: {
							id_turno: id_turno
						},
						success: function (rta) {
							//alert (rta);
							alert ('Turno cancelado correctamente!');
							var id_prestador = $('#prestadores').val();
							var fecha = dp_fecha.val();
							getTurnos(id_prestador, fecha);
						}
					});
				}				
			},
			cancel: { 
				text: 'No',
				//$.alert('Canceled!');
			}/*,
			somethingElse: {
				text: 'Something else',
				btnClass: 'btn-blue',
				keys: ['enter', 'shift'],
				action: function(){
					$.alert('Something else?');
				}
			}*/
		}
	});
    
});

$(document).on("click",".btn_confirmar",function() {
    var id_turno = $(this).data('id');
	$.confirm({
		title: 'Confirmacion de Turno',
		content: 'Esta seguro que desea confirmar el Turno?',
		buttons: {
			confirm:{ 
				text: 'Confirmar',
				action: function () {
					$.ajax({
						url: base_url + 'ajax/confirmarTurno',
						type: "POST",
						data: {
							id_turno: id_turno
						},
						success: function (rta) {
							//alert (rta);
							alert ('Turno confirmado correctamente!');
							var id_prestador = $('#prestadores').val();
							var fecha = dp_fecha.val();
							getTurnos(id_prestador, fecha);
						}
					});
				}				
			},
			cancel: function () {
				//$.alert('Canceled!');
			}/*,
			somethingElse: {
				text: 'Something else',
				btnClass: 'btn-blue',
				keys: ['enter', 'shift'],
				action: function(){
					$.alert('Something else?');
				}
			}*/
		}
	});
    
});

var dp_fecha = $("#fecha").datepicker({
	dateFormat: 'yy-mm-dd',
	onSelect: function(dateText, inst) {
		dp_fecha.val($(this).val());
		var id_prestador = $('#prestadores').val();
		var fecha = dp_fecha.val();
		getTurnos(id_prestador, fecha);
	}
});

$('#prestadores').on("change", function(){
	var id_prestador = $(this).val();
	var fecha = dp_fecha.val();
	getTurnos(id_prestador, fecha);
})

function getTurnos(id_prestador, fecha){
	$.ajax({
		url: base_url + 'ajax/getTurnos',
		type: "POST",
		data: {
			id_prestador: id_prestador,
			fecha: fecha
		},
		success: function (rta) {
			$('#listado_turnos').html(rta);                       
		}
	});	
}

$(document).on('change', '.id_tur_categorias', function (){
	var tabla = $(this).data('tabla');
	var id_tur_categoria = $(this).val();

	$.ajax({
		url: base_url + "ajax/getSubcategoriasCombo",
		type: "POST",
		data: {
			id_tur_categoria: id_tur_categoria,
			tabla: tabla
		},
		success: function (rta) {
			$('.pnl_tur_subcategorias').html(rta);
		}
	});
})

$(document).on('click', '.btn-anular', function(e){
	e.preventDefault();
	var button = $(this);
	var button_content = $(this).html();
	
	button.html('...');
	button.prop('disabled','disabled');
	var id = button.data('id');
	
	//alert (id);
	var pre = document.createElement('pre');
	//custom style.
	pre.style.maxHeight = "400px";
	pre.style.margin = "0";
	pre.style.padding = "24px";
	pre.style.whiteSpace = "pre-wrap";
	pre.style.textAlign = "justify";
	pre.appendChild(document.createTextNode('Esta seguro que desea Suspender al Prestador?'));
	alertify.defaults.glossary.title = 'Atención!';
	
	alertify.confirm(pre, function(){
		$.ajax({
			url      : base_url + "ajax/putprestador",
			type     : 'POST',
			data     : {
				id_prestador: id,
				datos: 'pre_estado=No Publicado'
			},
			beforeSend: function(){				
			},
			success  : function(data) {
				//alert (data);
				var rta = JSON.parse(data);
				$('#pnl_tabla_prestadores').fadeIn(1000).html(rta['listado']);
				alertify.error('Prestador Suspendido Correctamente!');
				cargarHorariosServicios();
			}
		});
	},function(){
		button.html(button_content);
		button.prop('disabled','');		
	}).set({labels:{ok:'Aceptar', cancel: 'Cancelar'}, padding: false});	
})

$(document).on('click', '.btn-publicar', function(e){
	e.preventDefault();
	var button = $(this);
	var button_content = $(this).html();
	
	button.html('...');
	button.prop('disabled','disabled');
	var id = button.data('id');
	
	//console.log($(this));
	var id = button.data('id');
	//alert (id);
	var pre = document.createElement('pre');
	//custom style.
	pre.style.maxHeight = "400px";
	pre.style.margin = "0";
	pre.style.padding = "24px";
	pre.style.whiteSpace = "pre-wrap";
	pre.style.textAlign = "justify";
	pre.appendChild(document.createTextNode('Esta seguro que desea Publicar este Prestador?'));
	alertify.defaults.glossary.title = 'Atención!';
	
	alertify.confirm(pre, function(){
		$.ajax({
			url      : base_url + "ajax/putprestador",
			type     : 'POST',
			data     : {
				id_prestador: id,
				datos: 'pre_estado=Publicado'
			},
			beforeSend: function(){},
			success  : function(data) {
				//alert (data);
				var rta = JSON.parse(data);
				$('#pnl_tabla_prestadores').fadeIn(1000).html(rta['listado']);
				alertify.success('Prestador Publicado Correctamente!');
				cargarHorariosServicios();
			}
		});
	},function(){
		button.html(button_content);
		button.prop('disabled','');
	}).set({labels:{ok:'Aceptar', cancel: 'Cancelar'}, padding: false});	
})