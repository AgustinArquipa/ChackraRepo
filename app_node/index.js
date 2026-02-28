const mysql = require('mysql');
const cron = require('node-cron');
const https = require('https');
const express = require('express');
const socketIO = require('socket.io');
const app = express();
const fs = require('fs');
const options = {
    key: fs.readFileSync('cert.key'),
    cert: fs.readFileSync('cert.pem')
  };
//const server = https.createServer(app);
const server = https.createServer(options, app).listen(3000);
const io = socketIO.listen(server);
var moment = require('moment');
io.set('origins', '*:*');




var Stopwatch = require('./models/stopwatch');
var new_rooms = [];
var rooms = [];
var watches = [];
var activar_alarma = false;

var token = 0;
var token_recibido = 0;
var ejecutando_sensado = false;

const connection = mysql.createPool({
    host    : '127.0.0.1',
    user    : 'root',
    password: '',
    database: 'chacra',
    connectionLimit: 10
});

app.use(express.static(__dirname + '/public'));
app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html',  { port: 'ALGO'});
});



/*server.listen(3000, function(){
    console.log(ahora() + ': Servidor activo en el puerto Nro.', 3000);
    setTimeout(() => {
        io.emit('alerta',{'tipo':'success','mensaje':'Servidor activo en el puerto Nro. 3000'});    
    }, 1000);    
})*/

const Serialport = require('serialport');
const Readline = Serialport.parsers.Readline;
var port;
var MYport;
var i = 0;
var now = new Date(); //Obtienes la fecha
var fecha_ahora = now.getFullYear() + '-' + (now.getMonth() +1)  + '-' + now.getDate();
//var fechas = ["2019-02-20", "2019-01-28", "2019-01-29", "2019-01-30"];

function cargaInicialSensores(rooms){
    console.log('Entra a cargaInicialSensores');
    var rta = [];
    var sql1 = 'SELECT M.* FROM mediciones M ' + 
    'LEFT JOIN componentes C ON M.id_componentes = C.id_componentes ' +
    'WHERE C.com_nombre_comando = "T00" ORDER BY M.id_mediciones DESC LIMIT 0,1'; 

    connection.query(sql1, function (err, result_t00) {
        var temp_exterior;
        if (result_t00 && result_t00.length > 0) {
            temp_exterior = result_t00[0]['med_temperatura'];
        }else{
            temp_exterior = 0;
        }      

        var sql = 'SELECT * FROM componentes WHERE com_tipos = "Sensores" ORDER BY com_nombre ASC';
        connection.query(sql, function (err, result1) {
            if (err) throw err;
            //console.log(result1);
            var i = 0;
            result1.forEach(function(room, index) {
                //console.log('COMPONENTE');
                //console.log(room);
                var sql = 'SELECT * FROM mediciones WHERE id_componentes = ? ORDER BY id_mediciones DESC LIMIT 0,1';
                //console.log(sql);
                connection.query(sql, [room.id_componentes], function (err, result2) {
                    //console.log('ENTRA EN MEDICIONES');
                    //console.log(result2);
                    if (result2.length > 0){
                        /*var alert_temp = 'normal';
                        var alert_hum = 'normal';
                        if (err) throw err;
                        if (parseFloat(result2[0]['med_temperatura']) < parseFloat(result1[0]['com_limite_inferior_temperatura'])){
                            alert_temp = 'baja';
                        }else if (parseFloat(result2[0]['med_temperatura']) > parseFloat(result1[0]['com_limite_superior_temperatura'])){
                            alert_temp = 'alta';
                        }

                        if (parseFloat(result2[0]['med_humedad']) < parseFloat(result1[0]['com_limite_inferior_humedad'])){
                            alert_hum = 'baja';
                        }else if (parseFloat(result2[0]['med_humedad']) > parseFloat(result1[0]['com_limite_superior_humedad'])){
                            alert_hum = 'alta';
                        }*/

                        var igual_exterior = false;
                        if (temp_exterior == result2[0]['med_temperatura']){
                            igual_exterior = true;    
                        }

                        var numRows = result2.length;
                        var time = moment(result2[0]['med_fecha_alta']); //new Date.parseDate( result2[0]['med_fecha_alta'], 'H:i:s' );
                        if (numRows > 0){
                            rta.push ({
                                "componente": room.com_nombre_comando, 
                                "temperatura": result2[0]['med_temperatura'], 
                                "humedad":result2[0]['med_humedad'], 
                                "alert_temp": result2[0]['med_temperatura_estado'], 
                                "alert_hum": result2[0]['med_humedad_estado'],
                                "igual_exterior": igual_exterior,
                                "hora":time.format("HH:mm:ss")
                            });
                        }else{
                            rta.push ({
                                "componente": room.com_nombre_comando, 
                                "temperatura": 0, 
                                "humedad": 0, 
                                "alert_temp": "normal", 
                                "alert_hum": "normal", 
                                "igual_exterior": igual_exterior,
                                "hora":time.format("HH:mm:ss")
                            });
                        }                    
                    }else{
                        rta.push ({
                            "componente": room.com_nombre_comando, 
                            "temperatura": null, 
                            "humedad": null, 
                            "alert_temp": null, 
                            "alert_hum": null, 
                            "igual_exterior": null
                        });   
                    }
                    if (i == result1.length - 1){
                        //console.log(rta);
                        io.emit('carga_inicial_sensores', rta);    
                    }else{
                        i++;
                    }
                });            
            });
        });       
    });   
    
}

