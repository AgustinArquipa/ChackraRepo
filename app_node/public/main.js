const socket = io();

socket.on('temperatura', function (data){
    console.log(data);
    let temp = $('#temperatura');

    if (data > 28){
        temp.addClass('texto_rojo');
        temp.removeClass('texto_verde');
    }else{
        temp.addClass('texto_verde');
        temp.removeClass('texto_rojo');
    }
    temp.html (`${data} °C`);

})