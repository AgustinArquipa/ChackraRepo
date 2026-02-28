<h2>Listado de Horarios: <strong><?= $componente[0]['com_nombre'] ?></strong></h1>
<?php
        if (!is_null($horarios)){
?>
            <table class="table">
                <tr class="text-center">
                    <th>Fecha</th>
                    <th>Hora</th>
                </tr>
                <?php
                        foreach ($horarios as $row){
                            $hora = str_pad($row['hor_hora'], 2, "0", STR_PAD_LEFT);
                            $minuto = str_pad($row['hor_minutos'], 2, "0", STR_PAD_LEFT);
                            $segundo = str_pad($row['hor_segundos'], 2, "0", STR_PAD_LEFT);
                            $hor_segundos = $hora . ':' . $minuto . ':' . $segundo;
                            $class = '';
                                if(date("Y-m-d") == $row['hor_fecha']){
                                    $class = 'fecha_actual';
                                }
                ?>
                                <tr class="text-center <?= $class ?>">
                                    <td><?= CI_Controller::fechaNormal($row['hor_fecha']) ?></td>
                                    <td><?= $hor_segundos ?></td>
                                </tr>
                <?php
                        }
                ?>
            </table>
<?php
        }else{
?>
            <div class="alert alert-warning">No existen horarios cargados para este componente</div>
<?php
        }
?>
