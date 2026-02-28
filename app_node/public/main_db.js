/* VARIABLES */
    const socket = io();
    var url = window.location;
/* -------- */

/* TESTEO SOBRE EL HOSTNAME */
    if (url.hostname != 'localhost'){
        $('a.accionadores').remove();
        $('a.btn_configuracion').remove();
    }
/* -------- */

/* PARA MOSTRAR MENSAJES ALERTIFY SOBRE ESTADOS */
    socket.on('alerta', function (data){
        console.log(data);
        alertify.notify(data.mensaje, data.tipo, 5, function(){  console.log('dismissed'); });
    });
/* -------- */

/* CARGA INICIAL */
    socket.on('carga_inicial_sensores', function (data){
        data.forEach(function(room, index) {
            temperatura(room);
        });
    });

    socket.on('carga_inicial_luces', function (data){
        //console.log(data);
        data.forEach(function(room, index) {
            if (room.estado == 'Apagar'){
                apagar_fe(room.id);   
            }else{
                encender_fe(room.id);   
            }
        });
    });
/* -------- */

/*MOSTRAR CUANDO SE EJECUTARAN LAS TAREAS DE SENSADO */
    socket.on('alert_task', function(data){
        console.log('ALERT TASK');
        console.log(data.com_nombre_comando);
        $('#alert_task_' + data.com_nombre_comando).html('Enc. Prog. para las <strong>' + data.hora + ':' + data.minutos + ':' + data.segundo + '</strong>');
    })
/* -------- */

/* PEDIR ESTADOS */
    socket.on('rooms', function (data) {    
        console.log(data);
        rooms = data.rooms;
        rooms.forEach(function(item){
            console.log('#room-'+item);

            socket.on('time-'+item, function (data) {
                $('#room-'+item).html(data.time);
            });
            
            socket.on('finish-'+item, function(){

                $('#'+item).find('.foco').attr('src', 'focus.png');
                $('#btn_apagar_'+item).addClass('d-none');
                $('#btn_encender_'+item).removeClass('d-none');    
                
                $("#room-"+item).html("Tiempo Cumplido");

                setTimeout(function(){
                    $("#room-"+item).html("");
                },3000);

                /*setTimeout(function(){
                    document.getElementById('my_audio').play();
                    setTimeout(function(){
                        document.getElementById('my_audio').pause();
                        document.getElementById('my_audio').load();
                    },2000);
                },1000);*/
            });
        });    
    });
/* -------- */

/* RESETEO Y APAGADO */
    $(document).on('click', '#btn_resetear', function(e){
        e.preventDefault();
        socket.emit('resetear');
    })

    $(document).on('click', '#btn_apagar_alarma', function(e){
        e.preventDefault;
        socket.emit('apagar_alarma')
    })
/* -------- */

/* ENCENDIDO DEL PIC */
    $(document).on('click', '#btn_encender_pic', function(e){
        e.preventDefault();
        //alert('Entra');
        socket.emit('encender_pic');
    })
/* -------- */

/* LUCES: ENCENDIDOS Y APAGADOS PARA EJECUTAR EN NODE */
    $(document).on('click', '#btn_encender_L01', function(e){
        e.preventDefault();
        //alert('Entrar');
        socket.emit('encender', { 'com_nombre_comando':'L01', 'id_componentes': 1 });
    })

    $(document).on('click', '#btn_apagar_L01', function(e){
        e.preventDefault();
        socket.emit('apagar', { 'com_nombre_comando':'L01', 'id_componentes': 1 });
    })

    $(document).on('click', '#btn_encender_L02', function(e){
        e.preventDefault();
        //alert('Entrar');
        socket.emit('encender', { 'com_nombre_comando':'L02', 'id_componentes': 2 });
    })

    $(document).on('click', '#btn_apagar_L02', function(e){
        e.preventDefault();
        socket.emit('apagar', { 'com_nombre_comando':'L02', 'id_componentes': 2 });
    })

    $(document).on('click', '#btn_encender_L03', function(e){
        e.preventDefault();
        //alert('Entrar');
        socket.emit('encender', { 'com_nombre_comando':'L03', 'id_componentes': 3 });
    })

    $(document).on('click', '#btn_apagar_L03', function(e){
        e.preventDefault();
        socket.emit('apagar', { 'com_nombre_comando':'L03', 'id_componentes': 3 });
    })

    $(document).on('click', '#btn_encender_L04', function(e){
        e.preventDefault();
        //alert('Entrar');
        socket.emit('encender', { 'com_nombre_comando':'L04', 'id_componentes': 4 });
    })

    $(document).on('click', '#btn_apagar_L04', function(e){
        e.preventDefault();
        socket.emit('apagar', { 'com_nombre_comando':'L04', 'id_componentes': 4 });
    })

    $(document).on('click', '#btn_encender_L05', function(e){
        e.preventDefault();
        //alert('Entrar');
        socket.emit('encender', { 'com_nombre_comando':'L05', 'id_componentes': 5 });
    })

    $(document).on('click', '#btn_apagar_L05', function(e){
        e.preventDefault();
        socket.emit('apagar', { 'com_nombre_comando':'L05', 'id_componentes': 5 });
    })