function cargaInicialLuces(rooms){
    //setearAccionadores();
    console.log('Entra a cargaInicialLuces');
    var rta = [];
    var sql = 'SELECT * FROM componentes WHERE com_tipos = "Accionadores" ORDER BY com_nombre ASC';
    connection.query(sql, function (err, result1) {
        if (err) throw err;
        var i = 0;
        result1.forEach(function(room, index) {
            //console.log('COMPONENTE');
            //console.log(room);
            var sql = 'SELECT * FROM acciones WHERE id_componentes = ? ORDER BY id_acciones DESC LIMIT 0,1';
            connection.query(sql, [room.id_componentes], function (err, result2) {
                //console.log(result2);
                if (err) throw err;
                var numRows = result2.length;
                if (numRows > 0){
                    rta.push ({"id": room.com_nombre_comando, "estado": result2[0]['acc_tipos']});
                }else{
                    rta.push ({"id": room.com_nombre_comando, "estado": 'Apagar'});
                }
                if (i == result1.length - 1){
                    //console.log(rta);
                    io.emit('carga_inicial_luces', rta);    
                }else{
                    i++;
                }
            });
        });
    });
}

setearAccionadores();

function setearAccionadores(){
    watches.forEach(function(item) {
        item.stop();
    })

    new_rooms = [];
    rooms = [];
    watches = [];
    
    var sql = 'SELECT * FROM componentes WHERE com_tipos = "Accionadores" ORDER BY com_nombre ASC';
    connection.query(sql, function (err, result) {
        //console.log(result);
        if (err) throw err;
        var numRows = result.length;
        result.forEach(function(componente, index) {
            new_rooms.push(componente['id_componentes']);
            rooms.push(componente['com_nombre_comando']);
            watches.push (new Stopwatch(componente['com_tiempo_apagado'], componente['com_nombre_comando']));
        });
        rooms.forEach(function(item) {
        
            var index = rooms.indexOf(item);
            watches[index].on('tick:stopwatch', function(time) { 
                //console.log(time); 
                io.emit('time-'+item, { time: time,  id: item });
            });
        
            watches[index].on('reset:stopwatch-'+item, function(time) {  
                io.emit('time-'+item, { time: time });
            });
        
            watches[index].on('stop:stopwatch-'+item, function() {
                setTimeout(() => {
                    console.log('ENTRA EN PARAR'); 
                    var indice = rooms.indexOf(item);
                    io.emit('finish-'+item);
                    var data = {com_nombre_comando: rooms[indice], id_componentes: new_rooms[indice]};
                    //var data = JSON.parse('{"com_nombre_comando": "' + rooms[indice] + '", "id_componentes": "'+ new_rooms[indice] + '"}');
                    //console.log(data);
                    apagar(data);
                    watches[indice].reset();    
                }, index * 500);                
            });    
            mueveReloj();
        });
    });        
}

process.on('uncaughtException', function (err) {
    console.error(err);
    io.emit('alerta',{'tipo':'error','mensaje': 'Tipo de Error: '  + err.code});
});

function ReconectarArduino() {
    console.log('Inicio Reconexion de ARDUINO.');
    setInterval(function(){
      console.log('Reconectando ARDUINO.');
      console.log(port);
      ConectarArduino();
      //port.close();
    }, 2000);
};

ConectarArduino();

