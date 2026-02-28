# Errores en el Sistema de Ejecucion de Tareas - ChackraRepo

## ESTADO: TODOS LOS ERRORES FUERON CORREGIDOS

| # | Error | Estado |
|---|-------|--------|
| 1 | PHPExcel getValue() para horas del Excel | CORREGIDO |
| 2 | Formato de fecha sin ceros en programacion_tareas() | CORREGIDO |
| 3 | Variables globales en mueveReloj() y ejecucion duplicada | CORREGIDO |
| 4 | Conexiones MySQL (pool + cerrar conexiones) | CORREGIDO |
| 5 | Variable input inexistente | CORREGIDO |
| 6 | SQL injection - queries parametrizadas | CORREGIDO |
| 7 | log_message ERROR por DEBUG en models PHP | CORREGIDO |
| 8 | err=false en enviarToken | CORREGIDO |
| 9 | Crash por result[0] sin verificar | CORREGIDO |
| 10 | Valores ENUM invalidos en acciones | CORREGIDO |
| 11 | Stopwatch no se detiene (<=0) | CORREGIDO |

### NOTA IMPORTANTE PARA PRODUCCION

Despues de desplegar estos cambios, ejecutar en MySQL:
```sql
ALTER TABLE acciones MODIFY acc_tipos enum('Encender','Apagar','Corte Luz','Reestablecimiento Luz','Encender PIC') NOT NULL;
```

---

## Flujo General del Sistema

```
Excel Upload (PHP)
    |
    v
Ajax.importar() --> PHPExcel parsea el archivo
    |
    v
Horarios_model.save() --> INSERT en tabla "horarios"
    |
    v
Node.js: programacion_tareas() --> Lee horarios del dia desde la BD
    |
    v
ejecutarTarea() --> Crea un cron.schedule() para la hora exacta
    |
    v
Cuando llega la hora --> port.write(comando + "E\n\r") --> Arduino enciende luz
    |
    v
INSERT en tabla "acciones" + Socket.IO emit al cliente
```

---

## NOTA SOBRE EL FRONTEND

El frontend NO usa React. Esta hecho con:
- jQuery (v3.3.1)
- Bootstrap 4
- Socket.IO (comunicacion en tiempo real con el servidor Node.js)
- Vistas PHP (CodeIgniter templates)
- Alertify.js y SweetAlert (notificaciones)

---

## POR QUE NO SE EJECUTAN LAS TAREAS PROGRAMADAS

---

### ERROR #1 - PHPExcel devuelve un numero decimal en vez de texto para la hora

**Archivo:** `application/controllers/Ajax.php` - Lineas 276 y 291-294
**Severidad:** CRITICO - Probablemente la causa principal

El Excel envia la hora en formato `05:22:00 pm`. Pero PHPExcel con `getValue()` (linea 276) NO devuelve ese texto. Las celdas de hora en Excel se almacenan internamente como un **numero decimal** (fraccion del dia). Por ejemplo:

- `05:22:00 PM` internamente es `0.72361111`
- `06:57:03 AM` internamente es `0.28962847`

Entonces el codigo hace:
```php
// Ajax.php linea 276:
$data_value = $obj->getActiveSheet()->getCell($cell)->getValue();
// $data_value = 0.72361111 (NO "05:22:00 pm")

// Ajax.php linea 291-294:
$time = strtotime(0.72361111);   // strtotime NO entiende esto -> devuelve FALSE
$hora = date("H", false);        // "00"
$minuto = date("i", false);      // "00"
$segundo = date("s", false);     // "00"
```

**Resultado:** Todas las tareas se guardan con hora 0, minuto 0, segundo 0. Se programan para las 00:00:00, pero el cron las revisa a las 00:10. Como ya pasaron, `crear_tarea()` dice "Hora actual MAYOR a hora de tarea. NO CREO TAREA." y **nunca se ejecutan**.