/* -------- */

/* LUCES: ENCENDER Y APAGAR EN FRONTEND (FE) */
    socket.on('tarea_encender', function (data){
        encender_fe(data.com_nombre_comando);
    })   

    socket.on('tarea_apagar', function (data){
        apagar_fe(data.com_nombre_comando); 
    })  
/* -------- */

/* SENSADO */
    socket.on('sensando_inicio', function (){
        console.log('Inicio de Sensado'); 
        $('.accionadores').attr("disabled", "disabled");
        $('#alert_sensado').show();
    })  

    socket.on('sensando', function (){
        console.log('Sensando...'); 
        $('.accionadores').attr("disabled", "disabled");
        $('#alert_sensado').show();
    })  

    socket.on('sensando_fin', function (){
        console.log('Fin de Sensado'); 
        $('.accionadores').attr("disabled", false);
        $('#alert_sensado').hide();
    })  
/* -------- */

/* ALARMA */
    socket.on('alarma', function (){
        if (url.hostname != 'localhost'){
            Swal.fire({
                title: 'Alerta de Temperatura/Humedad',
                text: 'Uno de los senores está presentando valores de temperatura fuera de los rangos permitidos.',
                type: 'error',
                showConfirmButton: false,
                allowOutsideClick: false
            });
        }else{
            Swal.fire({
                title: 'Alerta de Temperatura/Humedad',
                text: 'Uno de los senores está presentando valores de temperatura fuera de los rangos permitidos.',
                type: 'error',
                confirmButtonText: 'Apagar Alarma',
                allowOutsideClick: false
            }).then(function(){
                socket.emit('apagar_alarma');
            })
        }
    })  

    socket.on('close_modal_alarma', function(){
        Swal.close();
    })
/* -------- */

/* FUNCIONES INTERNAS */
    function encender_fe(room){
        $('#'+room).find('.foco').attr('src', 'focus1.png');
        $('#btn_encender_'+room).addClass('d-none');
        $('#btn_apagar_'+room).removeClass('d-none');
    }

    function apagar_fe(room){
        $('#'+room).find('.foco').attr('src', 'focus.png');
        $('#btn_apagar_'+room).addClass('d-none');
        $('#btn_encender_'+room).removeClass('d-none');
    }

    function formatDecimal(data){
        if (data.length == 3){
            var entero = data.substring(0,2);
            var nro_final = entero + '.' + data[2];
            return nro_final;
        }else{
            return data;
        }
    }

    function temperatura(data){
        console.log(data);
        console.log('TEMPERATURA');
        console.log(data);
        //console.log(data);
        var texto_temp, texto_hum;

        /*if (data.componente == 'T00'){
            data.alert_temp = 'normal';
            data.alert_hum = 'normal';
        }*/
        if (data.temperatura !== null && data.temperatura !== 0){
            texto_temp = '<img src="temperatura.png" width="40" class="img-fluid"><span class="'+ data.alert_temp +'">' + data.temperatura + '℃ </span>';
        }else{
            texto_temp = '<img src="temperatura.png" width="40" class="img-fluid"><span class="'+ data.alert_temp +' alta"> Sin datos </span>';
        }
        
        if (data.humedad !== null && data.humedad !== 0){
            texto_hum = '<img src="humedad.png" width="40" class="img-fluid"><span class="' + data.alert_hum +'">' + data.humedad + "%</span>";
        }else{
            texto_hum = '<img src="humedad.png" width="40" class="img-fluid"><span class="' + data.alert_hum +' alta"> Sin datos </span>';
        }
        

        $('#temperatura-'+data.componente).html(texto_temp);
        $('#humedad-'+data.componente).html(texto_hum);
        if (typeof(data.hora) != 'undefined'){
            if (data.componente == 'T00'){
                $('#hora-0'+data.componente).html('<i class="far fa-clock"></i> ' + data.hora);    
            }
            $('#hora-'+data.componente).html('<i class="far fa-clock"></i> ' + data.hora);
        }  

        $("#"+data.componente).removeClass('igual_exterior');
        if (data.igual_exterior){
            $("#"+data.componente).addClass('igual_exterior');
            $("#aviso_exterior-"+data.componente).html('Temperatura igual a la exterior.');
        }else{
            $("#aviso_exterior-"+data.componente).html('Temperatura igual a la exterior.');
            $("#aviso_exterior-"+data.componente).html('');   
        }
        //alert(data.alert_temp);
    }
