<?php
declare(strict_types=1);

/**
 * ============================================================
 * DASHBOARD DE REPORTES Y ESTADÍSTICAS
 * Proyecto: Patch_BolsaTrabajo (Kombitec)
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/Admin_Reportes.php
 * ============================================================
 */

require_once __DIR__ . "/../../../includes/auth.php";
requerirAdminPagina('../../../login.php');

require_once __DIR__ . "/../../../includes/config.php";

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function e($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function estadoTexto(string $estado): string {
    return match ($estado) {
        'nueva'      => 'Por revisar',
        'en_proceso' => 'En proceso',
        'entrevista' => 'Entrevista',
        'aceptado'   => 'Aceptado',
        'descartado' => 'Descartado',
        default      => ucwords(str_replace('_', ' ', $estado))
    };
}

// ============================================================
// PARÁMETROS DE FILTRADO
// ============================================================
$filtroVacante = isset($_GET['vacante']) ? (int)$_GET['vacante'] : 0;
$fechaInicio   = trim((string)($_GET['fecha_inicio'] ?? ''));
$fechaFin      = trim((string)($_GET['fecha_fin'] ?? ''));
$periodo       = trim((string)($_GET['periodo'] ?? 'todo'));

// Si se usó un atajo de período y no se enviaron fechas manuales
if ($periodo !== 'todo' && $fechaInicio === '' && $fechaFin === '') {
    $hoy = date('Y-m-d');
    if ($periodo === 'mes') {
        $fechaInicio = date('Y-m-01');
        $fechaFin    = date('Y-m-t');
    } elseif ($periodo === '3meses') {
        $fechaInicio = date('Y-m-d', strtotime('-3 months'));
        $fechaFin    = $hoy;
    } elseif ($periodo === '6meses') {
        $fechaInicio = date('Y-m-d', strtotime('-6 months'));
        $fechaFin    = $hoy;
    } elseif ($periodo === 'anio') {
        $fechaInicio = date('Y-01-01');
        $fechaFin    = date('Y-12-31');
    }
}

// ============================================================
// LISTA DE TODAS LAS VACANTES PARA EL SELECTOR
// ============================================================
$todasVacantes = [];
$sqlListaVacantes = "SELECT id, titulo, ubicacion, activo, archivada FROM patch_BolsaTrabajo ORDER BY archivada ASC, titulo ASC";
$resLV = $conn->query($sqlListaVacantes);
if ($resLV) {
    while ($v = $resLV->fetch_assoc()) {
        $todasVacantes[] = $v;
    }
    $resLV->free();
}

// ============================================================
// CONSTRUCCIÓN DE CONDICIONES WHERE PARA FILTRAR
// ============================================================
$whereP = ["1=1"];
$paramsP = [];
$typesP = "";

if ($filtroVacante > 0) {
    $whereP[] = "p.id_bolsa = ?";
    $paramsP[] = $filtroVacante;
    $typesP .= "i";
} elseif ($filtroVacante === -1) {
    $whereP[] = "p.id_bolsa IS NULL";
}

if ($fechaInicio !== '') {
    $whereP[] = "DATE(p.fecha_postulacion) >= ?";
    $paramsP[] = $fechaInicio;
    $typesP .= "s";
}
if ($fechaFin !== '') {
    $whereP[] = "DATE(p.fecha_postulacion) <= ?";
    $paramsP[] = $fechaFin;
    $typesP .= "s";
}

$whereSQL_P = implode(" AND ", $whereP);

// ============================================================
// TOTALES GENERALES DE VACANTES (INDEPENDIENTES DEL FILTRO DE FECHAS)
// ============================================================
$totalVacantesGlobal = 0;
$totalVacantesActivas = 0;
$totalVacantesInactivas = 0;
$totalVacantesArchivadas = 0;

$sqlVacTot = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN activo = 1 AND archivada = 0 THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN activo = 0 AND archivada = 0 THEN 1 ELSE 0 END) AS inactivas,
        SUM(CASE WHEN archivada = 1 THEN 1 ELSE 0 END) AS archivadas
    FROM patch_BolsaTrabajo