function ConectarArduino(){
    Serialport.list(function (err, ports) {
        if (ports.length > 0){
            ports.forEach(function(port) {
                console.log(port);
                if(port.vendorId == '1A86'){
                //if(port.vendorId == '2341'){
                    //console.log('Found It')
                    MYport = port.comName.toString();
                    //console.log(MYport);
                }       
            });
        
            port = new Serialport(MYport,{
                autoOpen: false,
                baudRate: 9600 //2400 
            }, false);

            function open () {
                port.open(function (err){
                    if (!err)
                       return;
            
                    console.log('Port is not open: ' + err.message);
                    setTimeout(open, 10000); // next attempt to open after 10s
                });
            }

            open();
        
            const parser = port.pipe(new Readline({ delimeter: ''}));
        
            parser.on('open', function(){
              console.log('Conexion Serial abierta');
            })
        
            port.on('close', function() { 
                console.log('ARDUINO DESCONECTADO');
                console.log(port.isOpen);
                //ReconectarArduino();  
                open();
                            
            });

        
            port.on('open', function() { 
                console.log('ARDUINO CONECTADO');
                console.log(port.isOpen);
            });

            parser.on('data', function(data){
                console.log('READ -------> ' + data);
                io.emit('alerta',{'tipo':'warning','mensaje':'Dato recibido: ' +data});
                //console.log(data.substring(0,1));
                //encender(data);
                
                  //data = "T00275156A";
                  
                  switch(data.substring(0,1)){
                      case 'T':
                          //console.log('ENTRA SENSOR');
                          var alert_temp = 'normal';
                          var alert_hum = 'normal';
                          var componente = data.substring(1,3);
                          /*var val_temp = Math.round(formatDecimal(data.substring(3,6)));
                          var val_hum = Math.round(formatDecimal(data.substring(6,9)));*/
                          var val_temp = formatDecimal(data.substring(3,6));
                          var val_hum = formatDecimal(data.substring(6,9));
                          var validador = data.substr(9,1);  
                          //console.log(validador);
                          
                          if (validador == 'A'){
                              var sql1 = 'SELECT M.* FROM mediciones M ' + 
                              'LEFT JOIN componentes C ON M.id_componentes = C.id_componentes ' +
                              'WHERE C.com_nombre_comando = "T00" ORDER BY M.id_mediciones DESC LIMIT 0,1'; 
                              connection.query(sql1, function (err, result_t00) {
                                  var temp_exterior;
                                  if (result_t00.length > 0){
                                      temp_exterior = result_t00[0]['med_temperatura'];
                                  }else{
                                      temp_exterior = 0;    
                                  }
              
                                  var sql = 'SELECT * FROM componentes WHERE com_nombre_comando = ? ORDER BY id_componentes DESC LIMIT 0,1';
                                  //console.log(sql);
                                  connection.query(sql, ['T' + componente], function (err, result_componente) {
                                        if (typeof result_componente[0] !== 'undefined') {
                                            if (parseFloat(result_componente[0]['com_calibracion_temperatura']) != 0){
                                                var val_temp_old = val_temp;
                                                val_temp = (parseFloat(val_temp) +  parseFloat(result_componente[0]['com_calibracion_temperatura'])).toFixed(1);
                                                io.emit('alerta',{'tipo':'warning','mensaje':'Calibración Temperatura realizada con éxito. Valor Recibido: ' + val_temp_old + '. Valor calibrado: ' + val_temp});   
                                            }
                                            
                                            if (parseFloat(result_componente[0]['com_calibracion_humedad']) != 0){
                                                var val_hum_old = val_hum;
                                                val_hum = (parseFloat(val_hum) +  parseFloat(result_componente[0]['com_calibracion_humedad'])).toFixed(1);
                                                io.emit('alerta',{'tipo':'warning','mensaje':'Calibración Humedad realizada con éxito. Valor Recibido: ' + val_hum_old + '. Valor calibrado: ' + val_hum});
                                            }
                                          
                                          //console.log(result_componente);
                                          if (err) throw err;
                                          if (result_componente.length > 0){
                                              //console.log (parseFloat(val_temp) + " - " + parseFloat(result_componente[0]['com_limite_inferior_temperatura']));
                                              if (parseFloat(val_temp) < parseFloat(result_componente[0]['com_limite_inferior_temperatura'])){
                                                  alert_temp = 'baja';
                                              }else if (parseFloat(val_temp) > parseFloat(result_componente[0]['com_limite_superior_temperatura'])){
                                                  alert_temp = 'alta';
                                              }
                  
                                              if (parseFloat(val_hum) < parseFloat(result_componente[0]['com_limite_inferior_humedad'])){
                                                  alert_hum = 'baja';
                                              }else if (parseFloat(val_hum) > parseFloat(result_componente[0]['com_limite_superior_humedad'])){
                                                  alert_hum = 'alta';
                                              }
                  
                                            /*try {
                                                var sql = 'INSERT INTO mediciones (id_componentes, med_temperatura, med_humedad, med_temperatura_limite_inferior, med_temperatura_limite_superior, med_humedad_limite_inferior, med_humedad_limite_superior, med_temperatura_estado, med_humedad_estado) VALUES ("'+ result_componente[0].id_componentes +'",'+ val_temp +', '+ val_hum +', ' + result_componente[0]['com_limite_inferior_temperatura'] + ', ' + result_componente[0]['com_limite_superior_temperatura'] + ', ' + result_componente[0]['com_limite_inferior_humedad'] + ', ' + result_componente[0]['com_limite_superior_humedad'] + ',"' + alert_temp + '", "' + alert_hum + '")';
                                                //console.log(sql);
                                                connection.query(sql, function (err, result) {
                                                    if (err) throw err;
                                                    console.log(ahora() + ': Medicion cargada con exito.');
                                                });
                                            }catch(error) {
                                                console.error(ahora() + ': ERROR EXCEPCION - ' + error);
                                            } */
    
                                            try {
                                                var sql = 'SELECT * FROM mediciones WHERE id_componentes = ? AND med_estado = "Iniciado" ORDER BY id_mediciones DESC LIMIT 0,1';
                                                //console.log(sql);
                                                connection.query(sql, [result_componente[0].id_componentes], function (err, result) {
                                                    if (err) throw err;
                                                    if (result && result.length > 0) {
                                                        var sql = 'UPDATE mediciones SET med_temperatura = ?, med_humedad = ?, med_temperatura_limite_inferior = ?, med_temperatura_limite_superior = ?, med_humedad_limite_inferior = ?, med_humedad_limite_superior = ?, med_temperatura_estado = ?, med_humedad_estado = ?, med_estado = "Realizado" WHERE id_mediciones = ?';
                                                        var sqlParams = [val_temp, val_hum, result_componente[0]['com_limite_inferior_temperatura'], result_componente[0]['com_limite_superior_temperatura'], result_componente[0]['com_limite_inferior_humedad'], result_componente[0]['com_limite_superior_humedad'], alert_temp, alert_hum, result[0]['id_mediciones']];
                                                        //console.log(sql);
                                                        connection.query(sql, sqlParams, function (err, result) {
                                                            if (err) throw err;
                                                            console.log(ahora() + ': Medicion actualizada con exito.');
                                                        });
                                                    } else {
                                                        console.log(ahora() + ': No se encontro medicion con estado Iniciado para actualizar.');
                                                    }
                                                });                                            
                                            }catch(error) {
                                                console.error(ahora() + ': ERROR EXCEPCION - ' + error);
                                            }
    
                  
                                            console.log("Sensor: " + result_componente[0].com_nombre_comando + " - Exterior: " + temp_exterior + " - Temp: " + val_temp);
                                            var igual_exterior = false;
                                            if (temp_exterior == val_temp){
                                                igual_exterior = true;    
                                            }
                                            
                                            io.emit('temperatura', {
                                                componente: result_componente[0].com_nombre_comando, 
                                                temperatura: val_temp, 
                                                alert_temp: alert_temp, 
                                                humedad: val_hum, 
                                                alert_hum: alert_hum, 
                                                igual_exterior: igual_exterior,
                                                hora: getDateTime()
                                            });
                
                                            if (alert_temp != 'normal' || alert_hum != 'normal'){
                                                activar_alarma = true; 
                                                console.log("ALARMA: " + activar_alarma);           
                                            }
                                          }
                                        }

                                        
                                  })    
                              });
          
                              
          
                              /*if (typeof port !== "undefined") {
                                  port.write('RECI' + data + '\n\r', (err) => {
                                      if (err){
                                      return console.log('Erro al escribir: ', err.message);
                                      }
                                  })
                              }else{
                                  console.log('No existe un PORT habilitado para responder a la recepcion de MEDICION DE TEMPERATURA (RECI).');
                              } */
                              var prox_componente = rellenarCeros(parseInt(componente) + 1, 2);
                              var sql = 'SELECT * FROM componentes WHERE com_nombre_comando = ? ORDER BY id_componentes DESC LIMIT 0,1';
                              //console.log(sql);
                              connection.query(sql, ['T' + prox_componente], function (err, result_sensores) {
                                  /*if (result_sensores.length > 0){
                                      setTimeout(() => {
                                          if (typeof port !== "undefined") {
                                              port.write("GS" + prox_componente.toString() +"\n\r", (err) => {
                                                  console.log("WRITE ------> GS" + prox_componente.toString() +"\n\r");
                                                  if (err){
                                                      return console.log('Erro al escribir: ', err.message);
                                                  }
                                                  console.log('Solicitud de TEMPERATURA ' + prox_componente + ' - GS' + prox_componente);
                                              }) 
                                          }else{
                                              console.log('No existe un PORT habilitado para solicitar temperatura PROXIMO SENSOR.');
                                          }     
                                      }, 1000);                              
                                  }else{
                                      console.log('No existe el PROXIMO SENSOR para pedir temperatura. Controlo ALARMAS.');
                                      verificar_alarma();                           
                                  }*/
                                  if (result_sensores.length == 0){
                                    console.log('No existe el PROXIMO SENSOR para pedir temperatura. Controlo ALARMAS.');
                                    verificar_alarma();                           
                                  }
                              })                    
                          }else{
                              console.log('Datos Temperatura: Datos Inválidos. No llega el VALIDADOR FINAL de la cadena (A).');
                              io.emit('alerta',{'tipo':'warning','mensaje':'Datos Temperatura: Datos Inválidos. No llega el VALIDADOR FINAL de la cadena (A). Dato Recibido: ' + data});   
                        }
                    break;
                    case 'C':
                        var segundo_caracter = data.substr(2,1);
                        var validador = data.substr(3,1);
                        var tipo_accion;
                        var sql;
                        var mensaje;
                        //if (validador == 'A'){
                            try {
                                console.log(segundo_caracter);
                                switch(segundo_caracter){
                                    case '0':
                                        sql = 'INSERT INTO acciones(acc_tipos) VALUES ("Corte Luz")';
                                        mensaje = 'Acción de "Corte de Luz" cargada con éxito.';
                                    break;
                                    case '1':
                                        sql = 'INSERT INTO acciones(acc_tipos) VALUES ("Reestablecimiento Luz")';
                                        mensaje = 'Acción de "Reestablecimiento Luz" cargada con éxito.';
                                    break;
                                }
                                console.log(sql);
                            
                                
                                //console.log(sql);
                                connection.query(sql, function (err, result) {
                                    if (err) throw err;
                                    console.log(ahora() + ': ' + mensaje);
                                    io.emit('alerta',{'tipo':'success','mensaje':ahora() + ': ' + mensaje});
                                }); 
                            }catch(error) {
                                console.error(ahora() + ': ERROR EXCEPCION - ' + error);
                            } 
                        //}else{
                            //console.log('Estado de Luz: Datos Inválidos. No llega el VALIDADOR FINAL de la cadena (A). Dato Recibido: ' + data);
                            //io.emit('alerta',{'tipo':'warning','mensaje':'Estado de Luz: Datos Inválidos. No llega el VALIDADOR FINAL de la cadena (A). Dato Recibido: ' + data});      
                        //}                        
                    break;
                    case 'K':
                        token_recibido = data.substring(1);
                    break;
                }        
            })
              
            parser.on('error', function(err){ 
                console.log('Error en Conexion Serial: ' + err)
                ReconectarArduino();   
            })

            setTimeout(function(){
                port.write("0000\n\r", (err) => {
                    console.log("WRITE ------> 0000\n\r");
                    if (err){
                        return console.log('Erro al escribir: ', err.message);
                    }
                    console.log('Conexion Serial abierta');
                    setTimeout(() => {
                        //pedirTemperaturaInicial();    
                        comprobarExistenTareas().then(function(existen_tareas){
                            pedirTemperatura(existen_tareas);
                        });
                    }, 1000);            
                })
            }, 1000);
        }
    });
}

