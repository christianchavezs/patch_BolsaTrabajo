<section class="patch-panel" id="panelCVGenerales">

    <div class="patch-panel-header">

        <div>

            <h2>CV generales</h2>

            <p>
                Candidatos que enviaron su CV sin seleccionar una vacante específica.
            </p>

        </div>

        <div class="patch-panel-count">

            <?php echo count($cvGenerales); ?>

            <?php echo count($cvGenerales) === 1 ? 'resultado' : 'resultados'; ?>

        </div>

    </div>


    <?php if (!empty($cvGenerales)): ?>

        <div class="patch-table-wrapper">

            <table class="patch-table">

                <thead>

                    <tr>

                        <th>
                            Candidato
                        </th>

                        <th>
                            Teléfono
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($cvGenerales as $cv): ?>

                        <?php

                        $idCV = (int)($cv['id'] ?? 0);

                        $nombreCV = trim(
                            (string)($cv['nombre_completo'] ?? '')
                        );

                        $correoCV = trim(
                            (string)($cv['correo'] ?? '')
                        );

                        $telefonoCV = trim(
                            (string)($cv['telefono'] ?? '')
                        );

                        $estadoCV = trim(
                            (string)($cv['estado'] ?? 'nueva')
                        );

                        $fechaCV = $cv['fecha_postulacion'] ?? '';

                        $archivoCV = trim(
                            (string)($cv['cv'] ?? '')
                        );

                        $estadoTextoCV = estadoTexto($estadoCV);

                        $estadoClaseCV = estadoClase($estadoCV);

                        ?>


                        <tr
                            data-postulacion-id="<?php echo $idCV; ?>"
                            data-estado="<?php echo e($estadoCV); ?>"
                        >

                            <td>

                                <div class="patch-candidate">

                                    <div class="patch-candidate-avatar">

                                        <?php
                                        echo e(
                                            iniciales($nombreCV)
                                        );
                                        ?>

                                    </div>


                                    <div class="patch-candidate-info">

                                        <strong>

                                            <?php
                                            echo e(
                                                $nombreCV !== ''
                                                    ? $nombreCV
                                                    : 'Sin nombre'
                                            );
                                            ?>

                                        </strong>


                                        <span>

                                            <?php
                                            echo e(
                                                $correoCV !== ''
                                                    ? $correoCV
                                                    : 'Sin correo'
                                            );
                                            ?>

                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <span class="patch-table-contact">

                                    <?php if ($telefonoCV !== ''): ?>

                                        <i class="fa fa-phone"></i>

                                        <?php echo e($telefonoCV); ?>

                                    <?php else: ?>

                                        <span class="patch-table-muted">

                                            Sin teléfono

                                        </span>

                                    <?php endif; ?>

                                </span>

                            </td>


                            <td>

                                <span class="patch-table-date">

                                    <?php
                                    echo formatearFecha($fechaCV);
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span
                                    class="patch-status <?php echo e($estadoClaseCV); ?>"
                                    data-estado="<?php echo e($estadoCV); ?>"
                                >

                                    <?php echo e($estadoTextoCV); ?>

                                </span>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="patch-action-button"
                                    title="Ver detalle del CV"
                                    onclick="cargarPostulacion(<?php echo $idCV; ?>)"
                                >

                                    <i class="fa fa-eye"></i>

                                    Ver detalle

                                </button>

                            </td>

                        </tr>


                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>


    <?php else: ?>


        <div class="patch-empty-state">

            <div class="patch-empty-icon">

                <i class="fa fa-file-pdf"></i>

            </div>


            <h3>

                No hay CV generales

            </h3>


            <p>

                Los CV enviados sin seleccionar una vacante específica aparecerán aquí.

            </p>

        </div>


    <?php endif; ?>

</section>