";
$resVT = $conn->query($sqlVacTot);
if ($resVT) {
    $rowVT = $resVT->fetch_assoc();
    $totalVacantesGlobal    = (int)($rowVT['total'] ?? 0);
    $totalVacantesActivas   = (int)($rowVT['activas'] ?? 0);
    $totalVacantesInactivas = (int)($rowVT['inactivas'] ?? 0);
    $totalVacantesArchivadas = (int)($rowVT['archivadas'] ?? 0);
    $resVT->free();
}

// ============================================================
// RESUMEN KPI DE POSTULACIONES (CON FILTROS APLICADOS)
// ============================================================
$totalPostulaciones = 0;
$totalNuevas = 0;
$totalEnProceso = 0;
$totalEntrevistas = 0;
$totalAceptados = 0;
$totalDescartados = 0;
$totalCVGenerales = 0;

$sqlResumen = "
    SELECT
        COUNT(p.id) AS total,
        SUM(CASE WHEN p.estado = 'nueva' THEN 1 ELSE 0 END) AS nuevas,
        SUM(CASE WHEN p.estado = 'en_proceso' THEN 1 ELSE 0 END) AS en_proceso,
        SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS entrevistas,
        SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados,
        SUM(CASE WHEN p.estado = 'descartado' THEN 1 ELSE 0 END) AS descartados,
        SUM(CASE WHEN p.id_bolsa IS NULL THEN 1 ELSE 0 END) AS cv_generales
    FROM patch_postulaciones p
    WHERE {$whereSQL_P}
";

$stmtR = $conn->prepare($sqlResumen);
if (!empty($paramsP)) {
    $stmtR->bind_param($typesP, ...$paramsP);
}
$stmtR->execute();
$resR = $stmtR->get_result();
if ($rowR = $resR->fetch_assoc()) {
    $totalPostulaciones = (int)($rowR['total'] ?? 0);
    $totalNuevas        = (int)($rowR['nuevas'] ?? 0);
    $totalEnProceso     = (int)($rowR['en_proceso'] ?? 0);
    $totalEntrevistas   = (int)($rowR['entrevistas'] ?? 0);
    $totalAceptados     = (int)($rowR['aceptados'] ?? 0);
    $totalDescartados   = (int)($rowR['descartados'] ?? 0);
    $totalCVGenerales   = (int)($rowR['cv_generales'] ?? 0);
}
$stmtR->close();

$enProcesoTotal = $totalEnProceso + $totalEntrevistas;
$tasaConversion = $totalPostulaciones > 0 ? round(($totalAceptados / $totalPostulaciones) * 100, 1) : 0.0;
$tasaEntrevistas = $totalPostulaciones > 0 ? round(($totalEntrevistas / $totalPostulaciones) * 100, 1) : 0.0;

// ============================================================
// TOP 6 VACANTES CON MÁS POSTULACIONES
// ============================================================
$topVacantes = [];
$sqlTop = "
    SELECT
        COALESCE(b.titulo, 'CV General (Espontánea)') AS titulo_vacante,
        COUNT(p.id) AS total_postulaciones,
        SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados
    FROM patch_postulaciones p
    LEFT JOIN patch_BolsaTrabajo b ON b.id = p.id_bolsa
    WHERE {$whereSQL_P}
    GROUP BY p.id_bolsa, b.titulo
    ORDER BY total_postulaciones DESC
    LIMIT 6
";

$stmtTop = $conn->prepare($sqlTop);
if (!empty($paramsP)) {
    $stmtTop->bind_param($typesP, ...$paramsP);
}
$stmtTop->execute();
$resTop = $stmtTop->get_result();
while ($rowT = $resTop->fetch_assoc()) {
    $topVacantes[] = $rowT;
}
$stmtTop->close();

// ============================================================
// TENDENCIA MENSUAL (HISTORIAL DE POSTULACIONES)
// ============================================================
$tendencia = [];
$sqlTendencia = "
    SELECT
        DATE_FORMAT(p.fecha_postulacion, '%Y-%m') AS periodo_mes,
        DATE_FORMAT(p.fecha_postulacion, '%b %Y') AS etiqueta_mes,
        COUNT(p.id) AS total,
        SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados,
        SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS entrevistas
    FROM patch_postulaciones p
    WHERE {$whereSQL_P}
    GROUP BY periodo_mes, etiqueta_mes
    ORDER BY periodo_mes ASC
    LIMIT 12
";

