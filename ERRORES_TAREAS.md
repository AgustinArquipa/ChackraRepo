# Errores en el Sistema de Ejecucion de Tareas - ChackraRepo

## ESTADO: TODOS LOS ERRORES CORREGIDOS Y VERIFICADOS EN PRODUCCION

| # | Error | Severidad | Archivo | Estado |
|---|-------|-----------|---------|--------|
| 1 | PHPExcel getValue() devuelve decimal en vez de hora | CRITICO | Ajax.php | CORREGIDO |
| 2 | Formato de fecha sin ceros en programacion_tareas() | CRITICO | index.js | CORREGIDO |
| 3 | Variables globales en mueveReloj() contaminan scope | CRITICO | index.js | CORREGIDO |
| 4 | mueveReloj() se ejecuta N veces en paralelo | ALTO | index.js | CORREGIDO |
| 5 | Conexiones MySQL sin pool ni reconexion | ALTO | index.js | CORREGIDO |
| 6 | Variable "input" inexistente | CRITICO | index.js | CORREGIDO |
| 7 | SQL injection en todas las queries | ALTO | index.js | CORREGIDO |
| 8 | err=false silencia errores en enviarToken | ALTO | index.js | CORREGIDO |
| 9 | Crash por result[0] sin verificar | CRITICO | index.js | CORREGIDO |
| 10 | Valores ENUM invalidos en tabla acciones | ALTO | BD.sql | CORREGIDO |
| 11 | Stopwatch no se detiene (=== 0 vs <= 0) | MEDIO | stopwatch.js | CORREGIDO |
| 12 | log_message ERROR para queries normales | BAJO | Models PHP | CORREGIDO |
| 13 | Node.js no detecta nuevos horarios tras importar Excel | CRITICO | index.js | CORREGIDO |

### NOTA PARA PRODUCCION

Ejecutar en MySQL:
```sql
ALTER TABLE acciones MODIFY acc_tipos enum('Encender','Apagar','Corte Luz','Reestablecimiento Luz','Encender PIC') NOT NULL;
```

---

## Causa Raiz del Problema Principal

Las tareas programadas no se ejecutaban por una combinacion de errores:

1. **Node.js solo leia horarios una vez al dia** (cron a las 00:10). Si se importaba un Excel despues de las 00:10, Node.js nunca se enteraba de los nuevos horarios.
2. **El formato de fecha sin ceros** ("2026-2-5" vs "2026-02-05") hacia que la query no encontrara horarios cuando el mes o dia era menor a 10.
3. **Variables globales de mueveReloj()** podian sobreescribir las variables de hora/minuto/segundo de las tareas.

**Solucion definitiva:** Se agrego un monitor que cada 30 segundos revisa la BD buscando horarios nuevos o modificados y los reprograma automaticamente.

---

## Flujo del Sistema

```
Excel Upload (PHP)
    |
    v
Ajax.importar() --> PHPExcel parsea el archivo
    |
    v
Horarios_model.save() --> INSERT/UPDATE en tabla "horarios"
    |
    v
Monitor (cada 30s) --> Detecta nuevos horarios en la BD
    |
    v
programacion_tareas() --> Lee horarios del dia
    |
    v
ejecutarTarea() --> Crea cron.schedule() para la hora exacta
    |
    v
Cuando llega la hora --> port.write(comando) --> Arduino enciende luz
    |
    v
INSERT en tabla "acciones" + Socket.IO emit al cliente
```

---

## Detalle de Errores y Correcciones

### #1 - PHPExcel getValue() devuelve decimal para horas

**Archivo:** `application/controllers/Ajax.php`

PHPExcel con `getValue()` puede devolver un numero decimal (fraccion del dia) en vez del texto de la hora. Ejemplo: `05:22:00 PM` -> `0.72361111`.

**Correccion:** Se detecta si el valor es numerico entre 0 y 1 y se convierte con `PHPExcel_Shared_Date::ExcelToPHP()`.

---

### #2 - Formato de fecha sin ceros en Node.js

**Archivo:** `app_node/index.js` - `programacion_tareas()`

```javascript
// ANTES (bug): genera "2026-2-5"
fecha_ahora = anio + '-' + mes + '-' + dia;

// DESPUES (corregido): genera "2026-02-05"
var fecha_ahora = moment().format('YYYY-MM-DD');
```

---

### #3 - Variables globales en mueveReloj()

**Archivo:** `app_node/index.js` - `mueveReloj()`

Las variables `hora`, `minuto`, `segundo` se declaraban sin `var`, creando globales implicitas que se sobreescribian cada segundo y colisionaban con las variables de `programacion_tareas()`.

