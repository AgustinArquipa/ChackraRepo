const cron = require('node-cron');
const http = require('http');
const express = require('express');
const app = express();
const server = http.createServer(app);
var moment = require('moment');

var now = new Date(); //Obtienes la fecha
var fecha_ahora = now.getFullYear() + '-' + (now.getMonth() +1)  + '-' + now.getDate();

var dia = now.getDate();
var mes = now.getMonth() +1;

console.log("* * * "+ dia +" " + mes + " *" );

var breakfast = moment('8:32:00','HH:mm:ss');
var lunch = moment('8:32:02','HH:mm:ss');
var diferencia = moment.duration(lunch - breakfast)._milliseconds; 
if (diferencia > 2000){
    console.log('ES MAYOR');
}else{
    console.log('ES MENOR');
}

console.log( moment.duration(lunch - breakfast).humanize() + ' between meals' ) // 4 hours between meals
console.log( moment.duration(lunch - breakfast)._milliseconds)

server.listen(3000, function(){
    console.log('Servidor activo en el puerto Nro.', 3000);
})