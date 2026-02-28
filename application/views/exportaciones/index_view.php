<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Exportaciones a Excel <small>(.xlsx)</small></h1>
        </div>
    </div>
    <form id="exportaciones">
        <div class="form-row">
            <div class="col">
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
            </div>
            <div class="col">
                <input type="text" class="form-control" name="daterange" value="01/01/2018 - 01/15/2018" />
            </div>
        </div>
        <div class="form-row mt-4">
            <div class="col text-center">
                <a href="#" id="exportar" class="btn btn-info">Generar Documento (.xlsx)</a>
                <br>
                <div class="loader d-none">
                    <img src="<?= base_url('assets/frontend/img/loader.gif') ?>" width="100" >
                    <br>
                    <small>Generando Reporte (.xlsx)...</small>
                </div>
                <div class="alert-post_loader alert alert-success d-none mt-4">
                    Reporte Generado con Exito!
                </div>
            </div>
        </div>
    </form>
</div>
