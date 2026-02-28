<form id="valores_limites">
    <h2>Configurar</h2>
    <input id="id_componentes" name="id_componentes" type="hidden" class="form-control" value="<?= (isset($componente[0]['id_componentes'])) ? $componente[0]['id_componentes']:"" ?>">
    <input id="com_tipos" name="com_tipos" type="hidden" class="form-control" value="<?= (isset($componente[0]['com_tipos'])) ? $componente[0]['com_tipos']:"" ?>">
    <fieldset class="form-group">
        <legend>Datos</legend>
        <div class="form-group">
            <label for="com_nombre">Nombre:</label>
            <input id="com_nombre" name="com_nombre" type="text" class="form-control" value="<?= (isset($componente[0]['com_nombre'])) ? $componente[0]['com_nombre']:"" ?>">
        </div>
    </fieldset>
    <fieldset class="form-group" id="extra_accionadores">
        <legend>Timers</legend>
        <div class="form-group">
            <label for="com_tiempo_apagado">Tiempo de Espera para Apagar:</label>
            <input id="com_tiempo_apagado" name="com_tiempo_apagado" type="number" class="form-control" value="<?= (isset($componente[0]['com_tiempo_apagado'])) ? $componente[0]['com_tiempo_apagado'] / 60000 :"0" ?>">
        </div>
    </fieldset>
    <fieldset class="form-group extra_sensores">
        <legend>Temperatura</legend>
        <div class="form-group">
            <label for="com_limite_inferior_temperatura">Limite Inferior:</label>
            <input id="com_limite_inferior_temperatura" name="com_limite_inferior_temperatura" type="number" class="form-control" value="<?= (isset($componente[0]['com_limite_inferior_temperatura'])) ? $componente[0]['com_limite_inferior_temperatura']:"0" ?>">
        </div>
        <div class="form-group">
            <label for="com_limite_superior_temperatura">Limite Superior:</label>
            <input id="com_limite_superior_temperatura" name="com_limite_superior_temperatura" type="number" class="form-control" value="<?= (isset($componente[0]['com_limite_superior_temperatura'])) ? $componente[0]['com_limite_superior_temperatura']:"0" ?>">   
        </div> 
    </fieldset>
    <fieldset class="form-group extra_sensores">
        <legend>Humedad</legend>
        <div class="form-group">
            <label for="com_limite_inferior_humedad">Limite Inferior:</label>
            <input id="com_limite_inferior_humedad" name="com_limite_inferior_humedad" type="number" class="form-control" value="<?= (isset($componente[0]['com_limite_inferior_humedad'])) ? $componente[0]['com_limite_inferior_humedad']:"0" ?>">
        </div>
        <div class="form-group">
            <label for="com_limite_superior_humedad">Limite Superior:</label>
            <input id="com_limite_superior_humedad" name="com_limite_superior_humedad" type="number" class="form-control" value="<?= (isset($componente[0]['com_limite_superior_humedad'])) ? $componente[0]['com_limite_superior_humedad']:"0" ?>">   
        </div> 
    </fieldset>
    <fieldset class="form-group extra_sensores">
        <legend>Calibración</legend>
        <div class="form-group">
            <label for="com_calibracion_temperatura">Temperatura:</label>
            <input id="com_calibracion_temperatura" name="com_calibracion_temperatura" type="number"  step="0.01" class="form-control" value="<?= (isset($componente[0]['com_calibracion_temperatura'])) ? $componente[0]['com_calibracion_temperatura']:"0" ?>">
        </div>
        <div class="form-group">
            <label for="com_calibracion_humedad">Humedad:</label>
            <input id="com_calibracion_humedad" name="com_calibracion_humedad" type="number" class="form-control" step="0.01" value="<?= (isset($componente[0]['com_calibracion_humedad'])) ? $componente[0]['com_calibracion_humedad']:"0" ?>">   
        </div> 
    </fieldset>
    <div class="form-group text-right">
        <button class="btn btn-success" type="submit">Guardar</button>
        <a href="#" id="cancelar_carga" class="btn btn-danger">Cancelar</a>
    </div>    
</form>