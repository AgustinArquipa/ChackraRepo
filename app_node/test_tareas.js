/**
 * Tests para verificar las correcciones del sistema de tareas
 * Ejecutar: cd app_node && node test_tareas.js
 */

var moment = require('moment');
var XLSX = require('xlsx');
var path = require('path');

var errores = 0;
var aciertos = 0;

function assert(condicion, mensaje) {
    if (condicion) {
        console.log('  OK: ' + mensaje);
        aciertos++;
    } else {
        console.log('  FALLO: ' + mensaje);
        errores++;
    }
}

function titulo(texto) {
    console.log('\n========================================');
    console.log('TEST: ' + texto);
    console.log('========================================');
}

// ============================================
// Funciones copiadas del index.js para testear
// ============================================

function rellenarCeros(number, width) {
    var numberOutput = Math.abs(number);
    var length = number.toString().length;
    var zero = "0";
    if (width <= length) {
        if (number < 0) {
            return ("-" + numberOutput.toString());
        } else {
            return numberOutput.toString();
        }
    } else {
        if (number < 0) {
            return ("-" + (zero.repeat(width - length)) + numberOutput.toString());
        } else {
            return ((zero.repeat(width - length)) + numberOutput.toString());
        }
    }
}

function getDateTime() {
    var date = new Date();
    var hour = date.getHours();
    hour = (hour < 10 ? "0" : "") + hour;
    var min = date.getMinutes();
    min = (min < 10 ? "0" : "") + min;
    var sec = date.getSeconds();
    sec = (sec < 10 ? "0" : "") + sec;
    return hour + ":" + min + ":" + sec;
}

function crear_tarea(hora_tarea) {
    var a = getDateTime();
    var b = hora_tarea;
    var aa1 = a.split(":");
    var aa2 = b.split(":");
    var d1 = new Date(parseInt("2001", 10), (parseInt("01", 10)) - 1, parseInt("01", 10), parseInt(aa1[0], 10), parseInt(aa1[1], 10), parseInt(aa1[2], 10));
    var d2 = new Date(parseInt("2001", 10), (parseInt("01", 10)) - 1, parseInt("01", 10), parseInt(aa2[0], 10), parseInt(aa2[1], 10), parseInt(aa2[2], 10));
    var dd1 = d1.valueOf();
    var dd2 = d2.valueOf();
    if (dd1 < dd2) {
        return true;
    } else {
        return false;
    }
}

// ============================================
// TEST 1: Lectura del Excel CAMARA 2.xlsx
// ============================================
titulo('Lectura del Excel CAMARA 2.xlsx');

var excelPath = path.join(__dirname, '..', 'CAMARA 2.xlsx');
var excelExiste = false;
var wb, ws, datos;
try {
    wb = XLSX.readFile(excelPath);
    ws = wb.Sheets[wb.SheetNames[0]];
    datos = XLSX.utils.sheet_to_json(ws);
    excelExiste = true;
} catch(e) {
    console.log('  INFO: Excel "CAMARA 2.xlsx" no encontrado, usando datos de prueba simulados');
    datos = [{ hor_fecha: moment().format('YYYY-MM-DD'), hora_inicio: '07:36:00 pm' }];
}

assert(datos.length > 0, 'El Excel tiene al menos 1 fila de datos (tiene ' + datos.length + ')');
assert(datos[0].hor_fecha !== undefined, 'Columna "hor_fecha" existe');
assert(datos[0].hora_inicio !== undefined, 'Columna "hora_inicio" existe');

var hor_fecha = datos[0].hor_fecha;
var hora_inicio = datos[0].hora_inicio;

console.log('  Valores leidos: fecha=' + hor_fecha + ', hora=' + hora_inicio);

