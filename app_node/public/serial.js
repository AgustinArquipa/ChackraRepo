var SerialPort = require('serialport');

var serialPort = new SerialPort('/dev/ttyUSB0', {
    baudRate: 9600
});

// Switches the port into "flowing mode"
serialPort.on('data', function (data) {
    console.log('Data:', data);
});

// Read data that is available but keep the stream from entering //"flowing mode"
serialPort.on('readable', function () {
    console.log('Data:', port.read());
});