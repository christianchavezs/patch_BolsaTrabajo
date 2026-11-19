<?php
declare(strict_types=1);

/**
 * ============================================================
 * EXPORTACIÓN A EXCEL - PATCH BOLSA DE TRABAJO
 * Proyecto: Patch_BolsaTrabajo (Kombitec)
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/api/exportar_excel.php
 * ============================================================
 */

require_once __DIR__ . "/../../../../includes/auth.php";
requerirAdminPagina('../../../login.php');

require_once __DIR__ . "/../../../../includes/config.php";

// Evitar buffer previo
if (ob_get_level()) {
    ob_end_clean();
}

$tipo        = trim((string)($_GET['tipo'] ?? 'postulaciones')); // 'postulaciones' | 'vacantes' | 'reporte_general'
$vacanteId   = isset($_GET['vacante']) ? (int)$_GET['vacante'] : 0; // 0 = todas, -1 = solo cv general, >0 = id específico
$estado      = trim((string)($_GET['estado'] ?? 'todas'));
$fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
$fechaFin    = trim((string)($_GET['fecha_fin'] ?? ''));
$buscar      = trim((string)($_GET['buscar'] ?? ''));
$formato     = trim((string)($_GET['formato'] ?? 'excel')); // 'excel' | 'csv'

$nombreAdmin = adminNombre();
$fechaEmision = date('d/m/Y H:i:s');

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

function estadoColor(string $estado): string {
    return match ($estado) {
        'nueva'      => '#b45309; background-color: #fef3c7',
        'en_proceso' => '#1d4ed8; background-color: #dbeafe',
        'entrevista' => '#6d28d9; background-color: #ede9fe',
        'aceptado'   => '#15803d; background-color: #dcfce7',
        'descartado' => '#b91c1c; background-color: #fee2e2',
        default      => '#334155; background-color: #f1f5f9'
    };
}

