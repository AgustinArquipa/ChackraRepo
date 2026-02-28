$(document).on("ready", function(){
    //ListadoMediciones('Accionadores','');
    //ListadoMediciones('Sensores','');
})

$(document).on("change", "select#componente_accionadores", function(e){
    var id_componente = $(this).val();
	e.preventDefault();
	ListadoMediciones('Accionadores', id_componente);
})

$(document).on("change", "select#componente_sensores", function(e){
    var id_componente = $(this).val();
	e.preventDefault();
	ListadoMediciones('Sensores', id_componente);
})

function ListadoMediciones(tipo, componente){
	if (componente != ''){
		$.ajax({
			url: base_url + "ajax/getListadoMediciones",
			type: "POST",
			data: {
				tipo: tipo,
				componente: componente
			},
			beforeSend: function (rta){
				$('#pnl_mediciones_' + tipo.toLowerCase()).html('Cargando Listado de Mediciones/Acciones...');
			},
			success: function (rta) {	
				//alert(rta);
				$('#pnl_mediciones_' + tipo.toLowerCase()).html(rta);
			}
		}) 
	}else{
		$('#pnl_mediciones_' + tipo.toLowerCase()).html('');	
	}
}