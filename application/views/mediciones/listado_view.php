<h2>Listado de Mediciones: <strong><?= $componente[0]['com_nombre'] ?></strong></h1>
<?php
        if (!is_null($mediciones)){
?>
            <table class="table table-striped table-borderless">
                <thead class="text-center">
                        <th>ID</th>
                        <th>Temperatura</th>
                        <th>Humedad</th>
                        <th>Fecha</th>
                </thead>
                <?php
                        foreach ($mediciones as $row){
                                $fecha_unix = strtotime($row['med_fecha_alta']);
                                $fecha = date('d/m/Y H:i:s', $fecha_unix);
                                if ($row['med_temperatura_estado'] == 'alta'){
                                    $valor_ref_temperatura = $row['med_temperatura_limite_superior'];
                                }else if ($row['med_temperatura_estado'] == 'baja'){
                                    $valor_ref_temperatura = $row['med_temperatura_limite_inferior'];
                                }else if ($row['med_temperatura_estado'] == 'normal'){
                                    $valor_ref_temperatura = '';
                                }
                                if ($valor_ref_temperatura != ''){
                                    $temperatura = '<span class="'. $row['med_temperatura_estado'] .'">' . $row['med_temperatura'] . '</span> <small>(' . $valor_ref_temperatura . ')</small>';
                                }else{
                                    $temperatura = '<span class="'. $row['med_temperatura_estado'] .'">' . $row['med_temperatura'] . '</span>';
                                }

                                if ($row['med_humedad_estado'] == 'alta'){
                                    $valor_ref_humedad = $row['med_humedad_limite_superior'];
                                }else if ($row['med_humedad_estado'] == 'baja'){
                                    $valor_ref_humedad = $row['med_humedad_limite_inferior'];
                                }else if ($row['med_humedad_estado'] == 'normal'){
                                    $valor_ref_humedad = '';
                                }
                                if ($valor_ref_humedad != ''){
                                    $humedad = '<span class="'. $row['med_humedad_estado'] .'">' . $row['med_humedad'] . '</span> <small>(' . $valor_ref_humedad . ')</small>';
                                }else{
                                    $humedad = '<span class="'. $row['med_humedad_estado'] .'">' . $row['med_humedad'] . '</span>';
                                }
                                
                ?>
                                <tr class="text-center">
                                        <td><?= $row['id_mediciones'] ?></td>
                                        <td><?= $temperatura ?></td>
                                        <td><?= $humedad ?></td>
                                        <td><?= $fecha ?></td>
                                </tr>
                <?php
                        }
                ?>
            </table>
<?php
        }else{
?>
            <div class="alert alert-warning">No existen mediciones registradas para este componente</div>
<?php
        }
?>