// ============================================================
// CASO 1: EXPORTAR VACANTES
// ============================================================
if ($tipo === 'vacantes') {
    $where = ["1=1"];
    $params = [];
    $types = "";

    if ($estado === 'activas') {
        $where[] = "b.activo = 1 AND b.archivada = 0";
    } elseif ($estado === 'inactivas') {
        $where[] = "b.activo = 0 AND b.archivada = 0";
    } elseif ($estado === 'archivadas') {
        $where[] = "b.archivada = 1";
    }

    if ($fechaInicio !== '') {
        $where[] = "DATE(b.fecha_publicacion) >= ?";
        $params[] = $fechaInicio;
        $types .= "s";
    }
    if ($fechaFin !== '') {
        $where[] = "DATE(b.fecha_publicacion) <= ?";
        $params[] = $fechaFin;
        $types .= "s";
    }
    if ($buscar !== '') {
        $where[] = "(b.titulo LIKE ? OR b.ubicacion LIKE ? OR b.modalidad LIKE ?)";
        $term = "%{$buscar}%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= "sss";
    }

    $whereSQL = implode(" AND ", $where);

    $sql = "
        SELECT
            b.id,
            b.titulo,
            b.ubicacion,
            b.tipo_jornada,
            b.modalidad,
            b.activo,
            b.archivada,
            b.fecha_publicacion,
            b.fecha_cierre,
            COUNT(p.id) AS total_postulaciones,
            SUM(CASE WHEN p.estado = 'nueva' THEN 1 ELSE 0 END) AS cant_nuevas,
            SUM(CASE WHEN p.estado = 'en_proceso' THEN 1 ELSE 0 END) AS cant_proceso,
            SUM(CASE WHEN p.estado = 'entrevista' THEN 1 ELSE 0 END) AS cant_entrevistas,
            SUM(CASE WHEN p.estado = 'aceptado' THEN 1 ELSE 0 END) AS cant_aceptados,
            SUM(CASE WHEN p.estado = 'descartado' THEN 1 ELSE 0 END) AS cant_descartados
        FROM patch_BolsaTrabajo b
        LEFT JOIN patch_postulaciones p ON p.id_bolsa = b.id
        WHERE {$whereSQL}
        GROUP BY b.id
        ORDER BY b.fecha_publicacion DESC, b.id DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $filas = [];
    while ($row = $result->fetch_assoc()) {
        $filas[] = $row;
    }
    $stmt->close();

    $nombreArchivo = "Kombitec_Reporte_Vacantes_" . date('Ymd_His');

    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Título de Vacante', 'Ubicación', 'Jornada', 'Modalidad', 'Estado', 'Fecha Publicación', 'Fecha Cierre', 'Total Postulaciones', 'Nuevas', 'En Proceso', 'Entrevistas', 'Aceptados', 'Descartados']);
        foreach ($filas as $f) {
            $estTexto = ($f['archivada'] == 1) ? 'Archivada' : (($f['activo'] == 1) ? 'Activa' : 'Inactiva');
            fputcsv($out, [
                $f['id'],
                $f['titulo'],
                $f['ubicacion'] ?: 'No especificada',
                $f['tipo_jornada'] ?: 'No especificada',
                $f['modalidad'] ?: 'No especificada',
                $estTexto,
                $f['fecha_publicacion'] ? date('d/m/Y', strtotime($f['fecha_publicacion'])) : 'Sin fecha',
                $f['fecha_cierre'] ? date('d/m/Y', strtotime($f['fecha_cierre'])) : 'Sin fecha',
                $f['total_postulaciones'],
                $f['cant_nuevas'],
                $f['cant_proceso'],
                $f['cant_entrevistas'],
                $f['cant_aceptados'],
                $f['cant_descartados']
            ]);
        }
        fclose($out);
        exit;
    }

    // Formato Excel HTML enriquecido
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Reporte de Vacantes - Kombitec</title>
        <style>
            body { font-family: Calibri, Arial, sans-serif; }
            .header-table { margin-bottom: 20px; }
            .title-kombitec { font-size: 18pt; font-weight: bold; color: #0b3b27; }
            .subtitle { font-size: 11pt; color: #159a5b; font-weight: bold; }
            .meta-text { font-size: 9.5pt; color: #475569; }
            .table-data { border-collapse: collapse; width: 100%; }
            .table-data th { background-color: #159a5b; color: #ffffff; font-weight: bold; padding: 10px 8px; border: 1px solid #0f633d; font-size: 10.5pt; text-align: left; }
            .table-data td { padding: 8px; border: 1px solid #cbd5e1; font-size: 10pt; vertical-align: middle; }
            .zebra { background-color: #f4fcf7; }
            .badge-active { background-color: #dcfce7; color: #15803d; font-weight: bold; padding: 3px 6px; border-radius: 4px; }
            .badge-archived { background-color: #f1f5f9; color: #475569; padding: 3px 6px; border-radius: 4px; }
            .badge-inactive { background-color: #fee2e2; color: #b91c1c; padding: 3px 6px; border-radius: 4px; }
            .num-cell { text-align: center; }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr>
                <td colspan="14" class="title-kombitec">KOMBITEC S.A. DE C.V. &middot; BOLSA DE TRABAJO</td>
            </tr>
            <tr>
                <td colspan="14" class="subtitle">REPORTE DETALLADO DE VACANTES LABORALES</td>
            </tr>
            <tr>
                <td colspan="14" class="meta-text">
                    <strong>Fecha de generación:</strong> <?php echo $fechaEmision; ?> &nbsp;|&nbsp;
                    <strong>Generado por:</strong> <?php echo e($nombreAdmin); ?> &nbsp;|&nbsp;
                    <strong>Total registros:</strong> <?php echo count($filas); ?>
                </td>
            </tr>
        </table>
        <br>
        <table class="table-data" border="1">
            <thead>
                <tr>
                    <th style="background-color: #12834d; color: #fff; text-align: center;">ID</th>
                    <th style="background-color: #12834d; color: #fff;">Título de la Vacante</th>
                    <th style="background-color: #12834d; color: #fff;">Ubicación</th>
                    <th style="background-color: #12834d; color: #fff;">Jornada</th>
                    <th style="background-color: #12834d; color: #fff;">Modalidad</th>
                    <th style="background-color: #12834d; color: #fff; text-align: center;">Estado</th>
                    <th style="background-color: #12834d; color: #fff; text-align: center;">Fecha Publicación</th>
                    <th style="background-color: #12834d; color: #fff; text-align: center;">Fecha Cierre</th>
                    <th style="background-color: #0f633d; color: #fff; text-align: center;">Total Postulaciones</th>
                    <th style="background-color: #b45309; color: #fff; text-align: center;">Por Revisar</th>
                    <th style="background-color: #1d4ed8; color: #fff; text-align: center;">En Proceso</th>
                    <th style="background-color: #6d28d9; color: #fff; text-align: center;">Entrevistas</th>
                    <th style="background-color: #15803d; color: #fff; text-align: center;">Aceptados</th>
                    <th style="background-color: #b91c1c; color: #fff; text-align: center;">Descartados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filas)): ?>
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 20px; color: #64748b;">No se encontraron vacantes con los filtros especificados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filas as $idx => $f): ?>
                        <?php
                        $claseZebra = ($idx % 2 === 1) ? 'class="zebra"' : '';
                        $estClase = ($f['archivada'] == 1) ? 'badge-archived' : (($f['activo'] == 1) ? 'badge-active' : 'badge-inactive');
                        $estTexto = ($f['archivada'] == 1) ? 'Archivada' : (($f['activo'] == 1) ? 'Activa' : 'Inactiva');
                        ?>
                        <tr <?php echo $claseZebra; ?>>
                            <td class="num-cell" style="font-weight: bold;"><?php echo $f['id']; ?></td>
                            <td><strong><?php echo e($f['titulo']); ?></strong></td>
                            <td><?php echo e($f['ubicacion'] ?: 'No especificada'); ?></td>
                            <td><?php echo e($f['tipo_jornada'] ?: 'No especificada'); ?></td>
                            <td><?php echo e($f['modalidad'] ?: 'No especificada'); ?></td>
                            <td class="num-cell"><span class="<?php echo $estClase; ?>"><?php echo $estTexto; ?></span></td>
                            <td class="num-cell"><?php echo $f['fecha_publicacion'] ? date('d/m/Y', strtotime($f['fecha_publicacion'])) : 'Sin fecha'; ?></td>
                            <td class="num-cell"><?php echo $f['fecha_cierre'] ? date('d/m/Y', strtotime($f['fecha_cierre'])) : 'Sin fecha'; ?></td>
                            <td class="num-cell" style="font-weight: bold; background-color: #eafaf1; color: #0b3b27;"><?php echo (int)$f['total_postulaciones']; ?></td>
                            <td class="num-cell" style="color: #b45309; font-weight: bold;"><?php echo (int)$f['cant_nuevas']; ?></td>
                            <td class="num-cell" style="color: #1d4ed8; font-weight: bold;"><?php echo (int)$f['cant_proceso']; ?></td>
                            <td class="num-cell" style="color: #6d28d9; font-weight: bold;"><?php echo (int)$f['cant_entrevistas']; ?></td>
                            <td class="num-cell" style="color: #15803d; font-weight: bold;"><?php echo (int)$f['cant_aceptados']; ?></td>
                            <td class="num-cell" style="color: #b91c1c; font-weight: bold;"><?php echo (int)$f['cant_descartados']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// CASO 2: EXPORTAR REPORTE EJECUTIVO Y ESTADÍSTICAS
// ============================================================
if ($tipo === 'reporte_general') {
    $whereP = ["1=1"];
    $paramsP = [];
    $typesP = "";

    if ($vacanteId > 0) {
        $whereP[] = "p.id_bolsa = ?";
        $paramsP[] = $vacanteId;
        $typesP .= "i";
    } elseif ($vacanteId === -1) {
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

    // Resumen de postulaciones
    $sqlRes = "
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
    $stmtRes = $conn->prepare($sqlRes);
    if (!empty($paramsP)) {
        $stmtRes->bind_param($typesP, ...$paramsP);
    }
    $stmtRes->execute();
    $rowRes = $stmtRes->get_result()->fetch_assoc() ?: [];
    $stmtRes->close();

    $totPost = (int)($rowRes['total'] ?? 0);
    $totNuev = (int)($rowRes['nuevas'] ?? 0);
    $totProc = (int)($rowRes['en_proceso'] ?? 0);
    $totEntr = (int)($rowRes['entrevistas'] ?? 0);
    $totAcep = (int)($rowRes['aceptados'] ?? 0);
    $totDesc = (int)($rowRes['descartados'] ?? 0);
    $tasaConv = $totPost > 0 ? round(($totAcep / $totPost) * 100, 1) : 0.0;

    // Desglose por vacantes
    $sqlDesg = "
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
        WHERE " . ($vacanteId > 0 ? "b.id = " . (int)$vacanteId : "1=1") . "
        GROUP BY b.id, b.titulo, b.ubicacion, b.tipo_jornada, b.modalidad, b.activo, b.archivada
        ORDER BY total_postulaciones DESC, b.titulo ASC
    ";
    $resDesg = $conn->query($sqlDesg);
    $desgloseV = [];
    if ($resDesg) {
        while ($rD = $resDesg->fetch_assoc()) {
            $desgloseV[] = $rD;
        }
        $resDesg->free();
    }

    $nombreArchivo = "Kombitec_Reporte_Estadistico_" . date('Ymd_His');

    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');

        fputcsv($out, ['KOMBITEC S.A. DE C.V. - REPORTE EJECUTIVO DE ESTADÍSTICAS']);
        fputcsv($out, ['Fecha de emisión:', $fechaEmision, 'Generado por:', $nombreAdmin]);
        fputcsv($out, []);

        fputcsv($out, ['RESUMEN EJECUTIVO (KPIS)']);
        fputcsv($out, ['Métrica', 'Valor']);
        fputcsv($out, ['Total Postulaciones Recibidas', $totPost]);
        fputcsv($out, ['Candidaturas Nuevas (Por revisar)', $totNuev]);
        fputcsv($out, ['En Proceso de Evaluación', $totProc]);
        fputcsv($out, ['En Etapa de Entrevista', $totEntr]);
        fputcsv($out, ['Aceptados / Contratados', $totAcep]);
        fputcsv($out, ['Descartados', $totDesc]);
        fputcsv($out, ['Tasa de Efectividad / Conversión', $tasaConv . '%']);
        fputcsv($out, []);

        fputcsv($out, ['RENDIMIENTO Y MÉTRICAS POR VACANTE']);
        fputcsv($out, ['ID', 'Vacante', 'Ubicación', 'Modalidad', 'Estado Vacante', 'Total Postulaciones', 'Nuevas', 'En Proceso', 'Entrevistas', 'Aceptados', 'Descartados', '% Conversión']);
        foreach ($desgloseV as $dv) {
            $tot = (int)$dv['total_postulaciones'];
            $tConv = $tot > 0 ? round(((int)$dv['aceptados'] / $tot) * 100, 1) : 0;
            $estVac = $dv['archivada'] ? 'Archivada' : ($dv['activo'] ? 'Activa' : 'Inactiva');
            fputcsv($out, [
                $dv['id'],
                $dv['titulo'],
                $dv['ubicacion'] ?: 'N/A',
                $dv['modalidad'] ?: 'Presencial',
                $estVac,
                $tot,
                (int)$dv['nuevas'],
                (int)$dv['en_proceso'],
                (int)$dv['entrevistas'],
                (int)$dv['aceptados'],
                (int)$dv['descartados'],
                $tConv . '%'
            ]);
        }
        fclose($out);
        exit;
    }

    // Formato Excel HTML enriquecido
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Reporte Estadístico Ejecutivo - Kombitec</title>
        <style>
            body { font-family: Calibri, Arial, sans-serif; }
            .header-table { margin-bottom: 20px; }
            .title-kombitec { font-size: 18pt; font-weight: bold; color: #0b3b27; }
            .subtitle { font-size: 11pt; color: #159a5b; font-weight: bold; }
            .meta-text { font-size: 9.5pt; color: #475569; }
            .table-data { border-collapse: collapse; width: 100%; margin-bottom: 25px; }
            .table-data th { background-color: #12834d; color: #ffffff; font-weight: bold; padding: 10px 8px; border: 1px solid #0f633d; font-size: 10pt; text-align: left; }
            .table-data td { padding: 8px; border: 1px solid #cbd5e1; font-size: 9.5pt; vertical-align: middle; }
            .zebra { background-color: #f4fcf7; }
            .num-cell { text-align: center; }
            .sec-title { font-size: 13pt; font-weight: bold; color: #0b3b27; padding: 10px 0; }
            .kpi-box { padding: 10px; background-color: #f4fcf7; border: 1px solid #a3c9b4; }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr><td colspan="12" class="title-kombitec">KOMBITEC S.A. DE C.V. &middot; BOLSA DE TRABAJO</td></tr>
            <tr><td colspan="12" class="subtitle">REPORTE EJECUTIVO DE ESTADÍSTICAS Y RENDIMIENTO DE SELECCIÓN</td></tr>
            <tr>
                <td colspan="12" class="meta-text">
                    <strong>Fecha de emisión:</strong> <?php echo $fechaEmision; ?> &nbsp;|&nbsp;
                    <strong>Generado por:</strong> <?php echo e($nombreAdmin); ?> &nbsp;|&nbsp;
                    <strong>Total postulaciones analizadas:</strong> <?php echo $totPost; ?> &nbsp;|&nbsp;
                    <strong>Período:</strong> <?php echo ($fechaInicio !== '' ? 'Desde ' . date('d/m/Y', strtotime($fechaInicio)) : 'Inicio') . ' hasta ' . ($fechaFin !== '' ? date('d/m/Y', strtotime($fechaFin)) : 'Hoy'); ?>
                </td>
            </tr>
        </table>
        <br>

        <div class="sec-title">1. RESUMEN EJECUTIVO (INDICADORES CLAVE - KPIS)</div>
        <table class="table-data" border="1">
            <thead>
                <tr>
                    <th style="background-color: #0b3b27; color:#fff; width: 280px;">Indicador de Selección</th>
                    <th style="background-color: #12834d; color:#fff; text-align: center; width: 140px;">Total Registrado</th>
                    <th style="background-color: #159a5b; color:#fff; text-align: center; width: 140px;">% del Total</th>
                    <th style="background-color: #12834d; color:#fff; width: 350px;">Observación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total de Candidaturas Recibidas</strong></td>
                    <td class="num-cell" style="font-weight: bold; font-size: 11pt;"><?php echo $totPost; ?></td>
                    <td class="num-cell">100.0%</td>
                    <td>Aspirantes que enviaron su solicitud en el período.</td>
                </tr>
                <tr class="zebra">
                    <td>Candidaturas Nuevas (Por revisar)</td>
                    <td class="num-cell" style="color: #b45309; font-weight: bold;"><?php echo $totNuev; ?></td>
                    <td class="num-cell"><?php echo $totPost > 0 ? round(($totNuev / $totPost) * 100, 1) : 0; ?>%</td>
                    <td>Pendientes de evaluación de perfil inicial.</td>
                </tr>
                <tr>
                    <td>Candidatos en Proceso de Evaluación</td>
                    <td class="num-cell" style="color: #1d4ed8; font-weight: bold;"><?php echo $totProc; ?></td>
                    <td class="num-cell"><?php echo $totPost > 0 ? round(($totProc / $totPost) * 100, 1) : 0; ?>%</td>
                    <td>Perfiles preseleccionados activos.</td>
                </tr>
                <tr class="zebra">
                    <td>Candidatos en Etapa de Entrevista</td>
                    <td class="num-cell" style="color: #6d28d9; font-weight: bold;"><?php echo $totEntr; ?></td>
                    <td class="num-cell"><?php echo $totPost > 0 ? round(($totEntr / $totPost) * 100, 1) : 0; ?>%</td>
                    <td>Citas acordadas telefónicamente con correo de confirmación.</td>
                </tr>
                <tr style="background-color: #dcfce7;">
                    <td><strong>Aceptados / Contratados (Éxito)</strong></td>
                    <td class="num-cell" style="color: #15803d; font-weight: bold; font-size: 11pt;"><?php echo $totAcep; ?></td>
                    <td class="num-cell" style="color: #15803d; font-weight: bold;"><?php echo $tasaConv; ?>%</td>
                    <td><strong>Candidatos incorporados a Kombitec.</strong></td>
                </tr>
                <tr class="zebra">
                    <td>Candidaturas Descartadas</td>
                    <td class="num-cell" style="color: #b91c1c; font-weight: bold;"><?php echo $totDesc; ?></td>
                    <td class="num-cell"><?php echo $totPost > 0 ? round(($totDesc / $totPost) * 100, 1) : 0; ?>%</td>
                    <td>Candidatos no seleccionados con correo de agradecimiento.</td>
                </tr>
            </tbody>
        </table>
        <br>

        <div class="sec-title">2. RENDIMIENTO Y MÉTRICAS POR VACANTE</div>
        <table class="table-data" border="1">
            <thead>
                <tr>
                    <th style="background-color: #0b3b27; color:#fff; text-align: center; width: 60px;">ID</th>
                    <th style="background-color: #0f633d; color:#fff; width: 260px;">Título de la Vacante</th>
                    <th style="background-color: #12834d; color:#fff; width: 140px;">Ubicación</th>
                    <th style="background-color: #12834d; color:#fff; width: 120px;">Modalidad</th>
                    <th style="background-color: #12834d; color:#fff; text-align: center; width: 90px;">Estado</th>
                    <th style="background-color: #159a5b; color:#fff; text-align: center; width: 100px;">Postulaciones</th>
                    <th style="background-color: #12834d; color:#fff; text-align: center; width: 80px;">Nuevas</th>
                    <th style="background-color: #12834d; color:#fff; text-align: center; width: 80px;">Proceso</th>
                    <th style="background-color: #12834d; color:#fff; text-align: center; width: 90px;">Entrevistas</th>
                    <th style="background-color: #15803d; color:#fff; text-align: center; width: 90px;">Aceptados</th>
                    <th style="background-color: #b91c1c; color:#fff; text-align: center; width: 90px;">Descartados</th>
                    <th style="background-color: #0b3b27; color:#fff; text-align: center; width: 100px;">Conversión</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($desgloseV)): ?>
                    <tr><td colspan="12" style="text-align:center; padding: 20px; color:#64748b;">No hay vacantes para el criterio seleccionado.</td></tr>
                <?php else: ?>
                    <?php foreach ($desgloseV as $idx => $dv): ?>
                        <?php
                        $claseZ = ($idx % 2 === 1) ? 'class="zebra"' : '';
                        $tot = (int)$dv['total_postulaciones'];
                        $tConv = $tot > 0 ? round(((int)$dv['aceptados'] / $tot) * 100, 1) : 0;
                        $estVac = $dv['archivada'] ? 'Archivada' : ($dv['activo'] ? 'Activa' : 'Inactiva');
                        ?>
                        <tr <?php echo $claseZ; ?>>
                            <td class="num-cell"><?php echo $dv['id']; ?></td>
                            <td><strong><?php echo e($dv['titulo']); ?></strong></td>
                            <td><?php echo e($dv['ubicacion'] ?: 'N/A'); ?></td>
                            <td><?php echo e($dv['modalidad'] ?: 'Presencial'); ?></td>
                            <td class="num-cell"><?php echo $estVac; ?></td>
                            <td class="num-cell" style="font-weight: bold; background-color: #eafaf1; color: #0b3b27;"><?php echo $tot; ?></td>
                            <td class="num-cell" style="color: #b45309; font-weight: bold;"><?php echo (int)$dv['nuevas']; ?></td>
                            <td class="num-cell" style="color: #1d4ed8; font-weight: bold;"><?php echo (int)$dv['en_proceso']; ?></td>
                            <td class="num-cell" style="color: #6d28d9; font-weight: bold;"><?php echo (int)$dv['entrevistas']; ?></td>
                            <td class="num-cell" style="color: #15803d; font-weight: bold; background-color: #f0fdf4;"><?php echo (int)$dv['aceptados']; ?></td>
                            <td class="num-cell" style="color: #b91c1c; font-weight: bold;"><?php echo (int)$dv['descartados']; ?></td>
                            <td class="num-cell" style="font-weight: bold; color: #0b3b27;"><?php echo $tConv; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// CASO 3: EXPORTAR POSTULACIONES / CANDIDATOS (POR DEFECTO)
// ============================================================
$where = ["1=1"];
$params = [];
$types = "";

if ($vacanteId > 0) {
    $where[] = "p.id_bolsa = ?";
    $params[] = $vacanteId;
    $types .= "i";
} elseif ($vacanteId === -1) {
    $where[] = "p.id_bolsa IS NULL";
}

if ($estado !== 'todas' && $estado !== '') {
    $where[] = "p.estado = ?";
    $params[] = $estado;
    $types .= "s";
}

if ($fechaInicio !== '') {
    $where[] = "DATE(p.fecha_postulacion) >= ?";
    $params[] = $fechaInicio;
    $types .= "s";
}

if ($fechaFin !== '') {
    $where[] = "DATE(p.fecha_postulacion) <= ?";
    $params[] = $fechaFin;
    $types .= "s";
}

if ($buscar !== '') {
    $where[] = "(p.nombre_completo LIKE ? OR p.correo LIKE ? OR p.telefono LIKE ? OR b.titulo LIKE ?)";
    $term = "%{$buscar}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "ssss";
}

$whereSQL = implode(" AND ", $where);

$sql = "
    SELECT
        p.id,
        p.id_bolsa,
        p.nombre_completo,
        p.correo,
        p.telefono,
        p.cv,
        p.comentarios,
        p.comentario_seguimiento,
        p.estado,
        p.fecha_entrevista,
        p.fecha_ultimo_correo,
        p.tipo_ultimo_correo,
        p.fecha_postulacion,
        p.updated_at,
        b.titulo AS vacante_titulo,
        b.ubicacion AS vacante_ubicacion,
        b.tipo_jornada AS vacante_jornada,
        b.modalidad AS vacante_modalidad
    FROM patch_postulaciones p
    LEFT JOIN patch_BolsaTrabajo b ON b.id = p.id_bolsa
    WHERE {$whereSQL}
    ORDER BY p.fecha_postulacion DESC, p.id DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$postulaciones = [];
while ($row = $result->fetch_assoc()) {
    $postulaciones[] = $row;
}
$stmt->close();

$nombreArchivo = "Kombitec_Postulaciones_" . date('Ymd_His');

if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'ID Postulación',
        'Fecha Postulación',
        'Nombre del Candidato',
        'Correo Electrónico',
        'Teléfono',
        'Vacante',
        'Ubicación',
        'Modalidad',
        'Estado',
        'Fecha Entrevista',
        'Último Correo Enviado',
        'Tiene CV',
        'Comentarios Candidato',
        'Seguimiento RH'
    ]);
    foreach ($postulaciones as $p) {
        $vacTitulo = !empty($p['id_bolsa']) ? ($p['vacante_titulo'] ?: 'Vacante sin título') : 'CV General / Postulación espontánea';
        fputcsv($out, [
            $p['id'],
            $p['fecha_postulacion'] ? date('d/m/Y H:i', strtotime($p['fecha_postulacion'])) : 'Sin fecha',
            $p['nombre_completo'],
            $p['correo'],
            $p['telefono'],
            $vacTitulo,
            $p['vacante_ubicacion'] ?: 'N/A',
            $p['vacante_modalidad'] ?: 'N/A',
            estadoTexto((string)$p['estado']),
            $p['fecha_entrevista'] ? date('d/m/Y H:i', strtotime($p['fecha_entrevista'])) : 'No agendada',
            $p['fecha_ultimo_correo'] ? date('d/m/Y H:i', strtotime($p['fecha_ultimo_correo'])) . ' (' . $p['tipo_ultimo_correo'] . ')' : 'Sin envíos',
            !empty($p['cv']) ? 'Sí' : 'No',
            $p['comentarios'] ?: '',
            $p['comentario_seguimiento'] ?: ''
        ]);
    }
    fclose($out);
    exit;
}

// Formato Excel HTML enriquecido
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Reporte de Postulaciones - Kombitec</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; }
        .header-table { margin-bottom: 20px; }
        .title-kombitec { font-size: 18pt; font-weight: bold; color: #0b3b27; }
        .subtitle { font-size: 11pt; color: #159a5b; font-weight: bold; }
        .meta-text { font-size: 9.5pt; color: #475569; }
        .table-data { border-collapse: collapse; width: 100%; }
        .table-data th { background-color: #12834d; color: #ffffff; font-weight: bold; padding: 10px 8px; border: 1px solid #0f633d; font-size: 10.5pt; text-align: left; }
        .table-data td { padding: 8px; border: 1px solid #cbd5e1; font-size: 10pt; vertical-align: middle; }
        .zebra { background-color: #f4fcf7; }
        .badge-status { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 9.5pt; display: inline-block; }
        .num-cell { text-align: center; }
        .cv-cell { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td colspan="13" class="title-kombitec">KOMBITEC S.A. DE C.V. &middot; BOLSA DE TRABAJO</td>
        </tr>
        <tr>
            <td colspan="13" class="subtitle">REPORTE DETALLADO DE CANDIDATOS Y POSTULACIONES</td>
        </tr>
        <tr>
            <td colspan="13" class="meta-text">
                <strong>Fecha de emisión:</strong> <?php echo $fechaEmision; ?> &nbsp;|&nbsp;
                <strong>Generado por:</strong> <?php echo e($nombreAdmin); ?> &nbsp;|&nbsp;
                <strong>Total de registros exportados:</strong> <?php echo count($postulaciones); ?> &nbsp;|&nbsp;
                <strong>Filtros aplicados:</strong> <?php echo ($estado !== 'todas' && $estado !== '') ? 'Estado: ' . estadoTexto($estado) : 'Todos los estados'; ?>
                <?php echo ($fechaInicio !== '' ? ' | Desde: ' . date('d/m/Y', strtotime($fechaInicio)) : ''); ?>
                <?php echo ($fechaFin !== '' ? ' | Hasta: ' . date('d/m/Y', strtotime($fechaFin)) : ''); ?>
            </td>
        </tr>
    </table>
    <br>
    <table class="table-data" border="1">
        <thead>
            <tr>
                <th style="background-color: #0b3b27; color: #fff; text-align: center; width: 60px;"># ID</th>
                <th style="background-color: #0f633d; color: #fff; text-align: center; width: 120px;">Fecha Postulación</th>
                <th style="background-color: #12834d; color: #fff; width: 220px;">Nombre del Candidato</th>
                <th style="background-color: #12834d; color: #fff; width: 200px;">Correo Electrónico</th>
                <th style="background-color: #12834d; color: #fff; width: 120px;">Teléfono</th>
                <th style="background-color: #159a5b; color: #fff; width: 220px;">Vacante a la que Aplica</th>
                <th style="background-color: #159a5b; color: #fff; width: 130px;">Modalidad</th>
                <th style="background-color: #12834d; color: #fff; text-align: center; width: 120px;">Estado Actual</th>
                <th style="background-color: #12834d; color: #fff; text-align: center; width: 140px;">Fecha Entrevista</th>
                <th style="background-color: #12834d; color: #fff; text-align: center; width: 140px;">Último Correo RH</th>
                <th style="background-color: #12834d; color: #fff; text-align: center; width: 70px;">CV</th>
                <th style="background-color: #12834d; color: #fff; width: 250px;">Comentarios del Candidato</th>
                <th style="background-color: #0f633d; color: #fff; width: 280px;">Seguimiento Interno RH</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($postulaciones)): ?>
                <tr>
                    <td colspan="13" style="text-align: center; padding: 25px; color: #64748b; font-size: 11pt;">
                        No se encontraron postulaciones que coincidan con los criterios seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($postulaciones as $idx => $p): ?>
                    <?php
                    $claseZebra = ($idx % 2 === 1) ? 'class="zebra"' : '';
                    $vacanteNombre = !empty($p['id_bolsa'])
                        ? ($p['vacante_titulo'] ?: 'Vacante sin título')
                        : 'CV General (Espontánea)';
                    $colorEstado = estadoColor((string)$p['estado']);
                    ?>
                    <tr <?php echo $claseZebra; ?>>
                        <td class="num-cell" style="font-weight: bold;"><?php echo $p['id']; ?></td>
                        <td class="num-cell"><?php echo $p['fecha_postulacion'] ? date('d/m/Y H:i', strtotime($p['fecha_postulacion'])) : 'Sin fecha'; ?></td>
                        <td><strong><?php echo e($p['nombre_completo']); ?></strong></td>
                        <td style="mso-number-format:'\@';"><?php echo e($p['correo']); ?></td>
                        <td style="mso-number-format:'\@';"><?php echo e($p['telefono']); ?></td>
                        <td>
                            <?php if (empty($p['id_bolsa'])): ?>
                                <em style="color: #64748b;"><?php echo e($vacanteNombre); ?></em>
                            <?php else: ?>
                                <strong><?php echo e($vacanteNombre); ?></strong>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($p['vacante_modalidad'] ?: 'No especificada'); ?></td>
                        <td class="num-cell">
                            <span class="badge-status" style="<?php echo $colorEstado; ?>;">
                                <?php echo estadoTexto((string)$p['estado']); ?>
                            </span>
                        </td>
                        <td class="num-cell" style="<?php echo !empty($p['fecha_entrevista']) ? 'font-weight:bold; color:#0f633d;' : 'color:#94a3b8;'; ?>">
                            <?php echo !empty($p['fecha_entrevista']) ? date('d/m/Y H:i', strtotime($p['fecha_entrevista'])) : 'No agendada'; ?>
                        </td>
                        <td class="num-cell" style="<?php echo !empty($p['fecha_ultimo_correo']) ? 'color:#15803d;' : 'color:#94a3b8;'; ?>">
                            <?php if (!empty($p['fecha_ultimo_correo'])): ?>
                                <?php echo date('d/m/Y H:i', strtotime($p['fecha_ultimo_correo'])); ?>
                                <br><small style="font-size: 8pt; color: #64748b;">(<?php echo e($p['tipo_ultimo_correo']); ?>)</small>
                            <?php else: ?>
                                Sin envíos
                            <?php endif; ?>
                        </td>
                        <td class="cv-cell" style="<?php echo !empty($p['cv']) ? 'color:#16a34a;' : 'color:#94a3b8;'; ?>">
                            <?php echo !empty($p['cv']) ? 'SÍ' : 'NO'; ?>
                        </td>
                        <td style="font-size: 9pt;"><?php echo nl2br(e($p['comentarios'])); ?></td>
                        <td style="font-size: 9pt; color: #334155;"><?php echo nl2br(e($p['comentario_seguimiento'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