setInterval(verificar_alarma, 120000);

function verificar_alarma(){
    if (activar_alarma){
        setTimeout(() => {
            if (typeof port !== "undefined") {
                port.write("AE\n\r", (err) => {
                    console.log('Enciendo Alarma');
                    if (err){
                        return console.log('Erro al escribir: ', err.message);
                    }
                })
            }else{
                console.log('No existe un PORT habilitado para ejecutar ALARMA ENCEDER');
            }
            io.emit('alarma');   
            var sql = 'INSERT INTO acciones (id_componentes, acc_tipos) VALUES (11, "Encender")';
            connection.query(sql, function (err, result) {
                if (err) throw err;
                console.log('1 registro guardado: Alarma Encendida.');
            });       
        }, 1000);
        activar_alarma = false; 
    }    
}

enviarToken();

setInterval(function(){
    comprobarExistenTareas().then(function(existen_tareas){
        enviarToken(existen_tareas);
    }
);
}, 600000);


function enviarToken(existen_tareas){
    if (existen_tareas){
        console.log('Existen TAREAS por ejecutar, se suspende el envio de TOKEN.');
    }else{
        if(ejecutando_sensado == false){
            console.log('Empieza func Enviar Token');  
            if (typeof port !== "undefined") {
                port.write("K" + token + "\n\r", (err) => {
                    console.log("WRITE ------> K" + token + "\n\r");
                    if (err){
                        return console.log('Erro al escribir: ', err.message);
                    }
        
                    setTimeout(() => {
                        console.log('Token: ' + token);
                        console.log('Token Recibido: ' + token_recibido)
                        if (token_recibido != token){
                            io.emit('alerta',{'tipo':'warning','mensaje':'Falta de Respuesta desde el PIC'});          
                        }else{
                            if (token == 9){
                                token = 0;
                            }else{
                                token = token + 1;
                            }
                            token_recibido = undefined;       
                        }
                    }, 5000);
                })        
            }else{        
                console.log('No existe un PORT habilitado para ejecutar tarea: enviarToken()');
                io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para ejecutar tarea: enviarToken()'});
            }  
        }else{
            io.emit('alerta',{'tipo':'warning','mensaje':'No se pudo ejecutar enviarToken() por Mediciones.'});
        }
    }
    
}

