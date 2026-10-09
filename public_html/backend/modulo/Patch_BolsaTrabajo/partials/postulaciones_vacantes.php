<section class="patch-section">

    <div class="patch-section-title">

        <div>

            <h2>Vacantes con postulaciones</h2>

            <p>
                Consulta rápidamente las vacantes que han recibido candidatos.
            </p>

        </div>

    </div>


    <?php if (!empty($vacantesPostulaciones)): ?>

        <div class="patch-vacancies-grid">

            <?php foreach ($vacantesPostulaciones as $vacante): ?>

                <?php

                $vacanteId = (int)(
                    $vacante['id'] ?? 0
                );

                $vacanteTitulo = trim(
                    (string)($vacante['titulo'] ?? '')
                );

                $vacanteUbicacion = trim(
                    (string)($vacante['ubicacion'] ?? '')
                );

                $vacanteJornada = trim(
                    (string)($vacante['tipo_jornada'] ?? '')
                );

                $vacanteModalidad = trim(
                    (string)($vacante['modalidad'] ?? '')
                );

                $totalVacante = (int)(
                    $vacante['total_postulaciones'] ?? 0
                );

                $pendientesVacante = (int)(
                    $vacante['pendientes'] ?? 0
                );

                $enProcesoVacante = (int)(
                    $vacante['en_proceso'] ?? 0
                );

                $entrevistasVacante = (int)(
                    $vacante['entrevistas'] ?? 0
                );

                $aceptadosVacante = (int)(
                    $vacante['aceptados'] ?? 0
                );

                $descartadosVacante = (int)(
                    $vacante['descartados'] ?? 0
                );

                $esArchivada = (int)(
                    $vacante['archivada'] ?? 0
                ) === 1;

                $esActiva = (int)(
                    $vacante['activo'] ?? 0
                ) === 1;


                if ($esArchivada) {

                    $estadoVacanteTexto = 'Archivada';

                    $estadoVacanteClase = 'archived';

                    $estadoVacanteIcono = 'fa-box-archive';

                } elseif ($esActiva) {

                    $estadoVacanteTexto = 'Activa';

                    $estadoVacanteClase = 'active';

                    $estadoVacanteIcono = 'fa-circle-check';

                } else {

                    $estadoVacanteTexto = 'Inactiva';

                    $estadoVacanteClase = 'inactive';

                    $estadoVacanteIcono = 'fa-circle-pause';

                }


                /*
                 * La tarjeta abre directamente el dashboard
                 * de postulaciones de la vacante seleccionada.
                 *
                 * Admin_Detalle_Postulacion.php funciona en dos modos:
                 *
                 * 1. ?id=ID
                 *    Muestra el detalle individual de una postulación.
                 *
                 * 2. ?vacante=ID
                 *    Muestra el dashboard de postulaciones
                 *    correspondiente únicamente a esa vacante.
                 */

                $urlVacantePostulaciones =
                    'Admin_Detalle_Postulacion.php?vacante=' .
                    $vacanteId;

                ?>

                <a
                    href="<?php echo e($urlVacantePostulaciones); ?>"
                    class="patch-vacancy-postulation-card"
                    data-vacante="<?php echo $vacanteId; ?>"
                    aria-label="Ver postulaciones de <?php echo e($vacanteTitulo !== '' ? $vacanteTitulo : 'vacante'); ?>"
                >

                    <div class="patch-vacancy-postulation-header">

                        <div class="patch-vacancy-postulation-info">

                            <h3>

                                <?php
                                echo e(
                                    $vacanteTitulo !== ''
                                        ? $vacanteTitulo
                                        : 'Vacante sin título'
                                );
                                ?>

                            </h3>


                            <?php if ($vacanteUbicacion !== ''): ?>

                                <p>

                                    <i class="fa fa-location-dot"></i>

                                    <?php echo e($vacanteUbicacion); ?>

                                </p>

                            <?php endif; ?>

                        </div>


                        <div class="patch-vacancy-postulation-total">

                            <strong>

                                <?php echo $totalVacante; ?>

                            </strong>


                            <span>

                                <?php echo $totalVacante === 1
                                    ? 'postulación'
                                    : 'postulaciones'; ?>

                            </span>

                        </div>

                    </div>


                    <?php if (
                        $vacanteJornada !== '' ||
                        $vacanteModalidad !== ''
                    ): ?>

                        <div class="patch-vacancy-postulation-meta">

                            <?php if ($vacanteJornada !== ''): ?>

                                <span>

                                    <i class="fa fa-clock"></i>

                                    <?php echo e($vacanteJornada); ?>

                                </span>

                            <?php endif; ?>


                            <?php if ($vacanteModalidad !== ''): ?>

                                <span>

                                    <i class="fa fa-building"></i>

                                    <?php echo e($vacanteModalidad); ?>

                                </span>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>


                    <div class="patch-vacancy-postulation-status">

                        <span
                            class="patch-vacancy-status <?php echo e($estadoVacanteClase); ?>"
                        >

                            <i
                                class="fa <?php echo e($estadoVacanteIcono); ?>"
                            ></i>

                            <?php echo e($estadoVacanteTexto); ?>

                        </span>

                    </div>


                    <div class="patch-vacancy-postulation-stats">

                        <div class="patch-vacancy-stat">

                            <span class="patch-vacancy-stat-value">

                                <?php echo $pendientesVacante; ?>

                            </span>

                            <span class="patch-vacancy-stat-label">

                                Por revisar

                            </span>

                        </div>


                        <div class="patch-vacancy-stat">

                            <span class="patch-vacancy-stat-value">

                                <?php echo $enProcesoVacante; ?>

                            </span>

                            <span class="patch-vacancy-stat-label">

                                En proceso

                            </span>

                        </div>


                        <div class="patch-vacancy-stat">

                            <span class="patch-vacancy-stat-value">

                                <?php echo $entrevistasVacante; ?>

                            </span>

                            <span class="patch-vacancy-stat-label">

                                Entrevistas

                            </span>

                        </div>


                        <div class="patch-vacancy-stat">

                            <span class="patch-vacancy-stat-value">

                                <?php echo $aceptadosVacante; ?>

                            </span>

                            <span class="patch-vacancy-stat-label">

                                Aceptados

                            </span>

                        </div>


                        <div class="patch-vacancy-stat">

                            <span class="patch-vacancy-stat-value">

                                <?php echo $descartadosVacante; ?>

                            </span>

                            <span class="patch-vacancy-stat-label">

                                Descartados

                            </span>

                        </div>

                    </div>


                    <div class="patch-vacancy-postulation-footer">

                        <span>

                            <i class="fa fa-users"></i>

                            Ver postulaciones

                        </span>


                        <i class="fa fa-arrow-right"></i>

                    </div>

                </a>

            <?php endforeach; ?>

        </div>


    <?php else: ?>

        <div class="patch-panel patch-empty-panel">

            <div class="patch-empty-state">

                <div class="patch-empty-icon">

                    <i class="fa fa-briefcase"></i>

                </div>


                <h3>

                    No hay vacantes con postulaciones

                </h3>


                <p>

                    Cuando una persona se postule a una vacante,
                    aparecerá aquí el resumen correspondiente.

                </p>

            </div>

        </div>

    <?php endif; ?>

</section>
