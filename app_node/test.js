var app = require('express')();

var http = require('http').Server(app);
var io = require('socket.io')(http);

var Stopwatch = require('./models/stopwatch');

app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});

var new_rooms = [];
var rooms = [];
var watches = [];


new_rooms.push(1);
rooms.push(1);
watches.push (new Stopwatch(6000, 1));
new_rooms.push(2);
rooms.push(2);
watches.push (new Stopwatch(60000, 2));
new_rooms.push(3);
rooms.push(3);
watches.push (new Stopwatch(600000, 3));

console.log(new_rooms);

new_rooms.forEach(function(item) {
    var index = rooms.indexOf(item);
    watches[index].on('tick:stopwatch', function(time) { 
        //console.log(time); 
        io.emit('time-'+item, { time: time,  id: item });
    });

    watches[index].on('reset:stopwatch-'+item, function(time) {  
        io.emit('time-'+item, { time: time });
    });

    watches[index].on('stop:stopwatch-'+item, function() { 
        var indice = rooms.indexOf(item);
        io.emit('finish-'+item);
    });

    watches[index].start();

    io.on('connection', function (socket) {
        socket.emit('rooms', {rooms: rooms});
    });

    mueveReloj();
});

function mueveReloj(){ 
    momentoActual = new Date() 
    hora = momentoActual.getHours() 
    minuto = momentoActual.getMinutes() 
    segundo = momentoActual.getSeconds() 

    horaImprimible = hora + " : " + minuto + " : " + segundo 

    io.emit('hora_servidor', { horaImprimible: horaImprimible });  

    //La función se tendrá que llamar así misma para que sea dinámica, 
    //de esta forma:

    setTimeout(mueveReloj,1000)
}

http.listen(8880, function(){
    console.log('listening on *:8880');
});