**Solucion:** Usar `getFormattedValue()` en vez de `getValue()`, o convertir el numero decimal de Excel a hora:
```php
// Opcion 1: Usar getFormattedValue()
$data_value = $obj->getActiveSheet()->getCell($cell)->getFormattedValue();

// Opcion 2: Convertir el serial de Excel a timestamp
$data_value = PHPExcel_Shared_Date::ExcelToPHP($obj->getActiveSheet()->getCell($cell)->getValue());
$hora = date("H", $data_value);
$minuto = date("i", $data_value);
$segundo = date("s", $data_value);
```

**IMPORTANTE:** Para verificar esto, agregar un log ANTES de strtotime:
```php
log_message('ERROR', 'hora_inicio RAW: ' . print_r($row['hora_inicio'], true));
```
Si ves un numero como `0.723...` en el log, este es el problema.

---

### ERROR #2 - Formato de fecha sin ceros en Node.js

**Archivo:** `app_node/index.js` - Lineas 802-806
**Severidad:** CRITICO

```javascript
// En programacion_tareas():
now = new Date();
var dia = now.getDate();
var mes = now.getMonth() + 1;
var anio = now.getFullYear();
fecha_ahora = anio + '-' + mes + '-' + dia;
// Genera: "2019-2-5" en vez de "2019-02-05"
```

La base de datos almacena fechas tipo DATE (formato YYYY-MM-DD con ceros). La query busca:
```sql
WHERE hor_fecha = "2019-2-5"
```
Pero en la BD el valor es `2019-02-05`. **NO MATCHEA** cuando mes o dia es menor a 10.

Comparar con `comprobarExistenTareas()` (linea 892) que SI lo hace bien:
```javascript
var fecha_ahora = moment().format('YYYY-MM-DD'); // Correcto: "2019-02-05"
```

**Solucion:** Usar `moment().format('YYYY-MM-DD')` en `programacion_tareas()`.

---

### ERROR #3 - Variables globales contaminan la programacion de tareas

**Archivo:** `app_node/index.js` - Lineas 1059-1074
**Severidad:** CRITICO

```javascript
function mueveReloj(){
    momentoActual = new Date()
    hora = momentoActual.getHours()       // SIN var/let/const -> GLOBAL
    minuto = momentoActual.getMinutes()   // SIN var/let/const -> GLOBAL
    segundo = momentoActual.getSeconds()  // SIN var/let/const -> GLOBAL
    // ...
    setTimeout(mueveReloj, 1000)
}
```

Las variables `hora`, `minuto`, `segundo` son globales implicitas y se sobreescriben cada segundo. En `programacion_tareas()` (lineas 821-823) se usan variables con los mismos nombres:
```javascript
var hora = results[x].hor_hora;
var minutos = results[x].hor_minutos;
var segundo = results[x].hor_segundos;  // "segundo" colisiona con la global
```

Si hay un race condition, `segundo` puede tener el valor del reloj en vez del valor de la tarea, generando un cron_string incorrecto.

**Solucion:** Agregar `var` a todas las variables en `mueveReloj()`.

---

### ERROR #4 - mueveReloj() se ejecuta N veces en paralelo

**Archivo:** `app_node/index.js` - Linea 233
**Severidad:** ALTO

```javascript
rooms.forEach(function(item) {
    // ... configuracion de watches ...
    mueveReloj();  // Se llama UNA VEZ POR CADA ACTUADOR
});
```

Si hay 5 actuadores, se crean 5 instancias de `mueveReloj()` corriendo en paralelo, cada una con su propio `setTimeout` recursivo. Se multiplica cada vez que se llama `setearAccionadores()`.

**Solucion:** Llamar `mueveReloj()` una sola vez, fuera del forEach.

---

### ERROR #5 - Conexiones MySQL se crean y nunca se cierran

**Archivo:** `app_node/index.js` - Lineas 795-800 y 1106-1111
**Severidad:** ALTO

```javascript
function programacion_tareas(){
    const connection = mysql.createConnection({...}); // Nueva conexion LOCAL
    // Se usa pero NUNCA se cierra con connection.end()
}

function ejecutarTarea(...){
    var task = cron.schedule(cron_string, () => {
        const connection = mysql.createConnection({...}); // OTRA nueva conexion
        // Tampoco se cierra
    });
}
```

