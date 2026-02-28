const socket = io();

socket.on('encendido', function (data){
    console.log(data);
    let temp = $('#encendido');

    if (data == 50){
        temp.addClass('texto_rojo');
        temp.removeClass('texto_verde');
        texto = 'LED Apagado';
    }else{
        temp.addClass('texto_verde');
        temp.removeClass('texto_rojo');
        texto = 'LED Encendido';
    }
    temp.html (texto);

})

$(document).ready(function(){
    socket.emit('entrada', 'Hola');
    alert ('ENtra');
})