function encenderPIC(){
    console.log('Empieza func Encender PIC');  
    if (typeof port !== "undefined") {
        port.write("Z\n\r", (err) => {
            console.log("WRITE ------> Z\n\r");
            if (err){
                return console.log('Erro al escribir: ', err.message);
            }
            var sql = 'INSERT INTO acciones (acc_tipos) VALUES ("Encender PIC")';
            connection.query(sql, function (err, result) {
                if (err) throw 'ERROR DE BASE DE DATOS: ' + err;
                console.log('1 registro guardado en ACCIONES ("Encender PIC")');
            });   

            io.emit('alerta',{'tipo':'success','mensaje':'Instrucción de Encendido de PIC enviada.'});
        })        
    }else{        
        console.log('No existe un PORT habilitado para ejecutar tareas.');
        io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para ejecutar tareas.'});
    }  
}

function encender(data){
    console.log('Empieza func Encender');
    if (typeof port !== "undefined") {
        port.write(data.com_nombre_comando + "E\n\r", (err) => {
            console.log("WRITE ------> " + data.com_nombre_comando + "E\n\r");
            if (err){
                return console.log('Erro al escribir: ', err.message);
            }
            /*setTimeout(() => {
                port.write(data.com_nombre_comando + "E\n\r", (err) => {
                    console.log("WRITE ------> " + data.com_nombre_comando + "E\n\r");
                    if (err){
                        return console.log('Erro al escribir: ', err.message);
                    }
                    setTimeout(() => {
                        port.write(data.com_nombre_comando + "E\n\r", (err) => {
                            console.log("WRITE ------> " + data.com_nombre_comando + "E\n\r");
                            if (err){
                                return console.log('Erro al escribir: ', err.message);
                            }
                            setTimeout(() => {
                                port.write(data.com_nombre_comando + "E\n\r", (err) => {
                                    console.log("WRITE ------> " + data.com_nombre_comando + "E\n\r");
                                    if (err){
                                        return console.log('Erro al escribir: ', err.message);
                                    }
                                })    
                            }, 500);  
                        })                          
                    }, 500);  
                })                  
            }, 500);*/
        })
        
        var index = rooms.indexOf(data.com_nombre_comando.toString());
        //console.log(index);
        watches[index].start();

        var sql = 'INSERT INTO acciones (id_componentes, acc_tipos) VALUES (?, "Encender")';
        connection.query(sql, [data.id_componentes], function (err, result) {
            if (err) throw 'ERROR DE BASE DE DATOS: ' + err;
            console.log('1 registro guardado en ACCIONES ("Encender")');
        });   
        io.emit('alerta',{'tipo':'success','mensaje':'Ejecución de Encendido.'});
        io.emit('tarea_encender', data);
    }else{
        
        console.log('No existe un PORT habilitado para ejecutar tareas.');
        io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para ejecutar tareas.'});
    }        
}

function apagar(data){   
    console.log('Empieza func Apagar');
    //console.log(data);    
    if (typeof port !== "undefined") {
        port.write(data.com_nombre_comando + "A\n\r", (err) => {
            console.log("WRITE ------> " + data.com_nombre_comando + "A\n\r");
            //console.log(data.com_nombre_comando + "A\n\r");
            if (err){
                return console.log('Erro al escribir: ', err.message);
            }
            /*setTimeout(() => {
                port.write(data.com_nombre_comando + "A\n\r", (err) => {
                    console.log("WRITE ------> " + data.com_nombre_comando + "A\n\r");
                    //console.log(data.com_nombre_comando + "A\n\r");
                    if (err){
                        return console.log('Erro al escribir: ', err.message);
                    }
                    setTimeout(() => {
                        port.write(data.com_nombre_comando + "A\n\r", (err) => {
                            console.log("WRITE ------> " + data.com_nombre_comando + "A\n\r");
                            //console.log(data.com_nombre_comando + "A\n\r");
                            if (err){
                                return console.log('Erro al escribir: ', err.message);
                            }
                        }) 
                    }, 1000); 
                })
            }, 1000);*/
        })         
        var sql = 'INSERT INTO acciones (id_componentes, acc_tipos) VALUES (?, "Apagar")';
        //console.log(sql);
        connection.query(sql, [data.id_componentes], function (err, result) {
            if (err) throw 'ERROR DE BASE DE DATOS: ' + err;
            console.log('1 registro guardado en ACCIONES ("Apagar")');
        });   
        io.emit('alerta',{'tipo':'success','mensaje':'Ejecución de Apagado.'});
        io.emit('tarea_apagar', data);
    }else{
        io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para ejecutar tareas.'});
        console.log('No existe un PORT habilitado para ejecutar tareas.');
    }    
}