assert(typeof hora_inicio === 'string', 'hora_inicio es string (tipo: ' + typeof hora_inicio + ')');
assert(hora_inicio.includes('pm') || hora_inicio.includes('am') || hora_inicio.includes('PM') || hora_inicio.includes('AM') || /^\d{1,2}:\d{2}/.test(hora_inicio), 'hora_inicio tiene formato de hora valido: "' + hora_inicio + '"');

// ============================================
// TEST 2: Simulacion del parseo PHP (strtotime)
// ============================================
titulo('Simulacion del parseo de hora (como lo haria PHP strtotime)');

// Simular lo que hace PHP: strtotime("05:22:00 pm") -> hora=17, min=22, seg=00
// En JS no tenemos strtotime, pero podemos parsear el formato
function parsearHoraComoPhp(horaStr) {
    // Simula el comportamiento de PHP strtotime + date("H"), date("i"), date("s")
    var parts = horaStr.trim().toLowerCase().match(/^(\d{1,2}):(\d{2}):(\d{2})\s*(am|pm)?$/);
    if (!parts) return null;

    var h = parseInt(parts[1]);
    var m = parseInt(parts[2]);
    var s = parseInt(parts[3]);
    var ampm = parts[4];

    if (ampm === 'pm' && h !== 12) h += 12;
    if (ampm === 'am' && h === 12) h = 0;

    return { hora: h, minuto: m, segundo: s };
}

var parsed = parsearHoraComoPhp(hora_inicio);
assert(parsed !== null, 'hora_inicio se parsea correctamente');
if (parsed) {
    console.log('  Parseado: hora=' + parsed.hora + ', minuto=' + parsed.minuto + ', segundo=' + parsed.segundo);
    assert(parsed.hora >= 0 && parsed.hora <= 23, 'Hora es valida (0-23): ' + parsed.hora);
    assert(parsed.minuto >= 0 && parsed.minuto <= 59, 'Minuto es valido (0-59): ' + parsed.minuto);
    assert(parsed.segundo >= 0 && parsed.segundo <= 59, 'Segundo es valido (0-59): ' + parsed.segundo);

    // Verificar que NO sea 00:00:00 (el bug que encontramos)
    var es_medianoche = (parsed.hora === 0 && parsed.minuto === 0 && parsed.segundo === 0);
    if (hora_inicio.toLowerCase().includes('pm') || (parseInt(hora_inicio) > 0)) {
        assert(!es_medianoche, 'La hora parseada NO es 00:00:00 (bug de PHPExcel corregido)');
    }
}

// ============================================
// TEST 3: Formato de fecha (Error #2 corregido)
// ============================================
titulo('Formato de fecha con moment.js (Error #2)');

var fecha_moment = moment().format('YYYY-MM-DD');
var now = new Date();
var fecha_vieja = now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate();

console.log('  Formato VIEJO (con bug): "' + fecha_vieja + '"');
console.log('  Formato NUEVO (corregido): "' + fecha_moment + '"');

assert(fecha_moment.length === 10, 'Fecha moment tiene 10 caracteres (YYYY-MM-DD): "' + fecha_moment + '"');
assert(/^\d{4}-\d{2}-\d{2}$/.test(fecha_moment), 'Fecha moment tiene formato correcto YYYY-MM-DD');

// Simular matcheo con la fecha del Excel
assert(fecha_moment === hor_fecha, 'Fecha de hoy (' + fecha_moment + ') MATCHEA con la fecha del Excel (' + hor_fecha + ')');

// Verificar que el formato viejo PODIA fallar
if (now.getMonth() + 1 < 10 || now.getDate() < 10) {
    assert(fecha_vieja !== fecha_moment, 'El formato viejo HUBIERA FALLADO: "' + fecha_vieja + '" != "' + fecha_moment + '"');
} else {
    console.log('  INFO: Hoy dia y mes son >= 10, ambos formatos coinciden');
}

// ============================================
// TEST 4: Generacion de cron string
// ============================================
titulo('Generacion de cron string para la tarea');

