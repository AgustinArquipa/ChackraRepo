<h2>Listado de Sensores</h1>
<table class="table">
    <tr class="text-center">
        <th>Nombre</th>
        <th>Comando</th>
        <th>Temp. Inferior (ºC)</th>
        <th>Temp. Superior (ºC)</th>
        <th>Hum. Inferior (%)</th>
        <th>Hum. Superior (%)</th>
        <th>Cal. Temp.</th>
        <th>Cal. Hum.</th>
        <th>Acción</th>
    </tr>
    <?php
        foreach ($componentes as $row){
    ?>
        <tr class="text-center">
            <td><?= $row['com_nombre'] ?></td>
            <td><?= $row['com_nombre_comando'] ?></td>
            <td><?= $row['com_limite_inferior_temperatura'] ?></td>
            <td><?= $row['com_limite_superior_temperatura'] ?></td>
            <td><?= $row['com_limite_inferior_humedad'] ?></td>
            <td><?= $row['com_limite_superior_humedad'] ?></td>
            <td><?= $row['com_calibracion_temperatura'] ?></td>
            <td><?= $row['com_calibracion_humedad'] ?></td>
            <td><a href="#" class="btn btn-primary btn-xs btn_editar_componente" data-id="<?= $row['id_componentes'] ?>" data-tipo="<?= strtolower($row['com_tipos']) ?>">Editar</a><!--<a href="#" class="btn btn-primary btn-xs">Historico</a>--></td>
        </tr>
    <?php
        }
    ?>
</table>