io.on('connection', function(socket){
    console.log('Usuario Conectado');

    socket.emit('rooms', {rooms: rooms});

    socket.on('resetear', setearAccionadores);

    socket.on('apagar_alarma', function (){
        if (typeof port !== "undefined") {
            port.write("AA\n\r", (err) => {  
                console.log("WRITE ------> AA\n\r");              
                if (err){
                    return console.log('Erro al escribir: ', err.message);
                }
                console.log('Se APAGA la ALERMA');
            })
            var sql = 'INSERT INTO acciones (id_componentes, acc_tipos) VALUES (11, "Apagar")';
            connection.query(sql, function (err, result) {
                if (err) throw err;
                console.log('1 registro guardado: ALARMA APAGADA.');
            });             
            io.emit('close_modal_alarma');
            io.emit('alerta',{'tipo':'success','mensaje':'Alarma Apagada.'});  
        }else{
            console.log('No existe un PORT habilitado para APAGAR ALARMA.');
        } 
    })

    socket.on('get_carga_inicial_sensores', function (data){
        cargaInicialSensores(rooms);
    })

    socket.on('get_carga_inicial_luces', function (data){
        cargaInicialLuces(rooms);
    })

    socket.on('apagar', function (data){
        console.log(data);
        var indice = rooms.indexOf(data.com_nombre_comando);
        console.log(indice);
        watches[indice].stop();

        var sql = 'SELECT * FROM acciones WHERE id_componentes = ? ORDER BY id_acciones DESC LIMIT 0,1';
        connection.query(sql, [data.id_componentes], function (err, result) {
            if (err) throw err;
            if (result.length > 0){
                if (result[0]['acc_tipos'] == "Encender"){
                    apagar(data);
                } 
            }else{
                console.log('No existen ACCIONES (Encendida) para enviar a APAGAR.');
            }                       
        }); 
    })

    socket.on('encender', function (data){
        encender(data);            
    })   

    socket.on('encender_pic', function (data){
        encenderPIC();
    });
});

function programacion_tareas(){
    console.log('[TAREAS] ====== INICIO PROGRAMACION DE TAREAS ======');
    var alertas_task = [];

    now = new Date();
    var dia = now.getDate();
    var mes = now.getMonth() +1;
    var anio = now.getFullYear();
    fecha_ahora = moment().format('YYYY-MM-DD');

    console.log('[TAREAS] Fecha de hoy (moment): ' + fecha_ahora);
    console.log('[TAREAS] Hora actual: ' + getDateTime());

    var sql = 'SELECT * FROM horarios H INNER JOIN componentes C ON H.id_componentes = C.id_componentes WHERE hor_fecha = ?';
    console.log('[TAREAS] Query: ' + sql + ' | Param: [' + fecha_ahora + ']');

    connection.query(sql, [fecha_ahora], function(error, results, fields){
        if (error) {
            console.log('[TAREAS] ERROR en query: ' + error.message);
            throw error;
        }
        console.log('[TAREAS] Resultados encontrados: ' + results.length);
        if (results.length > 0){
            var x = 0;
            var task1, task2, task3, task4, task5;
            var timer_task = 50000;

            for (x in results){
                var hora = results[x].hor_hora;
                var minutos = results[x].hor_minutos;
                var segundo = results[x].hor_segundos;
                var componente = results[x].id_componentes;
                var nombre_comando = results[x].com_nombre_comando;
                var data = { 'com_nombre_comando': nombre_comando, 'id_componentes': componente };
                var data_task = {
                    'com_nombre_comando':nombre_comando,
                    'id_componentes': componente,
                    'hora': rellenarCeros(hora,2),
                    'minutos': rellenarCeros(minutos,2),
                    'segundo': rellenarCeros(segundo,2)
                };

                var hora_completa_tarea = rellenarCeros(hora,2) + ":" + rellenarCeros(minutos,2) + ":" + rellenarCeros(segundo,2);
                console.log('[TAREAS] Tarea #' + x + ': componente=' + nombre_comando + ' hora=' + hora_completa_tarea + ' id_componentes=' + componente);
                if (crear_tarea(hora_completa_tarea)){
                    alertas_task.push(data_task);
                    console.log('[TAREAS] >>> Tarea PROGRAMADA: ' + nombre_comando + ' a las ' + hora_completa_tarea);
                    ejecutarTarea(nombre_comando, componente, hora, minutos, segundo, data, dia, mes);

                }else{
                    console.log('[TAREAS] >>> Tarea DESCARTADA (hora ya paso): ' + nombre_comando + ' ' + hora_completa_tarea + ' < ' + getDateTime());
                }

                //io.emit('alert_task', data_task);
                               
            }
        }
    }); 
    

    setTimeout(() => {
        alertas_task.forEach(function(item){
            io.emit('alert_task', item);
        })    
    }, 40000);
}

//cron.schedule('*/10 * * * * *', () => {
cron.schedule('0 10 0 * * *', () => {
    console.log('[CRON] Cron diario 00:10 disparado (iteracion ' + i + ')');
    programacion_tareas();
    i++;
});

setTimeout(() => {
    console.log('[INICIO] Verificando si se requiere programacion manual...');
    if (tareas_manuales()){
        console.log('[INICIO] Programacion MANUAL activada (hora actual > 00:10)');
        programacion_tareas();
    } else {
        console.log('[INICIO] Programacion AUTOMATICA (se espera al cron de las 00:10)');
    }
}, 10000);

//setInterval(pedirTemperaturaInicial,120000);

