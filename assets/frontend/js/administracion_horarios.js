$(document).on("ready", function(){
	ListadoHorarios();
})

$(document).on("change", "select#componente", function(e){
	e.preventDefault();
	ListadoHorarios();
})

function ListadoHorarios(){
    var componente = $('select#componente').val();

    $.ajax({
		url: base_url + "ajax/getListadoHorarios",
		type: "POST",
		data: {
			componente: componente
		},
		beforeSend: function (rta){
			$('#pnl_listado_horarios').html('Cargando Listado de Horarios...');
		},
		success: function (rta) {	
			$('#pnl_listado_horarios').html(rta);
		}
	})    
}