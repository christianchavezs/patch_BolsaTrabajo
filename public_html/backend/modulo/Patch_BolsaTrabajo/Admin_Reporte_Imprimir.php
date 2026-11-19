<?php
declare(strict_types=1);

/**
 * ============================================================
 * VISTA EJECUTIVA PARA IMPRESIÓN Y DESCARGA EN PDF
 * Proyecto: Patch_BolsaTrabajo (Kombitec)
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/Admin_Reporte_Imprimir.php
 * ============================================================
 */

require_once __DIR__ . "/../../../includes/auth.php";
requerirAdminPagina('../../../login.php');

require_once __DIR__ . "/../../../includes/config.php";

header('Content-Type: text/html; charset=utf-8');

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

// Parámetros
$filtroVacante = isset($_GET['vacante']) ? (int)$_GET['vacante'] : 0;
$fechaInicio   = trim((string)($_GET['fecha_inicio'] ?? ''));
$fechaFin      = trim((string)($_GET['fecha_fin'] ?? ''));
$periodo       = trim((string)($_GET['periodo'] ?? 'todo'));
$autoPrint     = isset($_GET['autoprint']) && $_GET['autoprint'] === '1';

if ($periodo !== 'todo' && $fechaInicio === '' && $fechaFin === '') {
    $hoy = date('Y-m-d');
    if ($periodo === 'mes') {
        $fechaInicio = date('Y-m-01');
        $fechaFin    = date('Y-m-t');
    } elseif ($periodo === '3meses') {
        $fechaInicio = date('Y-m-d', strtotime('-3 months'));
        $fechaFin    = $hoy;
    } elseif ($periodo === 'anio') {
        $fechaInicio = date('Y-01-01');
        $fechaFin    = date('Y-12-31');
    }
}

// Información de la vacante específica si aplica
$nombreVacanteFiltro = "Todas las vacantes y solicitudes";
if ($filtroVacante > 0) {
    $stmtV = $conn->prepare("SELECT titulo, ubicacion, modalidad FROM patch_BolsaTrabajo WHERE id = ? LIMIT 1");
    if ($stmtV) {
        $stmtV->bind_param('i', $filtroVacante);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        if ($rowV = $resV->fetch_assoc()) {
            $nombreVacanteFiltro = $rowV['titulo'] . (!empty($rowV['ubicacion']) ? ' (' . $rowV['ubicacion'] . ')' : '');
        }
        $stmtV->close();
    }
} elseif ($filtroVacante === -1) {
    $nombreVacanteFiltro = "Solo CV General (Postulaciones espontáneas)";
}

// Filtros SQL
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

// Métricas de Postulaciones
$sqlResumen = "
    SELECT
        COUNT(p.id) AS total,
        SUM(CASE WHEN p.estado = 'nueva' THEN 1 ELSE 0 END) AS nuevas,
        SUM(CASE WHEN p.estado = 'en_proceso' THEN 1 ELSE 0 END) AS en_proceso,
        SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS entrevistas,
        SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS aceptados,
        SUM(CASE WHEN p.estado = 'descartado' THEN 1 ELSE 0 END) AS descartados
    FROM patch_postulaciones p
    WHERE {$whereSQL_P}
";
$stmtR = $conn->prepare($sqlResumen);
if (!empty($paramsP)) {
    $stmtR->bind_param($typesP, ...$paramsP);
}
$stmtR->execute();
$resR = $stmtR->get_result();
$resumen = $resR->fetch_assoc() ?: [];
$stmtR->close();

$totalPostulaciones = (int)($resumen['total'] ?? 0);
$totalNuevas        = (int)($resumen['nuevas'] ?? 0);
$totalEnProceso     = (int)($resumen['en_proceso'] ?? 0);
$totalEntrevistas   = (int)($resumen['entrevistas'] ?? 0);
$totalAceptados     = (int)($resumen['aceptados'] ?? 0);
$totalDescartados   = (int)($resumen['descartados'] ?? 0);

