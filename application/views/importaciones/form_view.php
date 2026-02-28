<h4>Nuevo Archivo</h4>
<hr>
<form class="form-horizontal" id="importacion">
    <div class="col-12">
        <select id="componente" name="componente" class="form-control">
            <?php
                foreach ($componentes as $componente){
            ?>
                    <option value="<?= $componente['id_componentes'] ?>"><?= $componente['com_nombre'] ?></option>
            <?php
                }
            ?>
        </select>
    </div>
    <div class="col-12">
        <input type="file" name="file" id="file">
    </div>
    <div class="col-12 mt-3">
        <button class="btn btn-success" id="btn_upload" type="submit">Subir</button>
    </div>
</form>  