/*function pedirTemperaturaInicial(){
    activar_alarma = false;
    if (typeof port !== "undefined" && port.isOpen) {
        port.write("GS00\n\r", (err) => {
            console.log("WRITE ------> GS00\n\r");
            if (err){
                return console.log('Erro al escribir: ', err.message);
            }
            console.log('Solicitud de TEMPERATURA EXTERNA: GS00\n\r' );
        })
    }else{
        console.log('No existe un PORT habilitado para solicitar mediciones de Temperatura.');    
    }
}*/
async function comprobarExistenTareas(){
    return new Promise( ( resolve, reject ) => {
        var existen_tareas = false;
        var fecha_ahora = moment().format('YYYY-MM-DD');
        //console.log(fecha_ahora);
        
        var sql = 'SELECT * FROM horarios H INNER JOIN componentes C ON H.id_componentes = C.id_componentes WHERE hor_fecha = ?';
        connection.query(sql, [fecha_ahora], function(error, results, fields){
            if ( error )
                return reject( error );

            for (x in results){
                var hora = results[x].hor_hora;
                var minutos = results[x].hor_minutos;
                var segundo = results[x].hor_segundos;
                var hora_completa_tarea = rellenarCeros(hora,2) + ":" + rellenarCeros(minutos,2) + ":" + rellenarCeros(segundo,2);
                hora_completa_tarea = moment.utc(hora_completa_tarea, "HH:mm:ss");
                var hora_actual = moment().format('HH:mm:ss');
                hora_actual = moment.utc(hora_actual, "HH:mm:ss");

                console.log(hora_completa_tarea.toString() + ' - ' + hora_actual.toString());
                var diferencia = moment.duration(hora_completa_tarea - hora_actual)._milliseconds;
                console.log(diferencia);
                if (diferencia <= 40000 && diferencia > 0){
                    existen_tareas = true;
                    break;
                    //console.log(existen_tareas);
                    
                }
            } 
            resolve( existen_tareas );         
            
        }) 
    } );
}

setInterval(function(){
    comprobarExistenTareas().then(function(existen_tareas){
        pedirTemperatura(existen_tareas);
    }
);
}, 900000);

function pedirTemperatura(existen_tareas){
    if (existen_tareas){
        console.log('Existen TAREAS por ejecutar, se suspende el pedido de MEDICIONES.');
        io.emit('alerta',{'tipo':'warning','mensaje':'Existen TAREAS por ejecutar, se suspende el pedido de MEDICIONES.'});
        /*setTimeout(() => {
            comprobarExistenTareas().then(function(existen_tareas){
                pedirTemperatura(existen_tareas);
            })  
        }, 60000);  */ 
    }else{
        activar_alarma = false;
        var timeout = 0;
        var sql = 'SELECT * FROM componentes WHERE com_tipos = "Sensores" ORDER BY com_nombre_comando ASC';
        connection.query(sql, function (err, result1) {
            if (err) throw err;
            var i =  0;
            ejecutando_sensado = true;
            io.emit('alerta',{'tipo':'warning','mensaje':'Inicio de Sensado...'});
            result1.forEach(function(room, index) { 
                io.emit('sensando_inicio'); 
                var componente_nro = room.com_nombre_comando.substring(1, 3);
                timeout = timeout + 5000;
                setTimeout(() => {
                    io.emit('sensando');
                    if (typeof port !== "undefined" && port.isOpen) {
                        port.write("GS"+ componente_nro +"\n\r", (err) => {
                            console.log("WRITE ------> GS"+ componente_nro +"\n\r");
                            if (err){
                                return console.log('Erro al escribir: ', err.message);
                            }

                            //GUARDO LA SOLICITUD DE TEMPERATURA EN MEDICIONES COMO INICIADO, PARA DESPUES UNA VEZ QUE EL PIC RESPONDA, ACTUALIZAR LOS VALORES.
                            var sql = 'INSERT INTO mediciones (id_componentes, med_estado) VALUES (?, "Iniciado")';
                            connection.query(sql, [room.id_componentes], function (err, result) {
                                if (err) throw 'ERROR DE BASE DE DATOS: ' + err;
                                console.log('1 registro guardado en MEDICIONES');
                            });   

                            console.log('Solicitud de TEMPERATURA EXTERNA: GS'+ componente_nro +'\n\r' );
                            io.emit('alerta',{'tipo':'success','mensaje':'Solicitud de TEMPERATURA EXTERNA: GS'+ componente_nro +'\n\r'});
                        })
                    }else{
                        console.log('No existe un PORT habilitado para solicitar mediciones de Temperatura: GS'+ componente_nro +'\n\r');
                        io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para solicitar mediciones de Temperatura: GS'+ componente_nro +'\n\r'});    
                    }   
                    if (i == result1.length - 1){
                        //console.log(rta);
                        setTimeout(() => {
                            io.emit('sensando_fin'); 
                            io.emit('alerta',{'tipo':'warning','mensaje':'... Fin de Sensado.'});
                               
                        }, 5000);                            
                    }else{
                        i++;
                    } 
                }, timeout);
            }) 
            setTimeout(() => {
                ejecutando_sensado = false;    
            }, 5000 * result1.length);
        })  
    }
}

/////// FUNCIONES GENERICAS //////

function ahora(){    
    return moment().format('DD/MM/YY HH:mm:ss');
}


function getDateTime() {

    var date = new Date();

    var hour = date.getHours();
    hour = (hour < 10 ? "0" : "") + hour;

    var min  = date.getMinutes();
    min = (min < 10 ? "0" : "") + min;

    var sec  = date.getSeconds();
    sec = (sec < 10 ? "0" : "") + sec;

    return hour + ":" + min + ":" + sec;
}

function tareas_manuales(){
    var a = "00:10:00";
    var b = getDateTime();
    var aa1=a.split(":");
    var aa2=b.split(":");
    
    var d1=new Date(parseInt("2001",10),(parseInt("01",10))-1,parseInt("01",10),parseInt(aa1[0],10),parseInt(aa1[1],10),parseInt(aa1[2],10));
    var d2=new Date(parseInt("2001",10),(parseInt("01",10))-1,parseInt("01",10),parseInt(aa2[0],10),parseInt(aa2[1],10),parseInt(aa2[2],10));
    var dd1=d1.valueOf();
    var dd2=d2.valueOf();

    if(dd1<dd2){
        console.log("Hora actual MAYOR a hora de programacion. PROGRAMACION MANUAL");
        return true;        
    }else{
        console.log("Hora actual MENOR a hora de programacion. PROGRAMACION AUTOMATICA.");
        return false;
    }
}