$tasaConversion = $totalPostulaciones > 0 ? round(($totalAceptados / $totalPostulaciones) * 100, 1) : 0.0;
$tasaEntrevistas = $totalPostulaciones > 0 ? round(($totalEntrevistas / $totalPostulaciones) * 100, 1) : 0.0;

// Desglose por vacante
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
$desgloseVacantes = [];
if ($resDesglose) {
    while ($rowD = $resDesglose->fetch_assoc()) {
        $desgloseVacantes[] = $rowD;
    }
    $resDesglose->free();
}

// Entrevistas agendadas y candidatos activos
$sqlEntrevistas = "
    SELECT
        p.id,
        p.nombre_completo,
        p.correo,
        p.telefono,
        p.estado,
        p.fecha_entrevista,
        p.fecha_postulacion,
        b.titulo AS vacante_titulo
    FROM patch_postulaciones p
    LEFT JOIN patch_BolsaTrabajo b ON b.id = p.id_bolsa
    WHERE {$whereSQL_P} AND (p.estado = 'entrevista' OR p.fecha_entrevista IS NOT NULL)
    ORDER BY p.fecha_entrevista DESC, p.fecha_postulacion DESC
    LIMIT 20
";
$stmtE = $conn->prepare($sqlEntrevistas);
if (!empty($paramsP)) {
    $stmtE->bind_param($typesP, ...$paramsP);
}
$stmtE->execute();
$resEntrevistas = $stmtE->get_result();
$listaEntrevistas = [];
while ($rowE = $resEntrevistas->fetch_assoc()) {
    $listaEntrevistas[] = $rowE;
}
$stmtE->close();