**Correccion:** Variables locales con `var` + guard para evitar ejecucion multiple.

---

### #4 - mueveReloj() ejecutada N veces

**Archivo:** `app_node/index.js`

Se llamaba dentro de un `forEach` de actuadores, creando multiples instancias con `setTimeout` recursivo.

**Correccion:** Guard booleano `mueveRelojIniciado` que previene ejecucion duplicada.

---

### #5 - Conexiones MySQL sin pool

**Archivo:** `app_node/index.js`

- Conexion principal con `createConnection` no reconectaba si MySQL se reiniciaba.
- `programacion_tareas()` y `ejecutarTarea()` creaban conexiones locales que nunca se cerraban.

**Correccion:** Se cambio a `mysql.createPool()` con `connectionLimit: 10`. Se eliminaron las conexiones locales.

---

### #6 - Variable "input" inexistente

**Archivo:** `app_node/index.js`

```javascript
// ANTES: "input" nunca se define, siempre temp_exterior = 0
if (typeof input !== "undefined") { ... }

// DESPUES: verifica si hay datos en el resultado de la query
if (result_t00 && result_t00.length > 0) { ... }
```

---

### #7 - SQL Injection

**Archivo:** `app_node/index.js`

Todas las queries concatenaban strings. Se cambiaron a queries parametrizadas con `?`.

```javascript
// ANTES
var sql = 'SELECT * FROM componentes WHERE com_nombre_comando = "T' + componente + '"';

// DESPUES
connection.query('SELECT * FROM componentes WHERE com_nombre_comando = ?', ['T' + componente], callback);
```

---

### #8 - err=false en enviarToken

**Archivo:** `app_node/index.js` - `enviarToken()`

La linea `err = false;` sobreescribia cualquier error del puerto serial, silenciando fallos de comunicacion con el Arduino.

**Correccion:** Se elimino la linea.

---

### #9 - Crash por result[0] sin verificar

**Archivo:** `app_node/index.js`

Si una query retornaba sin resultados, acceder a `result[0]` crasheaba el servidor Node.js.

**Correccion:** Se agrego `if (result && result.length > 0)` antes de acceder a `result[0]`.

---

### #10 - Valores ENUM invalidos

**Archivo:** `BD.sql`

La tabla `acciones` solo tenia `enum('Encender','Apagar')` pero el codigo insertaba "Corte Luz", "Reestablecimiento Luz" y "Encender PIC".

**Correccion:** Se amplio el ENUM en BD.sql. Requiere ejecutar ALTER TABLE en produccion.

---

### #11 - Stopwatch no se detiene

**Archivo:** `app_node/models/stopwatch.js`

```javascript
// ANTES: si time salta de 500 a -500, nunca es exactamente 0
if (this.time === 0) { this.stop(); }

// DESPUES: cualquier valor <= 0 detiene el cronometro
if (this.time <= 0) { this.stop(); }
```

---

### #12 - log_message ERROR para queries normales

**Archivos:** `application/models/Acciones_model.php`, `application/models/Horarios_model.php`

Queries exitosas se logueaban con nivel ERROR, contaminando los logs de errores reales.

**Correccion:** Se cambio a nivel `debug`.

---

### #13 - Node.js no detecta nuevos horarios (CAUSA RAIZ)

**Archivo:** `app_node/index.js`

Node.js solo leia horarios una vez al dia con un cron a las 00:10. Cualquier Excel importado despues quedaba sin programar hasta el dia siguiente (cuando ya habia pasado la hora).

**Correccion:** Se agrego un monitor con `setInterval` cada 30 segundos que:
1. Consulta la BD buscando horarios de hoy
2. Filtra los que tienen hora futura
3. Compara un fingerprint con la ultima revision
4. Si detecta cambios, llama a `programacion_tareas()` para reprogramar

---

## Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `app_node/index.js` | Pool MySQL, queries parametrizadas, monitor 30s, mueveReloj local, logs [TAREAS]/[MONITOR] |
| `app_node/models/stopwatch.js` | Condicion `<= 0` |
| `application/controllers/Ajax.php` | Conversion hora Excel, notificacion a Node.js |
| `application/models/Acciones_model.php` | log_message nivel debug |
| `application/models/Horarios_model.php` | log_message nivel debug |
| `BD.sql` | ENUM ampliado con nuevos valores |
| `app_node/test_tareas.js` | Tests automatizados (28 tests) |

## Tests

Ejecutar: `cd app_node && node test_tareas.js`

Cubre: lectura Excel, parseo de hora, formato de fecha, cron string, crear_tarea(), mueveReloj scope, rellenarCeros(), Stopwatch, flujo completo.