/* -------- */

alertify.defaults = {
    // dialogs defaults
    autoReset:true,
    basic:false,
    closable:true,
    closableByDimmer:true,
    invokeOnCloseOff:false,
    frameless:false,
    defaultFocusOff:false,
    maintainFocus:true, // <== global default not per instance, applies to all dialogs
    maximizable:true,
    modal:true,
    movable:true,
    moveBounded:false,
    overflow:true,
    padding: true,
    pinnable:true,
    pinned:true,
    preventBodyShift:false, // <== global default not per instance, applies to all dialogs
    resizable:true,
    startMaximized:false,
    transition:'pulse',
    transitionOff:false,
    tabbable:'button:not(:disabled):not(.ajs-reset),[href]:not(:disabled):not(.ajs-reset),input:not(:disabled):not(.ajs-reset),select:not(:disabled):not(.ajs-reset),textarea:not(:disabled):not(.ajs-reset),[tabindex]:not([tabindex^="-"]):not(:disabled):not(.ajs-reset)',  // <== global default not per instance, applies to all dialogs

    // notifier defaults
    notifier:{
    // auto-dismiss wait time (in seconds)  
        delay:5,
    // default position
        position:'top-left',
    // adds a close button to notifier messages
        closeButton: false,
    // provides the ability to rename notifier classes
        classes : {
            base: 'alertify-notifier',
            prefix:'ajs-',
            message: 'ajs-message',
            top: 'ajs-top',
            right: 'ajs-right',
            bottom: 'ajs-bottom',
            left: 'ajs-left',
            center: 'ajs-center',
            visible: 'ajs-visible',
            hidden: 'ajs-hidden',
            close: 'ajs-close'
        }
    },

    // language resources 
    glossary:{
        // dialogs default title
        title:'AlertifyJS',
        // ok button text
        ok: 'OK',
        // cancel button text
        cancel: 'Cancel'            
    },

    // theme settings
    theme:{
        // class name attached to prompt dialog input textbox.
        input:'ajs-input',
        // class name attached to ok button
        ok:'ajs-ok',
        // class name attached to cancel button 
        cancel:'ajs-cancel'
    },
    // global hooks
    hooks:{
        // invoked before initializing any dialog
        preinit:function(instance){},
        // invoked after initializing any dialog
        postinit:function(instance){},
    },
};

$(document).ready(function(){
    socket.emit('get_carga_inicial_sensores');
    socket.emit('get_carga_inicial_luces');

    socket.on('hora_servidor', function (data) {
        $('#hora_servidor').html(data.horaImprimible);
    });
    
    socket.on('temperatura', function (data) {
        temperatura(data);   
    });
})

socket.on('disconnect', function(){
    alertify.notify('Servidor NODE caído!', 'error', 25, function(){  console.log('Usuario notificado de Servidor Caído.'); });
});