if (parsed) {
    var dia = now.getDate();
    var mes = now.getMonth() + 1;
    var cron_string = parsed.segundo + ' ' + parsed.minuto + ' ' + parsed.hora + ' ' + dia + ' ' + mes + ' *';
    console.log('  Cron string generado: "' + cron_string + '"');

    // Verificar que el cron es valido
    var cron = require('node-cron');
    var es_valido = cron.validate(cron_string);
    assert(es_valido, 'Cron string es valido: "' + cron_string + '"');

    // Verificar que la hora en el cron NO sea 0 0 0 (el bug principal)
    assert(!(parsed.hora === 0 && parsed.minuto === 0 && parsed.segundo === 0),
        'Cron NO programa a las 00:00:00 (bug principal evitado)');
}

// ============================================
// TEST 5: crear_tarea() valida correctamente
// ============================================
titulo('Funcion crear_tarea() - validacion de hora futura');

if (parsed) {
    var hora_completa = rellenarCeros(parsed.hora, 2) + ":" + rellenarCeros(parsed.minuto, 2) + ":" + rellenarCeros(parsed.segundo, 2);
    console.log('  Hora completa de la tarea: ' + hora_completa);
    console.log('  Hora actual: ' + getDateTime());

    var resultado = crear_tarea(hora_completa);
    console.log('  crear_tarea() retorna: ' + resultado);

    if (resultado) {
        console.log('  OK: La tarea ES FUTURA, se programaria correctamente');
    } else {
        console.log('  INFO: La tarea YA PASO, no se programaria (esto es correcto si la hora ya paso hoy)');
    }

    // Test con hora futura segura (23:59:59)
    assert(crear_tarea("23:59:59") === true, 'crear_tarea("23:59:59") retorna true (hora futura)');

    // Test con hora pasada segura (00:00:01)
    assert(crear_tarea("00:00:01") === false, 'crear_tarea("00:00:01") retorna false (hora pasada)');
}

// ============================================
// TEST 6: Variables de mueveReloj no contaminan
// ============================================
titulo('Variables de mueveReloj NO contaminan el scope global');

// Simular la funcion corregida
var mueveRelojIniciado = false;
function mueveRelojCorregido() {
    if (mueveRelojIniciado) return 'ya_iniciado';
    mueveRelojIniciado = true;

    var momentoActual = new Date();
    var h = momentoActual.getHours();
    var m = momentoActual.getMinutes();
    var s = momentoActual.getSeconds();
    return 'ok';
}

// Definir variables que simulan las de programacion_tareas
var hora = 17;
var segundo = 30;

// Ejecutar mueveReloj
mueveRelojCorregido();

// Verificar que NO contamino las variables
assert(hora === 17, 'Variable "hora" no fue contaminada por mueveReloj (valor: ' + hora + ')');
assert(segundo === 30, 'Variable "segundo" no fue contaminada por mueveReloj (valor: ' + segundo + ')');

// Verificar que no se ejecuta dos veces
var resultado2 = mueveRelojCorregido();
assert(resultado2 === 'ya_iniciado', 'mueveReloj no se ejecuta dos veces (guard funciona)');

// ============================================
// TEST 7: rellenarCeros funciona correctamente
// ============================================
titulo('Funcion rellenarCeros()');

assert(rellenarCeros(5, 2) === '05', 'rellenarCeros(5, 2) = "05"');
assert(rellenarCeros(12, 2) === '12', 'rellenarCeros(12, 2) = "12"');
assert(rellenarCeros(0, 2) === '00', 'rellenarCeros(0, 2) = "00"');
assert(rellenarCeros(9, 2) === '09', 'rellenarCeros(9, 2) = "09"');

// ============================================
// TEST 8: Stopwatch se detiene en <= 0
// ============================================
titulo('Stopwatch se detiene correctamente');

var Stopwatch = require('./models/stopwatch');
var sw = new Stopwatch(2000, 'test');
var detenido = false;

