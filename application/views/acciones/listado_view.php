<h2>Listado de Acciones: <strong><?= $componente[0]['com_nombre'] ?></strong></h1>
<?php
        if (!is_null($acciones)){
?>
            <table class="table table-striped table-borderless">
                <thead class="text-center">
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Fecha y Hora</th>
                </thead>
                <?php
                        foreach ($acciones as $row){
                                $fecha_unix = strtotime($row['acc_fecha_alta']);
                                $fecha = date('d/m/Y H:i:s', $fecha_unix);
                ?>
                                <tr class="text-center">
                                        <td><?= $row['id_acciones'] ?></td>
                                        <td><span class="<?= strtolower($row['acc_tipos']) ?>"><?= $row['acc_tipos'] ?></span></td>
                                        <td><?= $fecha ?></td>
                                </tr>
                <?php
                        }
                ?>
            </table>
<?php
        }else{
?>
            <div class="alert alert-warning">No existen acciones registradas para este componente</div>
<?php
        }
?>
