<section class="patch-panel" id="panelPostulaciones">

<div class="patch-panel-header">

    <div>
        <h2>Postulaciones a vacantes</h2>

        <p>
            Consulta y administra las personas que han enviado una postulación a una vacante.
        </p>
    </div>

    <div class="patch-panel-count">
        <?php echo count($postulaciones); ?>
        <?php echo count($postulaciones) === 1 ? 'resultado' : 'resultados'; ?>
    </div>

</div>


<form
    method="GET"
    action="Admin_Postulaciones.php"
    class="patch-filter-toolbar"
    id="formFiltros"
>

    <div class="patch-search-box">

        <i class="fa fa-search"></i>

        <input
            type="text"
            name="buscar"
            id="buscar"
            value="<?php echo e($buscar); ?>"
            placeholder="Buscar por nombre, correo, teléfono o vacante..."
            autocomplete="off"
        >

    </div>


    <div class="patch-filter-group">

        <label for="filtroVacante">
            Vacante
        </label>

        <select
            name="vacante"
            id="filtroVacante"
        >

            <option value="0">
                Todas las vacantes
            </option>

            <?php if (!empty($vacantesPostulaciones)): ?>

                <?php foreach ($vacantesPostulaciones as $vacante): ?>

                    <?php
                    $vacanteId = (int)($vacante['id'] ?? 0);
                    $vacanteTitulo = trim((string)($vacante['titulo'] ?? ''));
                    ?>

                    <option
                        value="<?php echo $vacanteId; ?>"
                        <?php echo $filtroVacante === $vacanteId ? 'selected' : ''; ?>
                    >
                        <?php echo e($vacanteTitulo !== '' ? $vacanteTitulo : 'Vacante sin título'); ?>
                    </option>

                <?php endforeach; ?>

            <?php endif; ?>

        </select>

    </div>


    <div class="patch-filter-group">

        <label for="filtroEstado">
            Estado
        </label>

        <select
            name="estado"
            id="filtroEstado"
        >

            <option value="">
                Todos los estados
            </option>

            <option
                value="nueva"
                <?php echo $filtroEstado === 'nueva' ? 'selected' : ''; ?>
            >
                Por revisar
            </option>

            <option
                value="en_proceso"
                <?php echo $filtroEstado === 'en_proceso' ? 'selected' : ''; ?>
            >
                En proceso
            </option>

            <option
                value="entrevista"
                <?php echo $filtroEstado === 'entrevista' ? 'selected' : ''; ?>
            >
                Entrevista
            </option>

            <option
                value="aceptado"
                <?php echo $filtroEstado === 'aceptado' ? 'selected' : ''; ?>
            >
                Aceptado
            </option>

            <option
                value="descartado"
                <?php echo $filtroEstado === 'descartado' ? 'selected' : ''; ?>
            >
                Descartado
            </option>

        </select>

    </div>


    <button
        type="submit"
        class="patch-filter-button"
    >
        <i class="fa fa-filter"></i>
        Filtrar
    </button>


    <?php if ($buscar !== '' || $filtroVacante > 0 || $filtroEstado !== ''): ?>

        <button
            type="button"
            class="patch-filter-clear"
            onclick="limpiarFiltros()"
        >
            <i class="fa fa-xmark"></i>
            Limpiar
        </button>

    <?php endif; ?>

</form>


