<h2>Listado de Accionadores</h1>
<table class="table">
    <tr class="text-center">
        <th>Nombre</th>
        <th>Comando</th>
        <th>Apagado (min)</th>
        <th>Acción</th>
    </tr>
    <?php
        foreach ($componentes as $row){
    ?>
        <tr class="text-center">
            <td><?= $row['com_nombre'] ?></td>
            <td><?= $row['com_nombre_comando'] ?></td>
            <td><?= $row['com_tiempo_apagado'] / 60000 ?></td>
            <td><a href="#" class="btn btn-primary btn-xs btn_editar_componente" data-id="<?= $row['id_componentes'] ?>" data-tipo="<?= strtolower($row['com_tipos']) ?>">Editar</a><!--<a href="#" class="btn btn-primary btn-xs">Historico</a>--></td>
        </tr>
    <?php
        }
    ?>
</table>