Cada dia se crea una conexion nueva. Cada tarea que se ejecuta crea otra. Nunca se cierran. Esto causa memory leaks y puede agotar el pool de conexiones de MySQL.

**Solucion:** Usar `connection.end()` al terminar o mejor usar `mysql.createPool()` como conexion principal.

---

### ERROR #6 - La conexion principal no tiene reconexion automatica

**Archivo:** `app_node/index.js` - Lineas 31-36
**Severidad:** ALTO

```javascript
const connection = mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'chacra'
});
```

Si MySQL se reinicia o la conexion se pierde (timeout), el servidor Node.js no se reconecta y todas las queries fallan. El servidor IoT esta disenado para correr 24/7.

**Solucion:** Usar `mysql.createPool()`.

---

### ERROR #7 - Variable "input" no existe

**Archivo:** `app_node/index.js` - Linea 70
**Severidad:** CRITICO

```javascript
if (typeof input !== "undefined") {
    temp_exterior = result_t00[0]['med_temperatura'];
} else {
    temp_exterior = 0;  // SIEMPRE entra aca
}
```

La variable `input` nunca se define. `temp_exterior` siempre es 0.

**Solucion:** Cambiar la condicion a verificar si `result_t00` tiene datos:
```javascript
if (result_t00 && result_t00.length > 0) {
```

---

### ERROR #8 - Error silenciado en enviarToken

**Archivo:** `app_node/index.js` - Linea 578
**Severidad:** ALTO

```javascript
port.write("K" + token + "\n\r", (err) => {
    err = false;  // SOBREESCRIBE el error real
    if (err) {    // NUNCA se ejecuta
        return console.log('Erro al escribir: ', err.message);
    }
```

**Solucion:** Eliminar la linea `err = false;`

---

### ERROR #9 - Crash por result[0] sin verificar

**Archivo:** `app_node/index.js` - Linea 386
**Severidad:** CRITICO

```javascript
connection.query(sql, function (err, result) {
    if (err) throw err;
    // Si result esta vacio, esto crashea el servidor:
    var sql = 'UPDATE mediciones SET ... WHERE id_mediciones = ' + result[0]['id_mediciones'];
```

Si no hay mediciones con estado "Iniciado", `result[0]` es `undefined` y el servidor se cae.

**Solucion:** Verificar `if (result && result.length > 0)` antes de acceder a `result[0]`.

---

### ERROR #10 - Valores ENUM invalidos en acciones

**Archivo:** `BD.sql` linea 34 vs `app_node/index.js` lineas 479, 484, 618
**Severidad:** ALTO

La tabla acciones define:
```sql
acc_tipos enum('Encender','Apagar')
```

Pero el codigo intenta insertar valores que no existen en el ENUM:
- "Corte Luz" (linea 479)
- "Reestablecimiento Luz" (linea 484)
- "Encender PIC" (linea 618)

**Solucion:** Agregar estos valores al ENUM o cambiar a VARCHAR.

---

### ERROR #11 - SQL Injection en todo el proyecto

**Archivos:** `app_node/index.js` (multiples lineas)
**Severidad:** ALTO (seguridad)

Todas las queries concatenan strings directamente:
```javascript
var sql = 'SELECT * FROM componentes WHERE com_nombre_comando = "T' + componente + '"';
```

**Solucion:** Usar queries parametrizadas:
```javascript
connection.query('SELECT * FROM componentes WHERE com_nombre_comando = ?', ['T' + componente], callback);
```

---

### ERROR #12 - Stopwatch puede no detenerse nunca

**Archivo:** `app_node/models/stopwatch.js` - Linea 79
**Severidad:** MEDIO

```javascript
if (this.time === 0) {
    this.stop();
}
```

Si `this.time` pasa de positivo a negativo sin tocar exactamente 0, el cronometro nunca se detiene.

**Solucion:** Cambiar a `if (this.time <= 0)`.

---

### ERROR #13 - Errores de queries ignorados

**Archivo:** `app_node/index.js` - Linea 68
**Severidad:** MEDIO