<?php if (!empty($postulaciones)): ?>

    <div class="patch-table-wrapper">

        <table class="patch-table">

            <thead>

                <tr>

                    <th>
                        Candidato
                    </th>

                    <th>
                        Vacante
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

                <?php foreach ($postulaciones as $postulacion): ?>

                    <?php
                    $nombrePostulante = trim((string)($postulacion['nombre_completo'] ?? ''));
                    $correoPostulante = trim((string)($postulacion['correo'] ?? ''));
                    $telefonoPostulante = trim((string)($postulacion['telefono'] ?? ''));
                    $vacantePostulacion = trim((string)($postulacion['vacante_titulo'] ?? ''));
                    $estadoPostulacion = trim((string)($postulacion['estado'] ?? 'nueva'));
                    $fechaPostulacion = $postulacion['fecha_postulacion'] ?? '';
                    $cvPostulacion = trim((string)($postulacion['cv'] ?? ''));

                    $datosPostulacion = [
                        'id' => (int)($postulacion['id'] ?? 0),
                        'id_bolsa' => (int)($postulacion['id_bolsa'] ?? 0),
                        'nombre_completo' => $nombrePostulante,
                        'correo' => $correoPostulante,
                        'telefono' => $telefonoPostulante,
                        'cv' => $cvPostulacion,
                        'comentarios' => (string)($postulacion['comentarios'] ?? ''),
                        'estado' => $estadoPostulacion,
                        'fecha_postulacion' => $fechaPostulacion,
                        'updated_at' => $postulacion['updated_at'] ?? '',
                        'vacante_titulo' => $vacantePostulacion,
                        'vacante_ubicacion' => (string)($postulacion['vacante_ubicacion'] ?? ''),
                        'vacante_jornada' => (string)($postulacion['vacante_jornada'] ?? ''),
                        'vacante_modalidad' => (string)($postulacion['vacante_modalidad'] ?? '')
                    ];

                    $jsonPostulacion = json_encode(
                        $datosPostulacion,
                        JSON_HEX_TAG |
                        JSON_HEX_APOS |
                        JSON_HEX_AMP |
                        JSON_HEX_QUOT |
                        JSON_UNESCAPED_UNICODE
                    );

                    $estadoTextoActual = estadoTexto($estadoPostulacion);
                    $estadoClaseActual = estadoClase($estadoPostulacion);
                    ?>

                    <tr>

                        <td>

                            <div class="patch-candidate">

                                <div class="patch-candidate-avatar">
                                    <?php echo e(iniciales($nombrePostulante)); ?>
                                </div>

                                <div class="patch-candidate-info">

                                    <strong>
                                        <?php echo e($nombrePostulante !== '' ? $nombrePostulante : 'Sin nombre'); ?>
                                    </strong>

                                    <span>
                                        <?php echo e($correoPostulante !== '' ? $correoPostulante : 'Sin correo'); ?>
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>

                            <div class="patch-vacancy-cell">

                                <strong>
                                    <?php echo e($vacantePostulacion !== '' ? $vacantePostulacion : 'Vacante sin título'); ?>
                                </strong>

                            </div>

                        </td>


                        <td>

                            <span class="patch-table-contact">

                                <?php if ($telefonoPostulante !== ''): ?>

                                    <i class="fa fa-phone"></i>

                                    <?php echo e($telefonoPostulante); ?>

                                <?php else: ?>

                                    <span class="patch-table-muted">
                                        Sin teléfono
                                    </span>

                                <?php endif; ?>

                            </span>

                        </td>


                        <td>

                            <span class="patch-table-date">

                                <?php echo formatearFecha($fechaPostulacion); ?>

                            </span>

                        </td>


                        <td>

                            <span class="patch-status <?php echo e($estadoClaseActual); ?>">

                                <?php echo e($estadoTextoActual); ?>

                            </span>

                        </td>


                        <td>

                            <button
                                type="button"
                                class="patch-action-button"
                                onclick='abrirPostulacion(<?php echo $jsonPostulacion; ?>)'
                                title="Ver detalle de la postulación"
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
            <i class="fa fa-users"></i>
        </div>

        <?php if ($buscar !== '' || $filtroVacante > 0 || $filtroEstado !== ''): ?>

            <h3>
                No se encontraron postulaciones
            </h3>

            <p>
                No existen postulaciones que coincidan con los filtros seleccionados.
            </p>

            <button
                type="button"
                class="patch-filter-button"
                onclick="limpiarFiltros()"
            >
                <i class="fa fa-rotate-left"></i>
                Limpiar filtros
            </button>

        <?php else: ?>

            <h3>
                No hay postulaciones
            </h3>

            <p>
                Cuando una persona se postule a una vacante, sus datos aparecerán aquí.
            </p>

        <?php endif; ?>

    </div>

<?php endif; ?>


</section>