$stmtTend = $conn->prepare($sqlTendencia);
if (!empty($paramsP)) {
    $stmtTend->bind_param($typesP, ...$paramsP);
}
$stmtTend->execute();
$resTend = $stmtTend->get_result();
while ($rowTend = $resTend->fetch_assoc()) {
    $tendencia[] = $rowTend;
}
$stmtTend->close();

// ============================================================
// DISTRIBUCIÓN POR MODALIDAD
// ============================================================
$modalidades = [];
$sqlMod = "
    SELECT
        COALESCE(b.modalidad, 'CV General') AS modalidad,
        COUNT(p.id) AS total
    FROM patch_postulaciones p
    LEFT JOIN patch_BolsaTrabajo b ON b.id = p.id_bolsa
    WHERE {$whereSQL_P}
    GROUP BY modalidad
    ORDER BY total DESC
";

$stmtMod = $conn->prepare($sqlMod);
if (!empty($paramsP)) {
    $stmtMod->bind_param($typesP, ...$paramsP);
}
$stmtMod->execute();
$resMod = $stmtMod->get_result();
while ($rowM = $resMod->fetch_assoc()) {
    $modalidades[] = $rowM;
}
$stmtMod->close();

// ============================================================
// DESGLOSE COMPLETO POR VACANTE
// ============================================================
$desgloseVacantes = [];
$sqlDesglose = "
    SELECT
        b.id,
        b.titulo,
        b.ubicacion,
        b.tipo_jornada,
        b.modalidad,
        b.activo,
        b.archivada,
        COUNT(p.id) AS total_postulaciones,
        SUM(CASE WHEN p.estado = 'nueva' THEN 1 ELSE 0 END) AS nuevas,
        SUM(CASE WHEN p.estado = 'en_proceso' THEN 1 ELSE 0 END) AS en_proceso,
        SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS entrevistas,
        SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados,
        SUM(CASE WHEN p.estado = 'descartado' THEN 1 ELSE 0 END) AS descartados
    FROM patch_BolsaTrabajo b
    LEFT JOIN patch_postulaciones p ON p.id_bolsa = b.id
    " . ($fechaInicio !== '' ? " AND DATE(p.fecha_postulacion) >= '{$conn->real_escape_string($fechaInicio)}'" : "") . "
    " . ($fechaFin !== '' ? " AND DATE(p.fecha_postulacion) <= '{$conn->real_escape_string($fechaFin)}'" : "") . "
    WHERE " . ($filtroVacante > 0 ? "b.id = " . (int)$filtroVacante : "1=1") . "
    GROUP BY b.id, b.titulo, b.ubicacion, b.tipo_jornada, b.modalidad, b.activo, b.archivada
    ORDER BY total_postulaciones DESC, b.titulo ASC
";

$resDesglose = $conn->query($sqlDesglose);
if ($resDesglose) {
    while ($rowD = $resDesglose->fetch_assoc()) {
        $desgloseVacantes[] = $rowD;
    }
    $resDesglose->free();
}

// Desglose especial de CV General
$cvGeneralRow = null;
if ($filtroVacante === 0 || $filtroVacante === -1) {
    $sqlCVG = "
        SELECT
            'CV General' AS titulo,
            'Nacional / Remoto' AS ubicacion,
            'Flexible' AS tipo_jornada,
            'CV General' AS modalidad,
            1 AS activo,
            0 AS archivada,
            COUNT(p.id) AS total_postulaciones,
            SUM(CASE WHEN p.estado = 'nueva' THEN 1 ELSE 0 END) AS nuevas,
            SUM(CASE WHEN p.estado = 'en_proceso' THEN 1 ELSE 0 END) AS en_proceso,
            SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS entrevistas,
            SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados,
            SUM(CASE WHEN p.estado = 'descartado' THEN 1 ELSE 0 END) AS descartados
        FROM patch_postulaciones p
        WHERE p.id_bolsa IS NULL
        " . ($fechaInicio !== '' ? " AND DATE(p.fecha_postulacion) >= '{$conn->real_escape_string($fechaInicio)}'" : "") . "
        " . ($fechaFin !== '' ? " AND DATE(p.fecha_postulacion) <= '{$conn->real_escape_string($fechaFin)}'" : "") . "
    ";
    $resCVG = $conn->query($sqlCVG);
    if ($resCVG) {
        $cvGeneralRow = $resCVG->fetch_assoc();
        $resCVG->free();
    }
}