```javascript
connection.query(sql1, function (err, result_t00) {
    // err se ignora completamente
```

---

### ERROR #14 - Columnas de calibracion no existen en el schema

**Archivo:** `app_node/index.js` - Lineas 342, 348
**Severidad:** MEDIO

Se referencian `com_calibracion_temperatura` y `com_calibracion_humedad` pero no estan en la tabla componentes del BD.sql.

---

### ERROR #15 - log_message con nivel ERROR para queries normales

**Archivos:** `application/models/Acciones_model.php` linea 29, `application/models/Horarios_model.php` linea 31
**Severidad:** BAJO

```php
log_message('ERROR', print_r($this->db->last_query(), 1));
```

Se loguea cada query exitosa como ERROR, contaminando los logs.

---

## ARCHIVOS PARA ANALIZAR

### Flujo de importacion Excel (PHP):

| # | Archivo | Que Hace |
|---|---------|----------|
| 1 | `application/controllers/Ajax.php` (lineas 252-321) | Importa Excel, parsea hora, guarda en BD |
| 2 | `application/models/Horarios_model.php` | CRUD de horarios |
| 3 | `application/views/importaciones/form_view.php` | Formulario de carga de Excel |
| 4 | `application/views/horarios/listado_view.php` | Lista de horarios programados |
| 5 | `application/views/horarios/index_view.php` | Vista principal de horarios |
| 6 | `assets/frontend/js/administracion_horarios.js` | JS del AJAX de horarios |
| 7 | `system/core/Controller.php` (linea 100) | Funcion fechaMySQL() |

### Flujo de ejecucion de tareas (Node.js):

| # | Archivo | Lineas | Que Hace |
|---|---------|--------|----------|
| 1 | `app_node/index.js` | 791-858 | `programacion_tareas()` - Lee horarios del dia |
| 2 | `app_node/index.js` | 1101-1145 | `ejecutarTarea()` - Crea cron y envia comando |
| 3 | `app_node/index.js` | 1039-1057 | `crear_tarea()` - Valida si hora es futura |
| 4 | `app_node/index.js` | 860-871 | Cron diario 00:10 + carga manual |
| 5 | `app_node/index.js` | 1019-1037 | `tareas_manuales()` - Decide programacion manual |
| 6 | `app_node/index.js` | 889-923 | `comprobarExistenTareas()` - Verifica tareas proximas |
| 7 | `app_node/index.js` | 570-608 | `enviarToken()` - Comunicacion con PIC |
| 8 | `app_node/index.js` | 190-236 | `setearAccionadores()` - Configura actuadores |
| 9 | `app_node/index.js` | 632-682 | `encender()` - Enciende componente |
| 10 | `app_node/index.js` | 684-725 | `apagar()` - Apaga componente |
| 11 | `app_node/index.js` | 1059-1074 | `mueveReloj()` - Variables globales problematicas |
| 12 | `app_node/models/stopwatch.js` | 1-117 | Temporizador de apagado automatico |

### Base de datos:

| # | Archivo | Que Hace |
|---|---------|----------|
| 1 | `BD.sql` | Schema completo (horarios, acciones, componentes, mediciones) |

---

## RESUMEN: POR DONDE EMPEZAR

Para solucionar el problema de que las tareas no se ejecutan:

1. **PRIMERO** - Verificar que PHPExcel devuelve la hora correcta (Error #1). Agregar log en Ajax.php linea 291 para ver el valor crudo de `hora_inicio`. Si es un numero decimal, usar `getFormattedValue()` o `PHPExcel_Shared_Date::ExcelToPHP()`.

2. **SEGUNDO** - Corregir formato de fecha en `programacion_tareas()` (Error #2). Usar `moment().format('YYYY-MM-DD')`.

3. **TERCERO** - Agregar `var` a variables de `mueveReloj()` (Error #3). Evitar race conditions.

4. **CUARTO** - Llamar `mueveReloj()` una sola vez (Error #4).

5. **QUINTO** - Cerrar conexiones MySQL o usar pool (Errores #5 y #6).

6. **SEXTO** - Verificar result[0] antes de acceder (Error #9).