$fechaEmision = date('d/m/Y H:i:s');
$adminNombre = adminNombre();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Ejecutivo de Selección - Kombitec</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --green-dark: #0b3b27;
            --green-primary: #12834d;
            --green-light: #159a5b;
            --green-subtle: #f4fcf7;
            --border: #cbd5e1;
            --text-dark: #0f172a;
            --text-muted: #475569;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background-color: #f8fafc;
            color: var(--text-dark);
            line-height: 1.5;
            padding-bottom: 50px;
        }

        /* BARRA SUPERIOR PARA PANTALLA */
        .no-print-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .no-print-bar .brand-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--green-dark);
        }

        .no-print-actions {
            display: flex;
            gap: 10px;
        }

        .btn-print {
            background: linear-gradient(135deg, #12834d 0%, #159a5b 100%);
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-print:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-back {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13.5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #f1f5f9;
        }

        /* HOJA DEL REPORTE (A4 / CARTA) */
        .report-page {
            max-width: 960px;
            margin: 25px auto;
            background: #ffffff;
            padding: 40px 48px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        /* ENCABEZADO EJECUTIVO */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid var(--green-primary);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .report-logo-text {
            font-size: 24px;
            font-weight: 900;
            color: var(--green-dark);
            letter-spacing: -0.5px;
        }

        .report-subtitle {
            font-size: 14px;
            font-weight: 700;
            color: var(--green-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .report-meta {
            text-align: right;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .report-meta strong {
            color: var(--text-dark);
        }

        /* FILTROS APLICADOS */
        .filter-summary-box {
            background: var(--green-subtle);
            border-left: 4px solid var(--green-primary);
            padding: 12px 16px;
            margin-bottom: 25px;
            font-size: 12.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 6px 6px 0;
        }

        /* SECCIONES Y TÍTULOS */
        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--green-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        /* GRID DE KPIS */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 26px;
        }

        .kpi-tile {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            border-top: 4px solid var(--green-primary);
        }

        .kpi-tile.amber { border-top-color: #f59e0b; }
        .kpi-tile.purple { border-top-color: #8b5cf6; }
        .kpi-tile.blue { border-top-color: #3b82f6; }

        .kpi-tile-num {
            font-size: 24px;
            font-weight: 800;
            color: var(--green-dark);
            line-height: 1.1;
        }

        .kpi-tile-label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 4px;
        }

        .kpi-tile-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* TABLAS DE REPORTE */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 26px;
        }

        .report-table th {
            background: var(--green-primary);
            color: #ffffff;
            padding: 8px 10px;
            font-weight: 700;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .report-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            vertical-align: middle;
        }

        .report-table tr:nth-child(even) td {
            background: #fafcfb;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .badge-status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
        }

        .badge-review { background: #fef3c7; color: #b45309; }
        .badge-process { background: #dbeafe; color: #1d4ed8; }
        .badge-interview { background: #ede9fe; color: #6d28d9; }
        .badge-accepted { background: #dcfce7; color: #15803d; }
        .badge-rejected { background: #fee2e2; color: #b91c1c; }

        /* BLOQUE DE FIRMAS */
        .signature-section {
            margin-top: 45px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            width: 80%;
            margin: 45px auto 8px auto;
            border-bottom: 1px solid #334155;
        }

        .signature-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--green-dark);
        }

        .signature-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* REGLAS PARA IMPRESIÓN Y PDF */
        @media print {
            body {
                background: #ffffff;
                color: #000000;
                padding-bottom: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .report-page {
                max-width: 100%;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            .report-header {
                border-bottom-color: #0b3b27 !important;
            }

            .report-table th {
                background-color: #12834d !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .filter-summary-box,
            .kpi-tile,
            .report-table tr:nth-child(even) td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .signature-section {
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 14mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA FLOTANTE EN PANTALLA (NO IMPRIMIBLE) -->
    <div class="no-print-bar">
        <div class="brand-info">
            <i class="fa fa-file-pdf" style="color: #dc2626; font-size: 20px;"></i>
            <span>KOMBITEC &middot; Vista previa de impresión ejecutiva</span>
        </div>
        <div class="no-print-actions">
            <a href="Admin_Reportes.php" class="btn-back">
                <i class="fa fa-arrow-left"></i>
                Regresar al Dashboard
            </a>
            <button type="button" class="btn-print" onclick="window.print();">
                <i class="fa fa-print"></i>
                Imprimir o Guardar en PDF
            </button>
        </div>
    </div>

    <!-- DOCUMENTO EJECUTIVO -->
    <div class="report-page">

        <!-- ENCABEZADO -->
        <div class="report-header">
            <div>
                <div class="report-logo-text">KOMBITEC S.A. DE C.V.</div>
                <div class="report-subtitle">Bolsa de Trabajo y Gestión de Talento</div>
                <div style="font-size: 13px; color: #334155; margin-top: 4px; font-weight: 600;">
                    Reporte Ejecutivo de Selección y Estadísticas
                </div>
            </div>
            <div class="report-meta">
                <div><strong>Emisión:</strong> <?php echo $fechaEmision; ?></div>
                <div><strong>Generado por:</strong> <?php echo e($adminNombre); ?></div>
                <div><strong>Estado de sesión:</strong> Administrador Autorizado</div>
            </div>
        </div>

        <!-- FILTROS APLICADOS -->
        <div class="filter-summary-box">
            <div>
                <strong>Criterio de análisis:</strong> <?php echo e($nombreVacanteFiltro); ?>
            </div>
            <div>
                <strong>Período:</strong>
                <?php
                if ($fechaInicio !== '' || $fechaFin !== '') {
                    echo ($fechaInicio ? 'Desde ' . date('d/m/Y', strtotime($fechaInicio)) : 'Inicio') . ' hasta ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Hoy');
                } else {
                    echo 'Histórico completo';
                }
                ?>
            </div>
        </div>

        <!-- MÉTRICAS CLAVE (KPIS) -->
        <div class="section-title">
            <i class="fa fa-tachometer-alt"></i> 1. Resumen Ejecutivo de Métricas Clave
        </div>

        <div class="kpi-row">
            <div class="kpi-tile">
                <div class="kpi-tile-num"><?php echo number_format($totalPostulaciones); ?></div>
                <div class="kpi-tile-label">Total Postulaciones</div>
                <div class="kpi-tile-sub"><?php echo $totalNuevas; ?> pendientes por revisar</div>
            </div>

            <div class="kpi-tile purple">
                <div class="kpi-tile-num"><?php echo number_format($totalEnProceso + $totalEntrevistas); ?></div>
                <div class="kpi-tile-label">Candidatos Activos</div>
                <div class="kpi-tile-sub"><?php echo $totalEntrevistas; ?> en etapa de entrevista</div>
            </div>

            <div class="kpi-tile">
                <div class="kpi-tile-num" style="color: #15803d;"><?php echo number_format($totalAceptados); ?></div>
                <div class="kpi-tile-label">Aceptados / Contratados</div>
                <div class="kpi-tile-sub">Tasa de éxito: <strong><?php echo $tasaConversion; ?>%</strong></div>
            </div>

            <div class="kpi-tile amber">
                <div class="kpi-tile-num" style="color: #b91c1c;"><?php echo number_format($totalDescartados); ?></div>
                <div class="kpi-tile-label">Descartados</div>
                <div class="kpi-tile-sub"><?php echo $totalPostulaciones > 0 ? round(($totalDescartados / $totalPostulaciones) * 100, 1) : 0; ?>% del total procesado</div>
            </div>
        </div>

        <!-- TABLA 1: DISTRIBUCIÓN POR ESTADOS -->
        <div class="section-title">
            <i class="fa fa-pie-chart"></i> 2. Desglose del Embudo de Selección
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Estado de Postulación</th>
                    <th class="text-center" style="width: 100px;">Cantidad</th>
                    <th class="text-center" style="width: 130px;">% del Total</th>
                    <th>Descripción de la Etapa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge-status badge-review">Por revisar (Nueva)</span></td>
                    <td class="text-center"><strong><?php echo $totalNuevas; ?></strong></td>
                    <td class="text-center"><?php echo $totalPostulaciones > 0 ? round(($totalNuevas / $totalPostulaciones) * 100, 1) : 0; ?>%</td>
                    <td>Candidaturas recibidas pendientes del primer filtro y revisión de CV.</td>
                </tr>
                <tr>
                    <td><span class="badge-status badge-process">En proceso</span></td>
                    <td class="text-center"><strong><?php echo $totalEnProceso; ?></strong></td>
                    <td class="text-center"><?php echo $totalPostulaciones > 0 ? round(($totalEnProceso / $totalPostulaciones) * 100, 1) : 0; ?>%</td>
                    <td>Candidatos validados para evaluación técnica o psicométrica inicial.</td>
                </tr>
                <tr>
                    <td><span class="badge-status badge-interview">Entrevista agendada</span></td>
                    <td class="text-center"><strong><?php echo $totalEntrevistas; ?></strong></td>
                    <td class="text-center"><?php echo $totalPostulaciones > 0 ? round(($totalEntrevistas / $totalPostulaciones) * 100, 1) : 0; ?>%</td>
                    <td>Candidatos con cita acordada telefónicamente y confirmación enviada por correo.</td>
                </tr>
                <tr>
                    <td><span class="badge-status badge-accepted">Aceptado / Contratado</span></td>
                    <td class="text-center"><strong><?php echo $totalAceptados; ?></strong></td>
                    <td class="text-center"><strong><?php echo $tasaConversion; ?>%</strong></td>
                    <td>Candidatos que completaron satisfactoriamente el proceso y oferta laboral.</td>
                </tr>
                <tr>
                    <td><span class="badge-status badge-rejected">Descartado</span></td>
                    <td class="text-center"><strong><?php echo $totalDescartados; ?></strong></td>
                    <td class="text-center"><?php echo $totalPostulaciones > 0 ? round(($totalDescartados / $totalPostulaciones) * 100, 1) : 0; ?>%</td>
                    <td>Candidatos no seleccionados que recibieron correo de agradecimiento o cierre.</td>
                </tr>
            </tbody>
        </table>

        <!-- TABLA 2: RENDIMIENTO POR VACANTE -->
        <div class="section-title">
            <i class="fa fa-briefcase"></i> 3. Desglose de Rendimiento por Vacante
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Vacante / Convocatoria</th>
                    <th>Ubicación</th>
                    <th>Modalidad</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Nuevas</th>
                    <th class="text-center">Proceso</th>
                    <th class="text-center">Entrevista</th>
                    <th class="text-center">Aceptados</th>
                    <th class="text-center">Conversión</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($desgloseVacantes)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                            No hay vacantes registradas en el criterio seleccionado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($desgloseVacantes as $dv): ?>
                        <?php
                        $totV = (int)$dv['total_postulaciones'];
                        $tasaV = $totV > 0 ? round(((int)$dv['aceptados'] / $totV) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo e($dv['titulo']); ?></strong>
                                <?php if ($dv['archivada']): ?>
                                    <span style="font-size: 10px; color: #64748b;">(Archivada)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($dv['ubicacion'] ?: 'N/A'); ?></td>
                            <td><?php echo e($dv['modalidad'] ?: 'Presencial'); ?></td>
                            <td class="text-center"><strong><?php echo $totV; ?></strong></td>
                            <td class="text-center"><?php echo (int)$dv['nuevas']; ?></td>
                            <td class="text-center"><?php echo (int)$dv['en_proceso']; ?></td>
                            <td class="text-center"><?php echo (int)$dv['entrevistas']; ?></td>
                            <td class="text-center" style="font-weight: 700; color: #15803d;"><?php echo (int)$dv['aceptados']; ?></td>
                            <td class="text-center"><strong><?php echo $tasaV; ?>%</strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TABLA 3: CANDIDATOS EN ETAPA DE ENTREVISTA / CITAS PROGRAMADAS -->
        <?php if (!empty($listaEntrevistas)): ?>
            <div class="section-title">
                <i class="fa fa-calendar-check"></i> 4. Candidatos en Etapa de Entrevista y Citas Programadas
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Candidato</th>
                        <th>Vacante</th>
                        <th>Correo Electrónico</th>
                        <th>Teléfono</th>
                        <th class="text-center">Fecha y Hora de Cita</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaEntrevistas as $ent): ?>
                        <tr>
                            <td><strong><?php echo e($ent['nombre_completo']); ?></strong></td>
                            <td><?php echo e($ent['vacante_titulo'] ?: 'CV General'); ?></td>
                            <td><?php echo e($ent['correo']); ?></td>
                            <td><?php echo e($ent['telefono']); ?></td>
                            <td class="text-center" style="font-weight: 700; color: #0f633d;">
                                <?php echo !empty($ent['fecha_entrevista']) ? date('d/m/Y H:i', strtotime($ent['fecha_entrevista'])) : 'Pendiente de acordar'; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge-status badge-interview">
                                    <?php echo estadoTexto((string)$ent['estado']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- FIRMAS Y CONFORMIDAD EJECUTIVA -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title"><?php echo e($adminNombre); ?></div>
                <div class="signature-role">Responsable de Recursos Humanos / Reclutamiento</div>
            </div>

            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Dirección de Operaciones / Administración</div>
                <div class="signature-role">Kombitec S.A. de C.V.</div>
            </div>
        </div>

    </div><!-- /report-page -->

    <?php if ($autoPrint): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    window.print();
                }, 500);
            });
        </script>
    <?php endif; ?>
</body>
</html>