// Estructura de datos JSON para Chart.js
$datosJS = [
    'estados' => [
        'labels' => ['Por revisar', 'En proceso', 'Entrevista', 'Aceptado', 'Descartado'],
        'data'   => [$totalNuevas, $totalEnProceso, $totalEntrevistas, $totalAceptados, $totalDescartados],
        'colors' => ['#f59e0b', '#3b82f6', '#8b5cf6', '#159a5b', '#ef4444']
    ],
    'topVacantes' => [
        'labels' => array_map(fn($v) => mb_substr($v['titulo_vacante'], 0, 24) . (mb_strlen($v['titulo_vacante']) > 24 ? '...' : ''), $topVacantes),
        'data'   => array_map(fn($v) => (int)$v['total_postulaciones'], $topVacantes),
        'aceptados' => array_map(fn($v) => (int)$v['aceptados'], $topVacantes)
    ],
    'tendencia' => [
        'labels' => array_map(fn($t) => $t['etiqueta_mes'] ?: $t['periodo_mes'], $tendencia),
        'totales' => array_map(fn($t) => (int)$t['total'], $tendencia),
        'aceptados' => array_map(fn($t) => (int)$t['aceptados'], $tendencia),
        'entrevistas' => array_map(fn($t) => (int)$t['entrevistas'], $tendencia)
    ],
    'modalidades' => [
        'labels' => array_map(fn($m) => $m['modalidad'] ?: 'Sin especificar', $modalidades),
        'data'   => array_map(fn($m) => (int)$m['total'], $modalidades),
        'colors' => ['#12834d', '#22b86a', '#39c97d', '#62d995', '#0f633d', '#94a3b8']
    ]
];

$urlImprimir = "Admin_Reporte_Imprimir.php?" . http_build_query([
    'vacante'      => $filtroVacante,
    'fecha_inicio' => $fechaInicio,
    'fecha_fin'    => $fechaFin,
    'periodo'      => $periodo
]);

