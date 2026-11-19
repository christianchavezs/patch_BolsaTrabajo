<?php
// Preparar lista de vacantes para el selector del modal independientemente de la vista
$listaVacantesModal = !empty($vacantesPostulaciones) ? $vacantesPostulaciones : (!empty($todasVacantes) ? $todasVacantes : []);
if (empty($listaVacantesModal) && isset($conn)) {
    $resVM = $conn->query("SELECT id, titulo, ubicacion, archivada, activo FROM patch_BolsaTrabajo ORDER BY archivada ASC, titulo ASC");
    if ($resVM) {
        while ($rvm = $resVM->fetch_assoc()) {
            $listaVacantesModal[] = $rvm;
        }
        $resVM->free();
    }
}
?>
<!-- MODAL DE EXPORTACIÓN A EXCEL -->
<div class="patch-modal" id="modalExportarExcel" aria-hidden="true">
    <div class="patch-modal-container patch-modal-exportar" role="dialog" aria-modal="true" aria-labelledby="modalTituloExportar">

        <div class="patch-modal-header">
            <div class="patch-modal-header-info">
                <h2 id="modalTituloExportar" style="display: flex; align-items: center; gap: 9px; color: #0b3b27;">
                    <i class="fa fa-file-excel" style="color: #159a5b;"></i>
                    <span>Exportar Datos a Excel</span>
                </h2>
                <p>Personaliza los filtros y criterios de información que deseas descargar.</p>
            </div>

            <button
                type="button"
                class="patch-modal-close"
                onclick="cerrarModalExportar()"
                aria-label="Cerrar modal"
            >
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <form action="api/exportar_excel.php" method="GET" target="_blank" id="formExportarExcel">
            <div class="patch-modal-body">

                <!-- TIPO DE CONTENIDO -->
                <div class="patch-modal-section">
                    <label class="patch-form-label">
                        <i class="fa fa-layer-group" style="color: #159a5b;"></i>
                        Información a exportar:
                    </label>
                    <div class="patch-export-type-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <label class="patch-export-type-card active">
                            <input type="radio" name="tipo" value="postulaciones" checked onchange="actualizarCamposExportar(this.value)">
                            <div class="patch-export-type-content">
                                <i class="fa fa-users"></i>
                                <div>
                                    <strong>Candidatos y Postulaciones</strong>
                                    <span>Listado completo de aspirantes, datos de contacto, estados y citas.</span>
                                </div>
                            </div>
                        </label>

                        <label class="patch-export-type-card">
                            <input type="radio" name="tipo" value="vacantes" onchange="actualizarCamposExportar(this.value)">
                            <div class="patch-export-type-content">
                                <i class="fa fa-briefcase"></i>
                                <div>
                                    <strong>Resumen de Vacantes</strong>
                                    <span>Métricas de candidatos recibidos, estados y vigencia por puesto.</span>
                                </div>
                            </div>
                        </label>

                        <label class="patch-export-type-card">
                            <input type="radio" name="tipo" value="reporte_general" onchange="actualizarCamposExportar(this.value)">
                            <div class="patch-export-type-content">
                                <i class="fa fa-chart-pie"></i>
                                <div>
                                    <strong>Reporte Ejecutivo Completo</strong>
                                    <span>Resumen gerencial de KPIs, conversión y desempeño por vacante.</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- FILTRO POR VACANTE -->
                <div class="patch-modal-section" id="grupoExportarVacante">
                    <label class="patch-form-label" for="exportarFiltroVacante">
                        Filtrar por Vacante:
                    </label>
                    <select name="vacante" id="exportarFiltroVacante" class="patch-input">
                        <option value="0">Todas las vacantes y solicitudes</option>
                        <option value="-1">Solo CV General (Postulaciones espontáneas)</option>
                        <?php if (!empty($listaVacantesModal)): ?>
                            <?php foreach ($listaVacantesModal as $vac): ?>
                                <?php
                                $vacId = (int)($vac['id'] ?? 0);
                                $vacTit = trim((string)($vac['titulo'] ?? ''));
                                $esArch = (int)($vac['archivada'] ?? 0) === 1;
                                ?>
                                <option value="<?php echo $vacId; ?>">
                                    <?php echo htmlspecialchars($vacTit !== '' ? $vacTit : 'Vacante #' . $vacId, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo $esArch ? ' (Archivada)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- FILTRO POR ESTADO -->
                <div class="patch-modal-section" id="grupoExportarEstado">
                    <label class="patch-form-label" for="exportarFiltroEstado">
                        Filtrar por Estado del Candidato:
                    </label>
                    <select name="estado" id="exportarFiltroEstado" class="patch-input">
                        <option value="todas">Todos los estados</option>
                        <option value="nueva">Por revisar (Nuevas)</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="entrevista">Entrevista agendada</option>
                        <option value="aceptado">Aceptado / Contratado</option>
                        <option value="descartado">Descartado</option>
                    </select>
                </div>

                <!-- FILTRO POR RANGO DE FECHAS -->
                <div class="patch-modal-section">
                    <label class="patch-form-label">
                        Rango de fechas:
                    </label>
                    <div class="patch-date-range-grid">
                        <div>
                            <span class="patch-date-sublabel">Desde:</span>
                            <input type="date" name="fecha_inicio" id="exportarFechaInicio" class="patch-input">
                        </div>
                        <div>
                            <span class="patch-date-sublabel">Hasta:</span>
                            <input type="date" name="fecha_fin" id="exportarFechaFin" class="patch-input">
                        </div>
                    </div>

                    <!-- ATAJOS RÁPIDOS DE FECHA -->
                    <div class="patch-date-shortcuts">
                        <button type="button" class="patch-date-pill" onclick="definirRangoFecha('todo')">Todo el tiempo</button>
                        <button type="button" class="patch-date-pill" onclick="definirRangoFecha('mes_actual')">Este mes</button>
                        <button type="button" class="patch-date-pill" onclick="definirRangoFecha('3_meses')">Últimos 3 meses</button>
                        <button type="button" class="patch-date-pill" onclick="definirRangoFecha('anio_actual')">Este año</button>
                    </div>
                </div>

                <!-- FORMATO DE ARCHIVO -->
                <div class="patch-modal-section" style="padding-top: 5px;">
                    <label class="patch-form-label">Formato de descarga:</label>
                    <div class="patch-format-options">
                        <label class="patch-format-chip">
                            <input type="radio" name="formato" value="excel" checked>
                            <span class="patch-format-chip-content">
                                <i class="fa fa-file-excel" style="color: #159a5b;"></i>
                                <span>Libro de Excel (.xls con formato corporativo verde Kombitec)</span>
                            </span>
                        </label>
                        <label class="patch-format-chip">
                            <input type="radio" name="formato" value="csv">
                            <span class="patch-format-chip-content">
                                <i class="fa fa-file-csv" style="color: #3b82f6;"></i>
                                <span>Archivo CSV (.csv UTF-8 estándar)</span>
                            </span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="patch-modal-footer">
                <button
                    type="button"
                    class="patch-modal-footer-button secondary"
                    onclick="cerrarModalExportar()"
                >
                    <i class="fa fa-xmark"></i>
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="patch-modal-footer-button primary"
                    id="btnConfirmarExportacion"
                >
                    <i class="fa fa-download"></i>
                    <span>Descargar archivo</span>
                </button>
            </div>
        </form>

    </div>
</div>
