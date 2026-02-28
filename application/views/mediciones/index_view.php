<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>
                Administración de Mediciones
                <a href="<?= base_url('web/exportaciones') ?>" class="btn btn-info float-right btn-sm">
                    Exportación a Excel (xlsx)
                </a>
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h3>Accionadores</h3>
            <select id="componente_accionadores" name="componente_accionadores" class="form-control">
                <option value="">Seleccione Accionador...</option>
                <?php
                    foreach ($componentes_accionadores as $componente){
                        if ($componente['com_nombre_comando'] != 'A'){
                ?>
                            <option value="<?= $componente['id_componentes'] ?>"><?= $componente['com_nombre'] ?></option>
                <?php
                        }
                    }
                ?>
            </select>
            <br>
            <div id="pnl_mediciones_accionadores">
                
            </div>
        </div>
        <div class="col-md-6">
            <h3>Sensores</h3>
            <select id="componente_sensores" name="componente_sensores" class="form-control">
                <option value="">Seleccione Sensor...</option>
                <?php
                    foreach ($componentes_sensores as $componente){
                        if ($componente['com_nombre_comando'] != 'A'){
                ?>
                            <option value="<?= $componente['id_componentes'] ?>"><?= $componente['com_nombre'] ?></option>
                <?php
                        }
                    }
                ?>
            </select>
            <br>
            <div id="pnl_mediciones_sensores">

            </div>   
        </div>
    </div>
</div>