$title = "Reportes y Estadísticas | Kombitec";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <meta name="description" content="Dashboard de reportes, métricas y estadísticas de selección y reclutamiento para Kombitec">

    <!-- Fuentes y estilos -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Admin_Reportes.css">
    <link rel="stylesheet" href="css/Admin_Postulaciones.css">

    <!-- Chart.js para visualización moderna de datos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="patch-reportes-wrapper">

        <!-- MENÚ DE USUARIO -->
        <details class="patch-user-menu" id="patchUserMenu">
            <summary aria-label="Abrir menú de usuario" aria-haspopup="true">
                <span class="patch-user-avatar">
                    <i class="fa fa-user" aria-hidden="true"></i>
                </span>
                <span class="patch-user-label"><?php echo e(adminNombre()); ?></span>
                <i class="fa fa-chevron-down patch-user-arrow" aria-hidden="true"></i>
            </summary>
            <div class="patch-user-dropdown">
                <div class="patch-user-info">
                    <strong><?php echo e(adminNombre()); ?></strong>
                    <span><?php echo e(adminCorreo() !== '' ? adminCorreo() : 'Sesión activa'); ?></span>
                </div>
                <div class="patch-user-divider"></div>
                <a href="../../../cerrar_sesion.php">
                    <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </details>

        <div class="patch-reportes-container">

            <!-- BARRA DE NAVEGACIÓN Y ACCIONES SUPERIORES -->
            <div class="patch-reportes-actions">
                <div class="patch-actions-left">
                    <a href="Admin_Postulaciones.php" class="patch-reportes-btn">
                        <i class="fa fa-arrow-left"></i>
                        Postulaciones
                    </a>
                    <a href="Admin_BolsaTrabajo.php" class="patch-reportes-btn">
                        <i class="fa fa-briefcase"></i>
                        Vacantes
                    </a>
                    <a href="Admin_Plantillas_Correo.php" class="patch-reportes-btn">
                        <i class="fa fa-envelope-open-text"></i>
                        Plantillas
                    </a>
                </div>

                <div class="patch-actions-right">
                    <button type="button" class="patch-btn-export-excel" onclick="abrirModalExportar('postulaciones')">
                        <i class="fa fa-file-excel"></i>
                        Exportar a Excel
                    </button>
                    <a href="<?php echo e($urlImprimir); ?>" target="_blank" class="patch-btn-pdf" title="Abrir versión ejecutiva para imprimir o guardar en PDF">
                        <i class="fa fa-file-pdf"></i>
                        Exportar / Imprimir PDF
                    </a>
                </div>
            </div>

            <!-- ENCABEZADO CON DEGRADADO CORPORATIVO VERDE KOMBITEC -->
            <div class="patch-reportes-header">
                <div class="patch-reportes-header-content">
                    <div>
                        <div class="patch-breadcrumb">
                            <a href="Admin_BolsaTrabajo.php">Bolsa de Trabajo</a>
                            <i class="fa fa-angle-right"></i>
                            <a href="Admin_Postulaciones.php">Postulaciones</a>
                            <i class="fa fa-angle-right"></i>
                            <span>Reportes y Estadísticas</span>
                        </div>
                        <h1>
                            <i class="fa fa-chart-pie"></i>
                            Reportes y Estadísticas de Selección
                        </h1>
                        <p>
                            Analiza en tiempo real las métricas de atracción de talento, el rendimiento de las vacantes activas y el embudo de conversión de los candidatos en Kombitec.
                        </p>
                    </div>

                    <div class="patch-reportes-header-icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <!-- BARRA DE FILTROS DEL DASHBOARD -->
            <div class="patch-filter-card">
                <form method="GET" action="Admin_Reportes.php" class="patch-filter-form" id="formFiltroReportes">
                    <div class="patch-filter-item">
                        <label for="filtroVacanteSelect"><i class="fa fa-briefcase"></i> Vacante</label>
                        <select name="vacante" id="filtroVacanteSelect" class="patch-filter-select">
                            <option value="0" <?php echo $filtroVacante === 0 ? 'selected' : ''; ?>>Todas las vacantes y solicitudes</option>
                            <option value="-1" <?php echo $filtroVacante === -1 ? 'selected' : ''; ?>>Solo CV General (Postulaciones espontáneas)</option>
                            <optgroup label="Vacantes publicadas">
                                <?php foreach ($todasVacantes as $tv): ?>
                                    <option value="<?php echo $tv['id']; ?>" <?php echo $filtroVacante === (int)$tv['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($tv['titulo']); ?> <?php echo !empty($tv['ubicacion']) ? '(' . e($tv['ubicacion']) . ')' : ''; ?> <?php echo $tv['archivada'] ? '[Archivada]' : ($tv['activo'] ? '[Activa]' : '[Inactiva]'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="patch-filter-item">
                        <label for="filtroFechaInicio"><i class="fa fa-calendar-alt"></i> Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="filtroFechaInicio" class="patch-filter-input" value="<?php echo e($fechaInicio); ?>">
                    </div>

                    <div class="patch-filter-item">
                        <label for="filtroFechaFin"><i class="fa fa-calendar-check"></i> Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="filtroFechaFin" class="patch-filter-input" value="<?php echo e($fechaFin); ?>">
                    </div>

                    <div class="patch-filter-item">
                        <button type="submit" class="patch-btn-filter">
                            <i class="fa fa-filter"></i>
                            Aplicar
                        </button>
                    </div>

                    <div class="patch-filter-item">
                        <a href="Admin_Reportes.php" class="patch-btn-reset" title="Restablecer filtros">
                            <i class="fa fa-undo"></i>
                            Limpiar
                        </a>
                    </div>
                </form>

                <!-- ATAJOS RÁPIDOS DE FECHA -->
                <div class="patch-period-pills">
                    <span style="font-size: 11.5px; font-weight: 700; color: var(--green-950); display: flex; align-items: center; gap: 5px;">
                        <i class="fa fa-clock"></i> Período rápido:
                    </span>
                    <a href="Admin_Reportes.php?periodo=todo<?php echo $filtroVacante ? '&vacante=' . $filtroVacante : ''; ?>" class="patch-period-pill <?php echo $periodo === 'todo' && $fechaInicio === '' ? 'active' : ''; ?>">Todo</a>
                    <a href="Admin_Reportes.php?periodo=mes<?php echo $filtroVacante ? '&vacante=' . $filtroVacante : ''; ?>" class="patch-period-pill <?php echo $periodo === 'mes' ? 'active' : ''; ?>">Este mes</a>
                    <a href="Admin_Reportes.php?periodo=3meses<?php echo $filtroVacante ? '&vacante=' . $filtroVacante : ''; ?>" class="patch-period-pill <?php echo $periodo === '3meses' ? 'active' : ''; ?>">Últimos 3 meses</a>
                    <a href="Admin_Reportes.php?periodo=6meses<?php echo $filtroVacante ? '&vacante=' . $filtroVacante : ''; ?>" class="patch-period-pill <?php echo $periodo === '6meses' ? 'active' : ''; ?>">Últimos 6 meses</a>
                    <a href="Admin_Reportes.php?periodo=anio<?php echo $filtroVacante ? '&vacante=' . $filtroVacante : ''; ?>" class="patch-period-pill <?php echo $periodo === 'anio' ? 'active' : ''; ?>">Este año</a>
                </div>
            </div>

            <!-- GRID DE TARJETAS KPI EJECUTIVAS -->
            <div class="patch-kpi-grid">
                <!-- KPI 1: TOTAL POSTULACIONES -->
                <div class="patch-kpi-card">
                    <div class="patch-kpi-header">
                        <div class="patch-kpi-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <span class="patch-kpi-pill">Candidatos</span>
                    </div>
                    <div class="patch-kpi-value"><?php echo number_format($totalPostulaciones); ?></div>
                    <div class="patch-kpi-label">Total de Postulaciones</div>
                    <div class="patch-kpi-footer">
                        <i class="fa fa-check-circle" style="color: var(--green-600);"></i>
                        <span><?php echo $totalNuevas; ?> nuevas por revisar</span>
                    </div>
                </div>

                <!-- KPI 2: EN PROCESO Y ENTREVISTAS -->
                <div class="patch-kpi-card purple">
                    <div class="patch-kpi-header">
                        <div class="patch-kpi-icon">
                            <i class="fa fa-calendar-check"></i>
                        </div>
                        <span class="patch-kpi-pill" style="background:#ede9fe; color:#7c3aed;">En seguimiento</span>
                    </div>
                    <div class="patch-kpi-value"><?php echo number_format($enProcesoTotal); ?></div>
                    <div class="patch-kpi-label">Candidatos Activos</div>
                    <div class="patch-kpi-footer">
                        <i class="fa fa-user-clock" style="color: #8b5cf6;"></i>
                        <span><?php echo $totalEntrevistas; ?> en etapa de entrevista (<?php echo $tasaEntrevistas; ?>%)</span>
                    </div>
                </div>

                <!-- KPI 3: CONTRATACIONES Y TASA DE SELECCIÓN -->
                <div class="patch-kpi-card">
                    <div class="patch-kpi-header">
                        <div class="patch-kpi-icon" style="background: #dcfce7; color: #15803d;">
                            <i class="fa fa-user-check"></i>
                        </div>
                        <span class="patch-kpi-pill" style="background: #dcfce7; color: #166534;">Éxito</span>
                    </div>
                    <div class="patch-kpi-value" style="color: #15803d;"><?php echo number_format($totalAceptados); ?></div>
                    <div class="patch-kpi-label">Candidatos Aceptados</div>
                    <div class="patch-kpi-footer">
                        <i class="fa fa-percentage" style="color: #159a5b;"></i>
                        <span>Tasa de contratación: <strong><?php echo $tasaConversion; ?>%</strong></span>
                    </div>
                </div>

                <!-- KPI 4: VACANTES ACTIVAS -->
                <div class="patch-kpi-card blue">
                    <div class="patch-kpi-header">
                        <div class="patch-kpi-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <span class="patch-kpi-pill" style="background: #dbeafe; color: #1d4ed8;">Disponibilidad</span>
                    </div>
                    <div class="patch-kpi-value" style="color: #1e3a8a;"><?php echo number_format($totalVacantesActivas); ?></div>
                    <div class="patch-kpi-label">Vacantes Publicadas Activas</div>
                    <div class="patch-kpi-footer">
                        <i class="fa fa-layer-group" style="color: #2563eb;"></i>
                        <span><?php echo $totalVacantesGlobal; ?> vacantes registradas en total</span>
                    </div>
                </div>
            </div>

            <!-- GRID DE GRÁFICOS (CHART.JS) -->
            <div class="patch-charts-grid">
                <!-- GRÁFICO 1: EMBUDO POR ESTADO -->
                <div class="patch-chart-card">
                    <div class="patch-chart-title">
                        <h3>
                            <i class="fa fa-pie-chart"></i>
                            Distribución por Estado de Candidatos
                        </h3>
                        <span>Proceso de selección</span>
                    </div>
                    <div class="patch-chart-container" style="height: 300px;">
                        <canvas id="chartEstadoPostulantes"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 2: TOP VACANTES -->
                <div class="patch-chart-card">
                    <div class="patch-chart-title">
                        <h3>
                            <i class="fa fa-ranking-star"></i>
                            Top Vacantes con Mayor Demanda
                        </h3>
                        <span>Por volumen de postulaciones</span>
                    </div>
                    <div class="patch-chart-container" style="height: 300px;">
                        <canvas id="chartTopVacantes"></canvas>
                    </div>
                </div>
            </div>

            <!-- SEGUNDA FILA DE GRÁFICOS -->
            <div class="patch-charts-grid">
                <!-- GRÁFICO 3: TENDENCIA TEMPORAL -->
                <div class="patch-chart-card">
                    <div class="patch-chart-title">
                        <h3>
                            <i class="fa fa-chart-line"></i>
                            Evolución y Flujo de Postulaciones
                        </h3>
                        <span>Historial mensual</span>
                    </div>
                    <div class="patch-chart-container" style="height: 280px;">
                        <canvas id="chartTendenciaMensual"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 4: MODALIDAD DE TRABAJO -->
                <div class="patch-chart-card">
                    <div class="patch-chart-title">
                        <h3>
                            <i class="fa fa-building"></i>
                            Distribución por Modalidad de Trabajo
                        </h3>
                        <span>Presencial / Híbrido / Remoto</span>
                    </div>
                    <div class="patch-chart-container" style="height: 280px;">
                        <canvas id="chartModalidades"></canvas>
                    </div>
                </div>
            </div>

            <!-- TABLA COMPLETA DE RENDIMIENTO POR VACANTE -->
            <div class="patch-table-card">
                <div class="patch-table-header-box">
                    <div>
                        <h3>
                            <i class="fa fa-table"></i>
                            Rendimiento y Métricas por Vacante
                        </h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">
                            Desglose cuantitativo por vacante: volumen de candidatos, distribución de estados y tasa de conversión efectiva.
                        </p>
                    </div>
                    <div>
                        <button type="button" class="patch-reportes-btn" onclick="abrirModalExportar('vacantes')">
                            <i class="fa fa-download"></i>
                            Exportar Vacantes
                        </button>
                    </div>
                </div>

                <div class="patch-table-responsive">
                    <table class="patch-report-table">
                        <thead>
                            <tr>
                                <th style="width: 280px;">Título de la Vacante</th>
                                <th>Ubicación</th>
                                <th>Modalidad</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center; width: 140px;">Postulaciones</th>
                                <th style="text-align: center;">Nuevas</th>
                                <th style="text-align: center;">En Proceso</th>
                                <th style="text-align: center;">Entrevista</th>
                                <th style="text-align: center; color: #15803d;">Aceptados</th>
                                <th style="text-align: center; color: #b91c1c;">Descartados</th>
                                <th style="text-align: center;">Conversión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cvGeneralRow && (int)$cvGeneralRow['total_postulaciones'] > 0): ?>
                                <?php
                                $totalG = (int)$cvGeneralRow['total_postulaciones'];
                                $pctG = $totalPostulaciones > 0 ? round(($totalG / $totalPostulaciones) * 100, 1) : 0;
                                $tasaG = $totalG > 0 ? round(((int)$cvGeneralRow['aceptados'] / $totalG) * 100, 1) : 0;
                                ?>
                                <tr style="background-color: #fafcfb;">
                                    <td>
                                        <strong style="color: var(--green-950);"><i class="fa fa-inbox" style="color: var(--green-600); margin-right: 6px;"></i>CV General / Espontánea</strong>
                                        <div style="font-size: 11.5px; color: var(--text-muted);">Solicitudes abiertas sin vacante específica</div>
                                    </td>
                                    <td>Nacional / Remoto</td>
                                    <td>General</td>
                                    <td style="text-align: center;">
                                        <span class="patch-badge-sm patch-badge-active"><i class="fa fa-circle" style="font-size: 7px;"></i> Activa</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong><?php echo $totalG; ?></strong>
                                        <div class="patch-prog-bar-wrapper" title="<?php echo $pctG; ?>% del total general">
                                            <div class="patch-prog-bar" style="width: <?php echo min(100, $pctG); ?>%;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #b45309;"><?php echo (int)$cvGeneralRow['nuevas']; ?></td>
                                    <td style="text-align: center; font-weight: 600; color: #1d4ed8;"><?php echo (int)$cvGeneralRow['en_proceso']; ?></td>
                                    <td style="text-align: center; font-weight: 600; color: #6d28d9;"><?php echo (int)$cvGeneralRow['entrevistas']; ?></td>
                                    <td style="text-align: center; font-weight: 700; color: #15803d; background: #f0fdf4;"><?php echo (int)$cvGeneralRow['aceptados']; ?></td>
                                    <td style="text-align: center; font-weight: 600; color: #b91c1c;"><?php echo (int)$cvGeneralRow['descartados']; ?></td>
                                    <td style="text-align: center; font-weight: 700; color: var(--green-900);">
                                        <?php echo $tasaG; ?>%
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if (empty($desgloseVacantes)): ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                        No se encontraron vacantes para los filtros seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($desgloseVacantes as $dv): ?>
                                    <?php
                                    $totV = (int)$dv['total_postulaciones'];
                                    $pctV = $totalPostulaciones > 0 ? round(($totV / $totalPostulaciones) * 100, 1) : 0;
                                    $tasaV = $totV > 0 ? round(((int)$dv['aceptados'] / $totV) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="Admin_Postulaciones.php?vacante=<?php echo $dv['id']; ?>" style="font-weight: 700; color: var(--green-950); text-decoration: none;" title="Ver postulaciones de esta vacante">
                                                <?php echo e($dv['titulo']); ?>
                                            </a>
                                            <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo e($dv['tipo_jornada'] ?: 'Jornada completa'); ?></div>
                                        </td>
                                        <td><?php echo e($dv['ubicacion'] ?: 'No especificada'); ?></td>
                                        <td><?php echo e($dv['modalidad'] ?: 'Presencial'); ?></td>
                                        <td style="text-align: center;">
                                            <?php if ($dv['archivada']): ?>
                                                <span class="patch-badge-sm patch-badge-archived"><i class="fa fa-archive"></i> Archivada</span>
                                            <?php elseif ($dv['activo']): ?>
                                                <span class="patch-badge-sm patch-badge-active"><i class="fa fa-circle" style="font-size: 7px;"></i> Activa</span>
                                            <?php else: ?>
                                                <span class="patch-badge-sm patch-badge-inactive"><i class="fa fa-pause-circle"></i> Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <strong><?php echo $totV; ?></strong>
                                            <div class="patch-prog-bar-wrapper" title="<?php echo $pctV; ?>% del total">
                                                <div class="patch-prog-bar" style="width: <?php echo min(100, $pctV); ?>%;"></div>
                                            </div>
                                        </td>
                                        <td style="text-align: center; font-weight: 600; color: #b45309;"><?php echo (int)$dv['nuevas']; ?></td>
                                        <td style="text-align: center; font-weight: 600; color: #1d4ed8;"><?php echo (int)$dv['en_proceso']; ?></td>
                                        <td style="text-align: center; font-weight: 600; color: #6d28d9;"><?php echo (int)$dv['entrevistas']; ?></td>
                                        <td style="text-align: center; font-weight: 700; color: #15803d; background: #f0fdf4;"><?php echo (int)$dv['aceptados']; ?></td>
                                        <td style="text-align: center; font-weight: 600; color: #b91c1c;"><?php echo (int)$dv['descartados']; ?></td>
                                        <td style="text-align: center; font-weight: 700; color: var(--green-900);">
                                            <?php echo $tasaV; ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /patch-reportes-container -->
    </div><!-- /patch-reportes-wrapper -->

    <!-- INCLUSIÓN DEL MODAL DE EXPORTACIÓN A EXCEL -->
    <?php include __DIR__ . '/partials/modal_exportar_excel.php'; ?>

    <!-- DATOS EMBEBIDOS PARA CHART.JS -->
    <script id="datosReportesJSON" type="application/json">
        <?php echo json_encode($datosJS, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>
    </script>

    <!-- SCRIPTS -->
    <script src="js/exportar-excel.js"></script>
    <script src="js/reportes-admin.js"></script>
</body>
</html>