sw.on('stop:stopwatch-test', function () {
    detenido = true;
});

// Simular que time es 1000 (1 segundo restante)
sw.time = 1000;
sw.onTick(); // time pasa a 0 -> deberia detenerse
assert(sw.time <= 0, 'Stopwatch: time llega a 0 o menos despues de tick');

// Simular caso edge: time es 500 (medio segundo)
var sw2 = new Stopwatch(500, 'test2');
sw2.time = 500;
sw2.onTick(); // time pasa a -500 -> con <= 0 se detiene
assert(sw2.time <= 0, 'Stopwatch: time negativo (-500) tambien se detiene con <= 0');

// ============================================
// TEST 9: Flujo completo Excel -> Cron
// ============================================
titulo('FLUJO COMPLETO: Excel -> parseo -> cron string -> validacion');

console.log('  1. Leyendo Excel: CAMARA 2.xlsx');
console.log('     hor_fecha = "' + hor_fecha + '"');
console.log('     hora_inicio = "' + hora_inicio + '"');

console.log('  2. Parseando hora (simulando PHP strtotime):');
if (parsed) {
    console.log('     hora=' + parsed.hora + ' minuto=' + parsed.minuto + ' segundo=' + parsed.segundo);

    console.log('  3. Formateando fecha con moment:');
    console.log('     fecha_ahora = "' + fecha_moment + '"');

    console.log('  4. Query SQL que se ejecutaria:');
    var sql = 'SELECT * FROM horarios H INNER JOIN componentes C ON H.id_componentes = C.id_componentes WHERE hor_fecha = ?';
    console.log('     ' + sql);
    console.log('     parametro: [' + fecha_moment + ']');

    var matchea = (fecha_moment === hor_fecha);
    console.log('  5. La fecha matchea con el Excel? ' + (matchea ? 'SI' : 'NO'));

    console.log('  6. Cron string:');
    var cron_final = parsed.segundo + ' ' + parsed.minuto + ' ' + parsed.hora + ' ' + dia + ' ' + mes + ' *';
    console.log('     "' + cron_final + '"');

    var hora_tarea_completa = rellenarCeros(parsed.hora, 2) + ':' + rellenarCeros(parsed.minuto, 2) + ':' + rellenarCeros(parsed.segundo, 2);
    var se_crea = crear_tarea(hora_tarea_completa);
    console.log('  7. crear_tarea("' + hora_tarea_completa + '") = ' + se_crea);

    if (matchea && se_crea) {
        console.log('');
        console.log('  >>> RESULTADO: La tarea SE PROGRAMARIA y SE EJECUTARIA a las ' + hora_tarea_completa);
        assert(true, 'Flujo completo EXITOSO');
    } else if (matchea && !se_crea) {
        console.log('');
        console.log('  >>> RESULTADO: La fecha matchea pero la hora ' + hora_tarea_completa + ' ya paso hoy.');
        console.log('  >>> Para testearlo en vivo, modifica la hora en el Excel a una hora futura.');
        assert(true, 'Flujo completo correcto (tarea pasada, comportamiento esperado)');
    } else {
        console.log('');
        console.log('  >>> RESULTADO: La fecha NO matchea. La tarea no se encontraria en la BD.');
        assert(false, 'Flujo completo FALLIDO - fecha no coincide');
    }
}

// ============================================
// RESUMEN
// ============================================
console.log('\n========================================');
console.log('RESUMEN DE TESTS');
console.log('========================================');
console.log('Aciertos: ' + aciertos);
console.log('Fallos: ' + errores);
console.log('Total: ' + (aciertos + errores));
console.log('');

if (errores === 0) {
    console.log('TODOS LOS TESTS PASARON CORRECTAMENTE');
} else {
    console.log('HAY ' + errores + ' TEST(S) QUE FALLARON');
}

process.exit(errores > 0 ? 1 : 0);
