$(document).on("submit", "#exportaciones", function(e){
    e.preventDefault();
    var datos = $(this).serialize();
    $.ajax({
		url: base_url + "ajax/postexportar",
        type: "POST",
        data:{
            datos: datos
        },
		beforeSend: function (rta){
            console.log(datos);
            $(".loader").removeClass('d-none');
            $(".alert-post_loader").addClass('d-none');
			//$('#pnl_form_componente').html('Actualizando datos del Componente...');
		},
		success: function (rta) {
            //alert(rta);
            window.open(rta,'_blank' );
            $(".loader").addClass('d-none');
            $(".alert-post_loader").removeClass('d-none');

            
		}
    })  
})

$(document).on('click', '#exportar', function(e){
    e.preventDefault();
    $('#exportaciones').submit();
})

$(function() {
    $('input[name="daterange"]').daterangepicker({
      opens: 'left',
      "timePicker": true,
      "timePicker24Hour": true,
      "locale": {
              "format": "DD/MM/YYYY HH:mm",
              "separator": " - ",
              "applyLabel": "Seleccionar",
              "cancelLabel": "Cancelar",
              "fromLabel": "Desde",
              "toLabel": "Hasta",
              "customRangeLabel": "Personalizar",
              "daysOfWeek": [
                  "Do",
                  "Lu",
                  "Ma",
                  "Mi",
                  "Ju",
                  "Vi",
                  "Sa"
              ],
              "monthNames": [
                  "Enero",
                  "Febrero",
                  "Marzo",
                  "Abril",
                  "Mayo",
                  "Junio",
                  "Julio",
                  "Agosto",
                  "Setiembre",
                  "Octubre",
                  "Noviembre",
                  "Diciembre"
              ],
              "firstDay": 1
          },
          "startDate": moment().startOf('hour'),
          "endDate": moment().startOf('hour').add(1, 'hour'),
          "opens": "center"
    }, function(start, end, label) {
      console.log("A new date selection was made: " + start.format('YYYY-MM-DD hh:mm') + ' to ' + end.format('YYYY-MM-DD hh:mm'));
    });
  });