$(document).on("ready", function(){
	ListadoComponentes('Accionadores');
	ListadoComponentes('Sensores');
})

$(document).on("click", ".btn_editar_componente", function(e){
	e.preventDefault();
	var id = $(this).data('id');
	var tipo = $(this).data('tipo');
    FormComponente(id, tipo);
})

$(document).on("click", "#cancelar_carga", function(e){
	e.preventDefault();
	$('#pnl_form_componente').html('');
})

$(document).on("submit", "#valores_limites", function(e){
    e.preventDefault();
    e.stopPropagation();
	var data = $(this).serialize();
	var tipo = $('#com_tipos').val();
	//alert(tipo);
    $.ajax({
		url: base_url + "ajax/putComponente",
        type: "POST",
        data:{
            data: data
        },
		beforeSend: function (rta){
			$('#pnl_form_componente').html('Actualizando datos del Componente...');
		},
		success: function (rta) {
            ListadoComponentes(tipo);
            $('#pnl_form_componente').html(rta);
		}
    })  
})

function FormComponente(id, tipo){
    $.ajax({
		url: base_url + "ajax/getFormComponente",
        type: "POST",
        data:{
            id: id
        },
		beforeSend: function (rta){
			$('#pnl_form_componente').html('Cargando Formulario de Componente...');
		},
		success: function (rta) {	
			$('#pnl_form_componente').html(rta);
			if (tipo == 'accionadores'){
				$('.extra_sensores').remove();
			}else if (tipo == 'sensores'){
				$('#extra_accionadores').remove();
			}
		}
	})   
}

function ListadoComponentes(tipo){
    $.ajax({
		url: base_url + "ajax/getListadoComponentes",
		type: "POST",
		data: {
			tipo: tipo
		},
		beforeSend: function (rta){
			$('#pnl_listado_' + tipo.toLowerCase()).html('Cargando Listado de Componentes...');
		},
		success: function (rta) {	
			$('#pnl_listado_' + tipo.toLowerCase()).html(rta);
		}
	})    
}