function crear_tarea(hora_tarea){
    var a = getDateTime();
    var b = hora_tarea;
    var aa1=a.split(":");
    var aa2=b.split(":");
    
    var d1=new Date(parseInt("2001",10),(parseInt("01",10))-1,parseInt("01",10),parseInt(aa1[0],10),parseInt(aa1[1],10),parseInt(aa1[2],10));
    var d2=new Date(parseInt("2001",10),(parseInt("01",10))-1,parseInt("01",10),parseInt(aa2[0],10),parseInt(aa2[1],10),parseInt(aa2[2],10));
    var dd1=d1.valueOf();
    var dd2=d2.valueOf();

    if(dd1<dd2){
        console.log("[CREAR_TAREA] Hora actual (" + a + ") < Hora tarea (" + hora_tarea + ") => CREO TAREA");
        return true;
    }else{
        console.log("[CREAR_TAREA] Hora actual (" + a + ") >= Hora tarea (" + hora_tarea + ") => NO CREO TAREA (ya paso)");
        return false;
    }
}

var mueveRelojIniciado = false;
function mueveReloj(){
    if (mueveRelojIniciado) return;
    mueveRelojIniciado = true;

    function tick() {
        var momentoActual = new Date();
        var h = momentoActual.getHours();
        var m = momentoActual.getMinutes();
        var s = momentoActual.getSeconds();
        if (h <= 9) h = "0" + h;
        if (m <= 9) m = "0" + m;
        if (s <= 9) s = "0" + s;
        var horaImprimible = h + " : " + m + " : " + s;

        io.emit('hora_servidor', { horaImprimible: horaImprimible });
        setTimeout(tick, 1000);
    }
    tick();
}

function formatDecimal(data){
    var entero = data.substring(0,2);
    var nro_final = entero + '.' + data[2];
    return nro_final;
}

function rellenarCeros(number, width) {
    var numberOutput = Math.abs(number); /* Valor absoluto del número */
    var length = number.toString().length; /* Largo del número */ 
    var zero = "0"; /* String de cero */      
    if (width <= length) {
        if (number < 0) {
             return ("-" + numberOutput.toString()); 
        }else{
             return numberOutput.toString(); 
        }
    }else{
        if (number < 0) {
            return ("-" + (zero.repeat(width - length)) + numberOutput.toString()); 
        }else{
            return ((zero.repeat(width - length)) + numberOutput.toString()); 
        }
    }
} 

function ejecutarTarea(nombre_comando, componente, hora, minuto, segundo, data, dia, mes){
    var cron_string = segundo + ' ' + minuto + ' ' + hora + ' ' + dia + ' ' + mes + ' *';
    console.log('[EJECUTAR] Registrando cron: "' + cron_string + '" para componente ' + nombre_comando + ' (id=' + componente + ')');
    console.log('[EJECUTAR] Se ejecutara a las ' + rellenarCeros(hora,2) + ':' + rellenarCeros(minuto,2) + ':' + rellenarCeros(segundo,2) + ' del ' + dia + '/' + mes);

        var task = cron.schedule(cron_string, () => {
            console.log('[EJECUTAR] >>>>>> CRON DISPARADO! Componente: ' + nombre_comando + ' a las ' + getDateTime());

            if (typeof port !== "undefined") {
                var timeout = Math.floor((Math.random() * 800) + 1);
                console.log('[EJECUTAR] Enviando comando al puerto serial con timeout de ' + timeout + 'ms');
                setTimeout(() => {
                    port.write(nombre_comando + "E\n\r", (err) => {
                        console.log('[EJECUTAR] WRITE ------> ' + nombre_comando + 'E');
                        if (err){
                            console.log('[EJECUTAR] ERROR al escribir al puerto: ' + err.message);
                            return;
                        }
                        console.log('[EJECUTAR] Comando enviado exitosamente al puerto serial');
                    })
                }, timeout);

                var sql = 'INSERT INTO acciones (id_componentes, acc_tipos) VALUES (?, "Encender")';
                connection.query(sql, [componente], function (err, result) {
                    if (err) {
                        console.log('[EJECUTAR] ERROR al guardar accion en BD: ' + err.message);
                        throw err;
                    }
                    console.log('[EJECUTAR] Accion "Encender" guardada en BD para componente ' + nombre_comando);
                });

                var data = { 'com_nombre_comando':nombre_comando, 'id_componentes': componente }
                io.emit('tarea_encender', data);

                console.log('[EJECUTAR] Tarea ejecutada OK: ' + nombre_comando + ' a las ' + hora + ':' + minuto + ':' + segundo);
                io.emit('alerta',{'tipo':'success','mensaje':'Ejecución de la tarea COMPONENTE ' + nombre_comando + ' a las ' + hora + ':' + minuto + ':' + segundo + ' en America/Argentina/Buenos_Aires'});
            }else{
                console.log('[EJECUTAR] ERROR: No existe un PORT habilitado para ejecutar la tarea programada.');
                io.emit('alerta',{'tipo':'error','mensaje':'No existe un PORT habilitado para ejecutar la tarea programada.'});
            }
            task.destroy();
            console.log('[EJECUTAR] Cron destruido para ' + nombre_comando);
        }, {
            scheduled: true,
            timezone: "America/Argentina/Buenos_Aires"
        });
        console.log('[EJECUTAR] Cron registrado exitosamente para ' + nombre_comando);
}