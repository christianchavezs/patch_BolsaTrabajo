<?php

require_once __DIR__ . "/../../../includes/config.php";

header('Content-Type: text/html; charset=utf-8');

/* =========================================================
   FUNCIONES AUXILIARES
   ========================================================= */

function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function estadoTexto(string $estado): string
{
    return match ($estado) {
        'nueva' => 'Por revisar',
        'en_proceso' => 'En proceso',
        'entrevista' => 'Entrevista',
        'aceptado' => 'Aceptado',
        'descartado' => 'Descartado',
        default => ucwords(str_replace('_', ' ', $estado))
    };
}

function estadoClase(string $estado): string
{
    return match ($estado) {
        'nueva' => 'review',
        'en_proceso' => 'process',
        'entrevista' => 'interview',
        'aceptado' => 'accepted',
        'descartado' => 'rejected',
        default => 'review'
    };
}

function formatearFecha($fecha): string
{
    if (empty($fecha)) {
        return 'Sin fecha';
    }

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return e($fecha);
    }

    return date('d/m/Y H:i', $timestamp);
}

function formatearFechaCorta($fecha): string
{
    if (empty($fecha)) {
        return 'Sin fecha';
    }

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return e($fecha);
    }

    return date('d/m/Y', $timestamp);
}

function iniciales(string $nombre): string
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return '?';
    }

    $partes = preg_split('/\s+/', $nombre);
    $resultado = '';

    if (!empty($partes[0])) {
        $resultado .= mb_substr($partes[0], 0, 1);
    }

    if (count($partes) > 1) {
        $ultima = $partes[count($partes) - 1];
        $resultado .= mb_substr($ultima, 0, 1);
    }

    return mb_strtoupper($resultado);
}

function valorSeguro($valor, string $default = 'No especificado'): string
{
    $valor = trim((string)($valor ?? ''));

    return $valor !== '' ? $valor : $default;
}

function nombreArchivo($ruta): string
{
    $ruta = trim((string)($ruta ?? ''));

    if ($ruta === '') {
        return 'CV.pdf';
    }

    $ruta = str_replace('\\', '/', $ruta);
    $nombre = basename($ruta);

    return $nombre !== '' ? $nombre : 'CV.pdf';
}

/* =========================================================
   ESTADOS PERMITIDOS
   ========================================================= */

$estadosPermitidos = [
    'nueva',
    'en_proceso',
    'entrevista',
    'aceptado',
    'descartado'
];

/* =========================================================
   IDENTIFICADORES
   ========================================================= */

$idPostulacion = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

$idBolsa = isset($_GET['vacante'])
    ? (int)$_GET['vacante']
    : (
        isset($_GET['id_bolsa'])
            ? (int)$_GET['id_bolsa']
            : 0
    );

/*
 * Si llega un POST, conservamos el modo desde los campos ocultos.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($idPostulacion <= 0) {
        $idPostulacion = isset($_POST['id'])
            ? (int)$_POST['id']
            : 0;
    }

    if ($idBolsa <= 0) {
        $idBolsa = isset($_POST['id_bolsa'])
            ? (int)$_POST['id_bolsa']
            : 0;
    }
}

/*
 * Si existe id_bolsa, esta vista funciona como dashboard
 * de la vacante. Si existe id, funciona como detalle
 * individual de la postulación.
 */
$modoDashboardVacante = $idBolsa > 0 && $idPostulacion <= 0;

/* =========================================================
   VARIABLES
   ========================================================= */

$mensajeOperacion = '';
$errorOperacion = '';

$postulacion = null;
$vacante = null;
$postulacionesVacante = [];

/* =========================================================
   ACTUALIZAR ESTADO
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'actualizar_estado'
) {

    $id = isset($_POST['id'])
        ? (int)$_POST['id']
        : 0;

    $estado = trim(
        (string)($_POST['estado'] ?? '')
    );

    if ($id <= 0) {

        $errorOperacion =
            'La postulación indicada no es válida.';

    } elseif (!in_array($estado, $estadosPermitidos, true)) {

        $errorOperacion =
            'El estado seleccionado no es válido.';

    } else {

        $stmt = $conn->prepare("
            UPDATE patch_postulaciones
            SET estado = ?
            WHERE id = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param(
                'si',
                $estado,
                $id
            );

            if ($stmt->execute()) {

                $mensajeOperacion =
                    'El estado de la postulación se actualizó correctamente.';

            } else {

                $errorOperacion =
                    'No fue posible actualizar el estado de la postulación.';

            }

            $stmt->close();

        } else {

            $errorOperacion =
                'No fue posible preparar la actualización.';

        }
    }

    /*
     * Después de actualizar una postulación desde el dashboard,
     * conservamos la vista de la vacante.
     */
    if ($idBolsa > 0) {
        $modoDashboardVacante = true;
    } else {
        $idPostulacion = $id;
    }
}

/* =========================================================
   MODO DASHBOARD DE VACANTE
   ========================================================= */

if ($modoDashboardVacante) {

    /*
     * Primero obtenemos la información de la vacante.
     */
    $stmtVacante = $conn->prepare("
        SELECT
            id,
            token,
            titulo,
            descripcion,
            ubicacion,
            tipo_jornada,
            modalidad,
            lo_que_se_ofrece,
            requisitos,
            responsabilidades,
            activo,
            archivada,
            solo_imagen,
            url_imagen,
            fecha_publicacion,
            fecha_cierre
        FROM patch_bolsatrabajo
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmtVacante) {

        $stmtVacante->bind_param(
            'i',
            $idBolsa
        );

        $stmtVacante->execute();

        $resultVacante =
            $stmtVacante->get_result();

        if ($resultVacante) {

            $vacante =
                $resultVacante->fetch_assoc();

            $resultVacante->free();
        }

        $stmtVacante->close();
    }

    /*
     * Si la vacante existe, obtenemos exclusivamente
     * las postulaciones pertenecientes a esa vacante.
     */
    if ($vacante) {

        $stmtPostulaciones = $conn->prepare("
            SELECT
                id,
                id_bolsa,
                nombre_completo,
                correo,
                telefono,
                cv,
                comentarios,
                estado,
                fecha_postulacion,
                updated_at
            FROM patch_postulaciones
            WHERE id_bolsa = ?
            ORDER BY fecha_postulacion DESC, id DESC
        ");

        if ($stmtPostulaciones) {

            $stmtPostulaciones->bind_param(
                'i',
                $idBolsa
            );

            $stmtPostulaciones->execute();

            $resultPostulaciones =
                $stmtPostulaciones->get_result();

            if ($resultPostulaciones) {

                while (
                    $fila =
                    $resultPostulaciones->fetch_assoc()
                ) {
                    $postulacionesVacante[] = $fila;
                }

                $resultPostulaciones->free();
            }

            $stmtPostulaciones->close();
        }
    }

    /*
     * Conteos del dashboard.
     */
    $totalPostulacionesVacante =
        count($postulacionesVacante);

    $pendientesVacante = 0;
    $enProcesoVacante = 0;
    $entrevistasVacante = 0;
    $aceptadosVacante = 0;
    $descartadosVacante = 0;

    foreach ($postulacionesVacante as $fila) {

        $estadoFila =
            trim((string)($fila['estado'] ?? ''));

        switch ($estadoFila) {

            case 'nueva':
                $pendientesVacante++;
                break;

            case 'en_proceso':
                $enProcesoVacante++;
                break;

            case 'entrevista':
                $entrevistasVacante++;
                break;

            case 'aceptado':
                $aceptadosVacante++;
                break;

            case 'descartado':
                $descartadosVacante++;
                break;
        }
    }

    /*
     * Estado de la vacante.
     */
    $vacanteArchivada =
        (int)($vacante['archivada'] ?? 0) === 1;

    $vacanteActiva =
        (int)($vacante['activo'] ?? 0) === 1;

    if ($vacanteArchivada) {

        $estadoVacanteTexto = 'Archivada';
        $estadoVacanteClase = 'archived';
        $estadoVacanteIcono = 'fa-box-archive';

    } elseif ($vacanteActiva) {

        $estadoVacanteTexto = 'Activa';
        $estadoVacanteClase = 'active';
        $estadoVacanteIcono = 'fa-circle-check';

    } else {

        $estadoVacanteTexto = 'Inactiva';
        $estadoVacanteClase = 'inactive';
        $estadoVacanteIcono = 'fa-circle-pause';
    }

    $tituloVacanteDashboard =
        valorSeguro(
            $vacante['titulo'] ?? '',
            'Vacante sin título'
        );

    $ubicacionVacanteDashboard =
        valorSeguro(
            $vacante['ubicacion'] ?? '',
            'No especificada'
        );

    $jornadaVacanteDashboard =
        valorSeguro(
            $vacante['tipo_jornada'] ?? '',
            'No especificada'
        );

    $modalidadVacanteDashboard =
        valorSeguro(
            $vacante['modalidad'] ?? '',
            'No especificada'
        );

    $descripcionDashboard =
        trim(
            (string)($vacante['descripcion'] ?? '')
        );

    $ofreceDashboard =
        trim(
            (string)($vacante['lo_que_se_ofrece'] ?? '')
        );

    $requisitosDashboard =
        trim(
            (string)($vacante['requisitos'] ?? '')
        );

    $responsabilidadesDashboard =
        trim(
            (string)($vacante['responsabilidades'] ?? '')
        );

    $soloImagenDashboard =
        (int)($vacante['solo_imagen'] ?? 0);

    $urlImagenDashboard =
        trim(
            (string)($vacante['url_imagen'] ?? '')
        );

    /*
     * Ruta de imagen.
     */
    $rutaImagenDashboard = '';

    if ($urlImagenDashboard !== '') {

        $imagenNormalizada =
            str_replace(
                '\\',
                '/',
                $urlImagenDashboard
            );

        if (
            str_starts_with(
                $imagenNormalizada,
                'http://'
            ) ||
            str_starts_with(
                $imagenNormalizada,
                'https://'
            ) ||
            str_starts_with(
                $imagenNormalizada,
                '/'
            )
        ) {

            $rutaImagenDashboard =
                $imagenNormalizada;

        } elseif (
            str_starts_with(
                $imagenNormalizada,
                'backend/'
            )
        ) {

            $rutaImagenDashboard =
                '../' . $imagenNormalizada;

        } else {

            $rutaImagenDashboard =
                '../../../' .
                $imagenNormalizada;
        }
    }

    $title =
        $tituloVacanteDashboard .
        ' | Postulaciones';

    $description =
        'Dashboard de postulaciones de la vacante ' .
        $tituloVacanteDashboard .
        '.';

    ?>

    <!DOCTYPE html>
    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            <?php echo e($title); ?>
        </title>

        <meta
            name="description"
            content="<?php echo e($description); ?>"
        >

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        >

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        >

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap"
        >

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        >

        <link
            rel="stylesheet"
            href="css/Admin_Detalle_Postulacion.css"
        >

        <style>

            .patch-vacancy-dashboard-header {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                padding: 28px;
                margin-bottom: 24px;
                box-shadow: 0 4px 16px rgba(0,0,0,.04);
            }

            .patch-vacancy-dashboard-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 24px;
            }

            .patch-vacancy-dashboard-title {
                display: flex;
                align-items: flex-start;
                gap: 18px;
                min-width: 0;
            }

            .patch-vacancy-dashboard-icon {
                width: 58px;
                height: 58px;
                min-width: 58px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #eaf7ef;
                color: #16864a;
                font-size: 24px;
            }

            .patch-vacancy-dashboard-title h1 {
                margin: 0 0 7px;
                font-size: 26px;
                line-height: 1.25;
                color: #1f2937;
            }

            .patch-vacancy-dashboard-title p {
                margin: 0;
                color: #6b7280;
                font-size: 14px;
            }

            .patch-vacancy-dashboard-status {
                flex-shrink: 0;
            }

            .patch-vacancy-dashboard-status .patch-vacancy-status {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 13px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 600;
            }

            .patch-vacancy-status.active {
                background: #eaf8ef;
                color: #16864a;
            }

            .patch-vacancy-status.inactive {
                background: #f3f4f6;
                color: #6b7280;
            }

            .patch-vacancy-status.archived {
                background: #fff4e5;
                color: #b96b00;
            }

            .patch-vacancy-dashboard-meta {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-top: 22px;
            }

            .patch-vacancy-dashboard-meta-item {
                display: flex;
                align-items: center;
                gap: 11px;
                padding: 13px 15px;
                border: 1px solid #edf0f2;
                border-radius: 10px;
                background: #fafbfc;
                min-width: 0;
            }

            .patch-vacancy-dashboard-meta-item i {
                width: 36px;
                height: 36px;
                min-width: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 9px;
                background: #edf8f2;
                color: #16864a;
            }

            .patch-vacancy-dashboard-meta-item div {
                min-width: 0;
            }

            .patch-vacancy-dashboard-meta-item span {
                display: block;
                color: #8a929c;
                font-size: 11px;
                margin-bottom: 3px;
            }

            .patch-vacancy-dashboard-meta-item strong {
                display: block;
                color: #303841;
                font-size: 13px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .patch-vacancy-dashboard-stats {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 14px;
                margin-bottom: 24px;
            }

            .patch-vacancy-dashboard-stat {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                padding: 19px;
                box-shadow: 0 3px 12px rgba(0,0,0,.03);
            }

            .patch-vacancy-dashboard-stat-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .patch-vacancy-dashboard-stat-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f3f4f6;
                color: #69717a;
            }

            .patch-vacancy-dashboard-stat-value {
                display: block;
                margin-top: 12px;
                font-size: 28px;
                line-height: 1;
                font-weight: 700;
                color: #20262d;
            }

            .patch-vacancy-dashboard-stat-label {
                display: block;
                margin-top: 7px;
                font-size: 12px;
                color: #7b838c;
            }

            .patch-vacancy-dashboard-stat.review .patch-vacancy-dashboard-stat-icon {
                background: #fff5dd;
                color: #c28700;
            }

            .patch-vacancy-dashboard-stat.process .patch-vacancy-dashboard-stat-icon {
                background: #edf5ff;
                color: #3978b8;
            }

            .patch-vacancy-dashboard-stat.interview .patch-vacancy-dashboard-stat-icon {
                background: #f4edff;
                color: #7a54b5;
            }

            .patch-vacancy-dashboard-stat.accepted .patch-vacancy-dashboard-stat-icon {
                background: #eaf8ef;
                color: #16864a;
            }

            .patch-vacancy-dashboard-stat.rejected .patch-vacancy-dashboard-stat-icon {
                background: #fff0f0;
                color: #bd5555;
            }

            .patch-vacancy-dashboard-panel {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                padding: 24px;
                margin-bottom: 24px;
                box-shadow: 0 4px 16px rgba(0,0,0,.03);
            }

            .patch-vacancy-dashboard-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 20px;
            }

            .patch-vacancy-dashboard-panel-header h2 {
                margin: 0 0 5px;
                color: #242b32;
                font-size: 20px;
            }

            .patch-vacancy-dashboard-panel-header p {
                margin: 0;
                color: #7b838c;
                font-size: 13px;
            }

            .patch-vacancy-dashboard-table-wrapper {
                width: 100%;
                overflow-x: auto;
            }

            .patch-vacancy-dashboard-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 820px;
            }

            .patch-vacancy-dashboard-table th {
                padding: 12px 14px;
                text-align: left;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: #7c858e;
                background: #f8faf9;
                border-bottom: 1px solid #e5e7eb;
            }

            .patch-vacancy-dashboard-table td {
                padding: 14px;
                border-bottom: 1px solid #edf0f2;
                vertical-align: middle;
                color: #3d454d;
                font-size: 13px;
            }

            .patch-vacancy-dashboard-table tr:last-child td {
                border-bottom: 0;
            }

            .patch-vacancy-dashboard-candidate {
                display: flex;
                align-items: center;
                gap: 11px;
                min-width: 190px;
            }

            .patch-vacancy-dashboard-avatar {
                width: 38px;
                height: 38px;
                min-width: 38px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #eaf7ef;
                color: #16864a;
                font-size: 12px;
                font-weight: 700;
            }

            .patch-vacancy-dashboard-candidate strong {
                display: block;
                color: #252b31;
                font-size: 13px;
                margin-bottom: 3px;
            }

            .patch-vacancy-dashboard-candidate span {
                display: block;
                color: #8a929b;
                font-size: 11px;
            }

            .patch-dashboard-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
            }

            .patch-dashboard-status.review {
                background: #fff5dd;
                color: #a87500;
            }

            .patch-dashboard-status.process {
                background: #edf5ff;
                color: #3978b8;
            }

            .patch-dashboard-status.interview {
                background: #f4edff;
                color: #7250a9;
            }

            .patch-dashboard-status.accepted {
                background: #eaf8ef;
                color: #16864a;
            }

            .patch-dashboard-status.rejected {
                background: #fff0f0;
                color: #b65353;
            }

            .patch-dashboard-actions {
                display: flex;
                align-items: center;
                gap: 7px;
                flex-wrap: wrap;
            }

            .patch-dashboard-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                min-height: 34px;
                padding: 7px 11px;
                border: 1px solid #dfe4e7;
                border-radius: 8px;
                background: #ffffff;
                color: #4d5963;
                text-decoration: none;
                font-size: 11px;
                font-weight: 600;
                cursor: pointer;
            }

            .patch-dashboard-action:hover {
                background: #f6faf8;
                border-color: #b9d9c6;
                color: #16864a;
            }

            .patch-dashboard-action.primary {
                background: #16864a;
                border-color: #16864a;
                color: #ffffff;
            }

            .patch-dashboard-action.primary:hover {
                background: #11713d;
                color: #ffffff;
            }

            .patch-dashboard-state-form {
                display: flex;
                align-items: center;
                gap: 7px;
            }

            .patch-dashboard-state-form select {
                height: 34px;
                border: 1px solid #dfe4e7;
                border-radius: 8px;
                padding: 0 28px 0 9px;
                background: #ffffff;
                color: #4b5560;
                font-size: 11px;
                outline: none;
            }

            .patch-dashboard-state-form select:focus {
                border-color: #16864a;
            }

            .patch-vacancy-content-dashboard {
                color: #4b5560;
                font-size: 14px;
                line-height: 1.7;
            }

            .patch-vacancy-content-dashboard h3 {
                margin: 0 0 10px;
                font-size: 16px;
                color: #293139;
            }

            .patch-vacancy-content-dashboard + .patch-vacancy-content-dashboard {
                margin-top: 25px;
                padding-top: 25px;
                border-top: 1px solid #edf0f2;
            }

            .patch-vacancy-image-dashboard {
                display: block;
                width: 100%;
                max-height: 650px;
                object-fit: contain;
                border-radius: 12px;
                background: #f7f8f9;
            }

            .patch-dashboard-empty {
                text-align: center;
                padding: 55px 20px;
                color: #7b838c;
            }

            .patch-dashboard-empty-icon {
                width: 58px;
                height: 58px;
                margin: 0 auto 15px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #edf8f2;
                color: #16864a;
                font-size: 22px;
            }

            .patch-dashboard-empty h3 {
                margin: 0 0 7px;
                color: #343b42;
                font-size: 17px;
            }

            .patch-dashboard-empty p {
                margin: 0;
                font-size: 13px;
            }

            .patch-dashboard-back {
                margin-bottom: 18px;
            }

            @media (max-width: 1100px) {

                .patch-vacancy-dashboard-stats {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

            }

            @media (max-width: 800px) {

                .patch-vacancy-dashboard-top {
                    flex-direction: column;
                }

                .patch-vacancy-dashboard-meta {
                    grid-template-columns: 1fr;
                }

                .patch-vacancy-dashboard-stats {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

            }

            @media (max-width: 520px) {

                .patch-vacancy-dashboard-header,
                .patch-vacancy-dashboard-panel {
                    padding: 18px;
                }

                .patch-vacancy-dashboard-stats {
                    grid-template-columns: 1fr;
                }

                .patch-vacancy-dashboard-title h1 {
                    font-size: 21px;
                }

            }

        </style>

    </head>

    <body>

    <div class="patch-detail-wrapper">

        <div class="patch-detail-container">

            <div class="patch-dashboard-back">

                <a
                    href="Admin_Postulaciones.php"
                    class="patch-detail-btn"
                >
                    <i class="fa fa-arrow-left"></i>
                    Regresar a postulaciones
                </a>

            </div>


            <?php if (!$vacante): ?>

                <div class="patch-detail-error">

                    <div class="patch-detail-error-icon">
                        <i class="fa fa-circle-exclamation"></i>
                    </div>

                    <h1>
                        Vacante no encontrada
                    </h1>

                    <p>
                        La vacante que intentas consultar no existe
                        o ya no se encuentra disponible.
                    </p>

                    <a
                        href="Admin_Postulaciones.php"
                        class="patch-detail-error-button"
                    >
                        <i class="fa fa-arrow-left"></i>
                        Regresar a postulaciones
                    </a>

                </div>

            <?php else: ?>


                <div class="patch-vacancy-dashboard-header">

                    <div class="patch-vacancy-dashboard-top">

                        <div class="patch-vacancy-dashboard-title">

                            <div class="patch-vacancy-dashboard-icon">
                                <i class="fa fa-briefcase"></i>
                            </div>

                            <div>

                                <h1>
                                    <?php echo e($tituloVacanteDashboard); ?>
                                </h1>

                                <p>
                                    Dashboard de postulaciones de esta vacante.
                                </p>

                            </div>

                        </div>


                        <div class="patch-vacancy-dashboard-status">

                            <span class="patch-vacancy-status <?php echo e($estadoVacanteClase); ?>">

                                <i class="fa <?php echo e($estadoVacanteIcono); ?>"></i>

                                <?php echo e($estadoVacanteTexto); ?>

                            </span>

                        </div>

                    </div>


                    <div class="patch-vacancy-dashboard-meta">

                        <div class="patch-vacancy-dashboard-meta-item">

                            <i class="fa fa-location-dot"></i>

                            <div>

                                <span>
                                    Ubicación
                                </span>

                                <strong>
                                    <?php echo e($ubicacionVacanteDashboard); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="patch-vacancy-dashboard-meta-item">

                            <i class="fa fa-clock"></i>

                            <div>

                                <span>
                                    Tipo de jornada
                                </span>

                                <strong>
                                    <?php echo e($jornadaVacanteDashboard); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="patch-vacancy-dashboard-meta-item">

                            <i class="fa fa-building"></i>

                            <div>

                                <span>
                                    Modalidad
                                </span>

                                <strong>
                                    <?php echo e($modalidadVacanteDashboard); ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="patch-vacancy-dashboard-stats">

                    <div class="patch-vacancy-dashboard-stat">

                        <div class="patch-vacancy-dashboard-stat-top">

                            <span class="patch-vacancy-dashboard-stat-label">
                                Total
                            </span>

                            <span class="patch-vacancy-dashboard-stat-icon">
                                <i class="fa fa-users"></i>
                            </span>

                        </div>

                        <strong class="patch-vacancy-dashboard-stat-value">
                            <?php echo $totalPostulacionesVacante; ?>
                        </strong>

                        <span class="patch-vacancy-dashboard-stat-label">
                            Postulaciones recibidas
                        </span>

                    </div>


                    <div class="patch-vacancy-dashboard-stat review">

                        <div class="patch-vacancy-dashboard-stat-top">

                            <span class="patch-vacancy-dashboard-stat-label">
                                Por revisar
                            </span>

                            <span class="patch-vacancy-dashboard-stat-icon">
                                <i class="fa fa-clock"></i>
                            </span>

                        </div>

                        <strong class="patch-vacancy-dashboard-stat-value">
                            <?php echo $pendientesVacante; ?>
                        </strong>

                        <span class="patch-vacancy-dashboard-stat-label">
                            Nuevas
                        </span>

                    </div>


                    <div class="patch-vacancy-dashboard-stat process">

                        <div class="patch-vacancy-dashboard-stat-top">

                            <span class="patch-vacancy-dashboard-stat-label">
                                En proceso
                            </span>

                            <span class="patch-vacancy-dashboard-stat-icon">
                                <i class="fa fa-spinner"></i>
                            </span>

                        </div>

                        <strong class="patch-vacancy-dashboard-stat-value">
                            <?php echo $enProcesoVacante; ?>
                        </strong>

                        <span class="patch-vacancy-dashboard-stat-label">
                            Candidatos en proceso
                        </span>

                    </div>


                    <div class="patch-vacancy-dashboard-stat interview">

                        <div class="patch-vacancy-dashboard-stat-top">

                            <span class="patch-vacancy-dashboard-stat-label">
                                Entrevistas
                            </span>

                            <span class="patch-vacancy-dashboard-stat-icon">
                                <i class="fa fa-comments"></i>
                            </span>

                        </div>

                        <strong class="patch-vacancy-dashboard-stat-value">
                            <?php echo $entrevistasVacante; ?>
                        </strong>

                        <span class="patch-vacancy-dashboard-stat-label">
                            En entrevista
                        </span>

                    </div>


                    <div class="patch-vacancy-dashboard-stat accepted">

                        <div class="patch-vacancy-dashboard-stat-top">

                            <span class="patch-vacancy-dashboard-stat-label">
                                Aceptados
                            </span>

                            <span class="patch-vacancy-dashboard-stat-icon">
                                <i class="fa fa-circle-check"></i>
                            </span>

                        </div>

                        <strong class="patch-vacancy-dashboard-stat-value">
                            <?php echo $aceptadosVacante; ?>
                        </strong>

                        <span class="patch-vacancy-dashboard-stat-label">
                            Candidatos aceptados
                        </span>

                    </div>

                </div>


                <section class="patch-vacancy-dashboard-panel">

                    <div class="patch-vacancy-dashboard-panel-header">

                        <div>

                            <h2>
                                Postulaciones de esta vacante
                            </h2>

                            <p>
                                Consulta y administra exclusivamente los candidatos que se postularon a esta vacante.
                            </p>

                        </div>

                    </div>


                    <?php if (!empty($postulacionesVacante)): ?>

                        <div class="patch-vacancy-dashboard-table-wrapper">

                            <table class="patch-vacancy-dashboard-table">

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
                                            Acciones
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                <?php foreach ($postulacionesVacante as $fila): ?>

                                    <?php

                                    $idFila =
                                        (int)($fila['id'] ?? 0);

                                    $nombreFila =
                                        valorSeguro(
                                            $fila['nombre_completo'] ?? '',
                                            'Candidato'
                                        );

                                    $correoFila =
                                        valorSeguro(
                                            $fila['correo'] ?? '',
                                            'Sin correo'
                                        );

                                    $telefonoFila =
                                        valorSeguro(
                                            $fila['telefono'] ?? '',
                                            'Sin teléfono'
                                        );

                                    $estadoFila =
                                        trim(
                                            (string)($fila['estado'] ?? 'nueva')
                                        );

                                    if (
                                        !in_array(
                                            $estadoFila,
                                            $estadosPermitidos,
                                            true
                                        )
                                    ) {
                                        $estadoFila = 'nueva';
                                    }

                                    $cvFila =
                                        trim(
                                            (string)($fila['cv'] ?? '')
                                        );

                                    $fechaFila =
                                        $fila['fecha_postulacion'] ?? null;

                                    ?>

                                    <tr>

                                        <td>

                                            <div class="patch-vacancy-dashboard-candidate">

                                                <div class="patch-vacancy-dashboard-avatar">
                                                    <?php echo e(iniciales($nombreFila)); ?>
                                                </div>

                                                <div>

                                                    <strong>
                                                        <?php echo e($nombreFila); ?>
                                                    </strong>

                                                    <span>
                                                        <?php echo e($correoFila); ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>


                                        <td>
                                            <?php echo e($telefonoFila); ?>
                                        </td>


                                        <td>
                                            <?php echo e(formatearFecha($fechaFila)); ?>
                                        </td>


                                        <td>

                                            <span class="patch-dashboard-status <?php echo e(estadoClase($estadoFila)); ?>">

                                                <i class="fa fa-circle"></i>

                                                <?php echo e(estadoTexto($estadoFila)); ?>

                                            </span>

                                        </td>


                                        <td>

                                            <div class="patch-dashboard-actions">

                                                <?php if ($idFila > 0): ?>

                                                    <a
                                                        href="Admin_Detalle_Postulacion.php?id=<?php echo $idFila; ?>"
                                                        class="patch-dashboard-action primary"
                                                        title="Ver detalle de la postulación"
                                                    >
                                                        <i class="fa fa-eye"></i>
                                                        Ver detalle
                                                    </a>

                                                <?php endif; ?>


                                                <?php if ($cvFila !== ''): ?>

                                                    <a
                                                        href="api/ver_cv.php?id=<?php echo $idFila; ?>"
                                                        class="patch-dashboard-action"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        title="Ver CV"
                                                    >
                                                        <i class="fa fa-file-pdf"></i>
                                                        CV
                                                    </a>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="patch-dashboard-empty">

                            <div class="patch-dashboard-empty-icon">
                                <i class="fa fa-users-slash"></i>
                            </div>

                            <h3>
                                Esta vacante aún no tiene postulaciones
                            </h3>

                            <p>
                                Cuando un candidato se postule a esta vacante,
                                aparecerá aquí.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>


                <?php

                $hayContenidoDashboard =
                    $descripcionDashboard !== '' ||
                    $ofreceDashboard !== '' ||
                    $requisitosDashboard !== '' ||
                    $responsabilidadesDashboard !== '' ||
                    (
                        $soloImagenDashboard === 1 &&
                        $rutaImagenDashboard !== ''
                    );

                ?>

                <?php if ($hayContenidoDashboard): ?>

                    <section class="patch-vacancy-dashboard-panel">

                        <div class="patch-vacancy-dashboard-panel-header">

                            <div>

                                <h2>
                                    Información de la vacante
                                </h2>

                                <p>
                                    Información publicada originalmente en esta vacante.
                                </p>

                            </div>

                        </div>


                        <?php if (
                            $soloImagenDashboard === 1 &&
                            $rutaImagenDashboard !== ''
                        ): ?>

                            <img
                                src="<?php echo e($rutaImagenDashboard); ?>"
                                alt="<?php echo e($tituloVacanteDashboard); ?>"
                                class="patch-vacancy-image-dashboard"
                            >

                        <?php else: ?>

                            <?php if ($descripcionDashboard !== ''): ?>

                                <div class="patch-vacancy-content-dashboard">

                                    <h3>
                                        Descripción del puesto
                                    </h3>

                                    <div>
                                        <?php echo $descripcionDashboard; ?>
                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if ($ofreceDashboard !== ''): ?>

                                <div class="patch-vacancy-content-dashboard">

                                    <h3>
                                        Lo que se ofrece
                                    </h3>

                                    <div>
                                        <?php echo $ofreceDashboard; ?>
                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if ($requisitosDashboard !== ''): ?>

                                <div class="patch-vacancy-content-dashboard">

                                    <h3>
                                        Requisitos
                                    </h3>

                                    <div>
                                        <?php echo $requisitosDashboard; ?>
                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if ($responsabilidadesDashboard !== ''): ?>

                                <div class="patch-vacancy-content-dashboard">

                                    <h3>
                                        Responsabilidades
                                    </h3>

                                    <div>
                                        <?php echo $responsabilidadesDashboard; ?>
                                    </div>

                                </div>

                            <?php endif; ?>

                        <?php endif; ?>

                    </section>

                <?php endif; ?>


            <?php endif; ?>

        </div>

    </div>


    <div
        class="patch-toast <?php echo $mensajeOperacion !== '' ? 'show success' : ($errorOperacion !== '' ? 'show error' : ''); ?>"
        id="patchToast"
    >

        <?php if ($mensajeOperacion !== ''): ?>

            <i class="fa fa-circle-check"></i>

            <span>
                <?php echo e($mensajeOperacion); ?>
            </span>

        <?php elseif ($errorOperacion !== ''): ?>

            <i class="fa fa-circle-exclamation"></i>

            <span>
                <?php echo e($errorOperacion); ?>
            </span>

        <?php endif; ?>

    </div>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const toast =
                document.getElementById('patchToast');

            if (
                !toast ||
                !toast.classList.contains('show')
            ) {
                return;
            }

            setTimeout(function () {

                toast.classList.remove('show');

            }, 4000);

        });

    </script>

    </body>
    </html>

    <?php

    exit;
}

/* =========================================================
   MODO DETALLE INDIVIDUAL
   ========================================================= */

if ($idPostulacion > 0) {

    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.id_bolsa,
            p.nombre_completo,
            p.correo,
            p.telefono,
            p.cv,
            p.comentarios,
            p.estado,
            p.fecha_postulacion,
            p.updated_at,
            b.id AS vacante_id,
            b.token AS vacante_token,
            b.titulo AS vacante_titulo,
            b.descripcion AS vacante_descripcion,
            b.ubicacion AS vacante_ubicacion,
            b.tipo_jornada AS vacante_jornada,
            b.modalidad AS vacante_modalidad,
            b.lo_que_se_ofrece AS vacante_ofrece,
            b.requisitos AS vacante_requisitos,
            b.responsabilidades AS vacante_responsabilidades,
            b.activo AS vacante_activo,
            b.archivada AS vacante_archivada,
            b.solo_imagen AS vacante_solo_imagen,
            b.url_imagen AS vacante_url_imagen,
            b.fecha_publicacion AS vacante_fecha_publicacion,
            b.fecha_cierre AS vacante_fecha_cierre
        FROM patch_postulaciones p
        LEFT JOIN patch_bolsatrabajo b
            ON b.id = p.id_bolsa
        WHERE p.id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            'i',
            $idPostulacion
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        if ($result) {

            $postulacion =
                $result->fetch_assoc();

            $result->free();
        }

        $stmt->close();
    }
}

/* =========================================================
   POSTULACIÓN NO ENCONTRADA
   ========================================================= */

if (!$postulacion) {

    http_response_code(404);

    $title =
        'Postulación no encontrada | Bolsa de Trabajo';

    $description =
        'No fue posible encontrar la postulación indicada.';

    ?>

    <!DOCTYPE html>
    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            <?php echo e($title); ?>
        </title>

        <meta
            name="description"
            content="<?php echo e($description); ?>"
        >

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap"
        >

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        >

        <link
            rel="stylesheet"
            href="css/Admin_Detalle_Postulacion.css"
        >

    </head>

    <body>

    <div class="patch-detail-wrapper">

        <div class="patch-detail-container">

            <div class="patch-detail-actions">

                <a
                    href="Admin_Postulaciones.php"
                    class="patch-detail-btn"
                >
                    <i class="fa fa-arrow-left"></i>
                    Regresar a postulaciones
                </a>

            </div>


            <div class="patch-detail-error">

                <div class="patch-detail-error-icon">

                    <i class="fa fa-circle-exclamation"></i>

                </div>

                <h1>
                    Postulación no encontrada
                </h1>

                <p>
                    La postulación que intentas consultar no existe
                    o ya no se encuentra disponible.
                </p>

                <a
                    href="Admin_Postulaciones.php"
                    class="patch-detail-error-button"
                >
                    <i class="fa fa-arrow-left"></i>
                    Regresar a postulaciones
                </a>

            </div>

        </div>

    </div>

    </body>

    </html>

    <?php

    exit;
}

/* =========================================================
   DATOS DE LA POSTULACIÓN
   ========================================================= */

$nombre = valorSeguro(
    $postulacion['nombre_completo'],
    'Candidato'
);

$correo = valorSeguro(
    $postulacion['correo'],
    'Sin correo'
);

$telefono = valorSeguro(
    $postulacion['telefono'],
    'Sin teléfono'
);

$comentarios = trim(
    (string)($postulacion['comentarios'] ?? '')
);

$estadoActual = trim(
    (string)($postulacion['estado'] ?? 'nueva')
);

if (!in_array(
    $estadoActual,
    $estadosPermitidos,
    true
)) {
    $estadoActual = 'nueva';
}

$fechaPostulacion =
    $postulacion['fecha_postulacion'] ?? null;

$fechaActualizacion =
    $postulacion['updated_at'] ?? null;

$cv = trim(
    (string)($postulacion['cv'] ?? '')
);

$tieneCV =
    $cv !== '';

$nombreCV =
    nombreArchivo($cv);

$vacanteId =
    isset($postulacion['vacante_id'])
        ? (int)$postulacion['vacante_id']
        : 0;

$tituloVacante =
    trim(
        (string)($postulacion['vacante_titulo'] ?? '')
    );

$esPostulacionVacante =
    $vacanteId > 0 &&
    $tituloVacante !== '';

$ubicacion =
    valorSeguro(
        $postulacion['vacante_ubicacion'] ?? '',
        'No especificada'
    );

$jornada =
    valorSeguro(
        $postulacion['vacante_jornada'] ?? '',
        'No especificada'
    );

$modalidad =
    valorSeguro(
        $postulacion['vacante_modalidad'] ?? '',
        'No especificada'
    );

$descripcionVacante =
    trim(
        (string)($postulacion['vacante_descripcion'] ?? '')
    );

$ofreceVacante =
    trim(
        (string)($postulacion['vacante_ofrece'] ?? '')
    );

$requisitosVacante =
    trim(
        (string)($postulacion['vacante_requisitos'] ?? '')
    );

$responsabilidadesVacante =
    trim(
        (string)($postulacion['vacante_responsabilidades'] ?? '')
    );

$soloImagen =
    (int)($postulacion['vacante_solo_imagen'] ?? 0);

$urlImagen =
    trim(
        (string)($postulacion['vacante_url_imagen'] ?? '')
    );

/* =========================================================
   RUTA DE LA IMAGEN
   ========================================================= */

$rutaImagenVacante = '';

if ($urlImagen !== '') {

    $imagenNormalizada =
        str_replace(
            '\\',
            '/',
            $urlImagen
        );

    if (
        str_starts_with(
            $imagenNormalizada,
            'http://'
        ) ||
        str_starts_with(
            $imagenNormalizada,
            'https://'
        ) ||
        str_starts_with(
            $imagenNormalizada,
            '/'
        )
    ) {

        $rutaImagenVacante =
            $imagenNormalizada;

    } elseif (
        str_starts_with(
            $imagenNormalizada,
            'backend/'
        )
    ) {

        $rutaImagenVacante =
            '../' . $imagenNormalizada;

    } else {

        $rutaImagenVacante =
            '../../../' .
            $imagenNormalizada;
    }
}

/* =========================================================
   URL PARA VER CV
   ========================================================= */

$urlCV = '';

if ($tieneCV) {

    $urlCV =
        'api/ver_cv.php?id=' .
        $idPostulacion;
}

/* =========================================================
   REGRESAR
   ========================================================= */

if ($vacanteId > 0) {

    $urlRegresar =
        'Admin_Detalle_Postulacion.php?id_bolsa=' .
        $vacanteId;

    $textoRegresar =
        'Regresar a la vacante';

} else {

    $urlRegresar =
        'Admin_Postulaciones.php';

    $textoRegresar =
        'Regresar a postulaciones';
}

/* =========================================================
   TÍTULO
   ========================================================= */

$title =
    $nombre .
    ' | Detalle de postulación';

$description =
    'Detalle de la postulación de ' .
    $nombre .
    ' en la Bolsa de Trabajo de Kombitec.';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo e($title); ?>
    </title>

    <meta
        name="description"
        content="<?php echo e($description); ?>"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="css/Admin_Detalle_Postulacion.css"
    >

</head>

<body>

<div class="patch-detail-wrapper">

    <div class="patch-detail-container">

        <div class="patch-detail-actions">

            <a
                href="<?php echo e($urlRegresar); ?>"
                class="patch-detail-btn"
            >
                <i class="fa fa-arrow-left"></i>
                <?php echo e($textoRegresar); ?>
            </a>

        </div>


        <div class="patch-detail-header">

            <div class="patch-detail-header-content">

                <div class="patch-detail-header-icon">
                    <?php echo e(iniciales($nombre)); ?>
                </div>

                <div class="patch-detail-header-info">

                    <div class="patch-detail-breadcrumb">

                        <a href="Admin_BolsaTrabajo.php">
                            Bolsa de Trabajo
                        </a>

                        <i class="fa fa-angle-right"></i>

                        <a href="Admin_Postulaciones.php">
                            Postulaciones
                        </a>

                        <i class="fa fa-angle-right"></i>

                        <span>
                            Detalle de postulante
                        </span>

                    </div>

                    <h1>
                        <?php echo e($nombre); ?>
                    </h1>

                    <p>

                        <?php if ($esPostulacionVacante): ?>

                            Postulación para:

                            <strong>
                                <?php echo e($tituloVacante); ?>
                            </strong>

                        <?php else: ?>

                            CV general recibido por Kombitec.

                        <?php endif; ?>

                    </p>

                </div>

            </div>

        </div>


        <section class="patch-summary-grid">


            <article
                class="patch-summary-card <?php echo e(estadoClase($estadoActual)); ?>"
            >

                <div class="patch-summary-card-header">

                    <div>

                        <div class="patch-summary-card-label">
                            Estado actual
                        </div>

                        <div class="patch-summary-card-value">
                            <?php echo e(estadoTexto($estadoActual)); ?>
                        </div>

                    </div>

                    <div class="patch-summary-card-icon">

                        <?php if ($estadoActual === 'nueva'): ?>

                            <i class="fa fa-clock"></i>

                        <?php elseif ($estadoActual === 'en_proceso'): ?>

                            <i class="fa fa-spinner"></i>

                        <?php elseif ($estadoActual === 'entrevista'): ?>

                            <i class="fa fa-comments"></i>

                        <?php elseif ($estadoActual === 'aceptado'): ?>

                            <i class="fa fa-circle-check"></i>

                        <?php elseif ($estadoActual === 'descartado'): ?>

                            <i class="fa fa-circle-xmark"></i>

                        <?php else: ?>

                            <i class="fa fa-circle-info"></i>

                        <?php endif; ?>

                    </div>

                </div>

                <p class="patch-summary-card-description">
                    Estado actual de la postulación.
                </p>

            </article>


            <article class="patch-summary-card">

                <div class="patch-summary-card-header">

                    <div>

                        <div class="patch-summary-card-label">
                            Vacante
                        </div>

                        <div class="patch-summary-card-value patch-summary-card-value-small">

                            <?php if ($esPostulacionVacante): ?>

                                <?php echo e($tituloVacante); ?>

                            <?php else: ?>

                                CV general

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="patch-summary-card-icon">
                        <i class="fa fa-briefcase"></i>
                    </div>

                </div>

                <p class="patch-summary-card-description">

                    <?php if ($esPostulacionVacante): ?>

                        Vacante a la que se postuló.

                    <?php else: ?>

                        No está asociada a una vacante específica.

                    <?php endif; ?>

                </p>

            </article>


            <article class="patch-summary-card blue">

                <div class="patch-summary-card-header">

                    <div>

                        <div class="patch-summary-card-label">
                            Fecha de postulación
                        </div>

                        <div class="patch-summary-card-value patch-summary-card-value-small">
                            <?php echo e(formatearFechaCorta($fechaPostulacion)); ?>
                        </div>

                    </div>

                    <div class="patch-summary-card-icon">
                        <i class="fa fa-calendar"></i>
                    </div>

                </div>

                <p class="patch-summary-card-description">
                    <?php echo e(formatearFecha($fechaPostulacion)); ?>
                </p>

            </article>


            <article class="patch-summary-card purple">

                <div class="patch-summary-card-header">

                    <div>

                        <div class="patch-summary-card-label">
                            Currículum vitae
                        </div>

                        <div class="patch-summary-card-value patch-summary-card-value-small">

                            <?php if ($tieneCV): ?>

                                Disponible

                            <?php else: ?>

                                No disponible

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="patch-summary-card-icon">
                        <i class="fa fa-file-pdf"></i>
                    </div>

                </div>

                <p class="patch-summary-card-description">

                    <?php if ($tieneCV): ?>

                        Documento PDF recibido.

                    <?php else: ?>

                        Esta postulación no contiene CV.

                    <?php endif; ?>

                </p>

            </article>

        </section>


        <div class="patch-detail-main-grid">


            <section class="patch-detail-panel">

                <div class="patch-detail-panel-header">

                    <div>

                        <h2>
                            Información del postulante
                        </h2>

                        <p>
                            Datos proporcionados por el candidato.
                        </p>

                    </div>

                    <div class="patch-detail-panel-icon">
                        <i class="fa fa-user"></i>
                    </div>

                </div>


                <div class="patch-profile-card">

                    <div class="patch-profile-avatar">
                        <?php echo e(iniciales($nombre)); ?>
                    </div>

                    <div class="patch-profile-info">

                        <h3>
                            <?php echo e($nombre); ?>
                        </h3>

                        <span>
                            <?php echo e(estadoTexto($estadoActual)); ?>
                        </span>

                    </div>

                </div>


                <div class="patch-info-grid">

                    <div class="patch-info-item">

                        <div class="patch-info-icon">
                            <i class="fa fa-envelope"></i>
                        </div>

                        <div>

                            <span class="patch-info-label">
                                Correo electrónico
                            </span>

                            <span class="patch-info-value">
                                <?php echo e($correo); ?>
                            </span>

                        </div>

                    </div>


                    <div class="patch-info-item">

                        <div class="patch-info-icon">
                            <i class="fa fa-phone"></i>
                        </div>

                        <div>

                            <span class="patch-info-label">
                                Teléfono
                            </span>

                            <span class="patch-info-value">
                                <?php echo e($telefono); ?>
                            </span>

                        </div>

                    </div>


                    <div class="patch-info-item">

                        <div class="patch-info-icon">
                            <i class="fa fa-calendar"></i>
                        </div>

                        <div>

                            <span class="patch-info-label">
                                Fecha de postulación
                            </span>

                            <span class="patch-info-value">
                                <?php echo e(formatearFecha($fechaPostulacion)); ?>
                            </span>

                        </div>

                    </div>


                    <div class="patch-info-item">

                        <div class="patch-info-icon">
                            <i class="fa fa-clock"></i>
                        </div>

                        <div>

                            <span class="patch-info-label">
                                Última actualización
                            </span>

                            <span class="patch-info-value">
                                <?php echo e(formatearFecha($fechaActualizacion)); ?>
                            </span>

                        </div>

                    </div>

                </div>

            </section>


            <section class="patch-detail-panel">

                <div class="patch-detail-panel-header">

                    <div>

                        <h2>
                            Vacante
                        </h2>

                        <p>
                            Información relacionada con la postulación.
                        </p>

                    </div>

                    <div class="patch-detail-panel-icon">
                        <i class="fa fa-briefcase"></i>
                    </div>

                </div>


                <?php if ($esPostulacionVacante): ?>

                    <div class="patch-vacancy-title-card">

                        <div class="patch-vacancy-title-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>

                        <div>

                            <h3>
                                <?php echo e($tituloVacante); ?>
                            </h3>

                            <p>

                                <?php echo e(
                                    valorSeguro(
                                        $postulacion['vacante_ubicacion'] ?? '',
                                        'Ubicación no especificada'
                                    )
                                ); ?>

                            </p>

                        </div>

                    </div>


                    <div class="patch-vacancy-details-grid">

                        <div class="patch-vacancy-detail">

                            <div class="patch-vacancy-detail-icon">
                                <i class="fa fa-location-dot"></i>
                            </div>

                            <div>

                                <span>
                                    Ubicación
                                </span>

                                <strong>
                                    <?php echo e($ubicacion); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="patch-vacancy-detail">

                            <div class="patch-vacancy-detail-icon">
                                <i class="fa fa-clock"></i>
                            </div>

                            <div>

                                <span>
                                    Tipo de jornada
                                </span>

                                <strong>
                                    <?php echo e($jornada); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="patch-vacancy-detail">

                            <div class="patch-vacancy-detail-icon">
                                <i class="fa fa-building"></i>
                            </div>

                            <div>

                                <span>
                                    Modalidad
                                </span>

                                <strong>
                                    <?php echo e($modalidad); ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="patch-empty-state">

                        <div class="patch-empty-state-icon">
                            <i class="fa fa-file-user"></i>
                        </div>

                        <h3>
                            CV general
                        </h3>

                        <p>
                            Esta postulación no está asociada a una vacante específica.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        </div>


        <?php if ($esPostulacionVacante): ?>

            <?php

            $hayContenidoVacante =
                $descripcionVacante !== '' ||
                $ofreceVacante !== '' ||
                $requisitosVacante !== '' ||
                $responsabilidadesVacante !== '' ||
                (
                    $soloImagen === 1 &&
                    $rutaImagenVacante !== ''
                );

            ?>

            <?php if ($hayContenidoVacante): ?>

                <section class="patch-detail-panel patch-detail-panel-full">

                    <div class="patch-detail-panel-header">

                        <div>

                            <h2>
                                Información de la vacante
                            </h2>

                            <p>
                                Contenido publicado originalmente en la vacante.
                            </p>

                        </div>

                        <div class="patch-detail-panel-icon">
                            <i class="fa fa-file-lines"></i>
                        </div>

                    </div>


                    <?php if (
                        $soloImagen === 1 &&
                        $rutaImagenVacante !== ''
                    ): ?>

                        <div class="patch-vacancy-image-wrapper">

                            <img
                                src="<?php echo e($rutaImagenVacante); ?>"
                                alt="<?php echo e($tituloVacante); ?>"
                                class="patch-vacancy-image"
                            >

                        </div>

                    <?php else: ?>

                        <?php if ($descripcionVacante !== ''): ?>

                            <div class="patch-content-section">

                                <h3>
                                    Descripción del puesto
                                </h3>

                                <div class="patch-rich-content">
                                    <?php echo $descripcionVacante; ?>
                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if ($ofreceVacante !== ''): ?>

                            <div class="patch-content-section">

                                <h3>
                                    Lo que se ofrece
                                </h3>

                                <div class="patch-rich-content">
                                    <?php echo $ofreceVacante; ?>
                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if ($requisitosVacante !== ''): ?>

                            <div class="patch-content-section">

                                <h3>
                                    Requisitos
                                </h3>

                                <div class="patch-rich-content">
                                    <?php echo $requisitosVacante; ?>
                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if ($responsabilidadesVacante !== ''): ?>

                            <div class="patch-content-section">

                                <h3>
                                    Responsabilidades
                                </h3>

                                <div class="patch-rich-content">
                                    <?php echo $responsabilidadesVacante; ?>
                                </div>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>


        <div class="patch-detail-bottom-grid">


            <section class="patch-detail-panel">

                <div class="patch-detail-panel-header">

                    <div>

                        <h2>
                            Comentarios del candidato
                        </h2>

                        <p>
                            Información adicional enviada durante la postulación.
                        </p>

                    </div>

                    <div class="patch-detail-panel-icon">
                        <i class="fa fa-comment"></i>
                    </div>

                </div>


                <div class="patch-comments-box">

                    <?php if ($comentarios !== ''): ?>

                        <?php echo nl2br(e($comentarios)); ?>

                    <?php else: ?>

                        <span class="patch-muted">
                            El candidato no agregó comentarios.
                        </span>

                    <?php endif; ?>

                </div>

            </section>


            <section class="patch-detail-panel">

                <div class="patch-detail-panel-header">

                    <div>

                        <h2>
                            Currículum vitae
                        </h2>

                        <p>
                            Documento enviado por el candidato.
                        </p>

                    </div>

                    <div class="patch-detail-panel-icon">
                        <i class="fa fa-file-pdf"></i>
                    </div>

                </div>


                <?php if ($tieneCV): ?>

                    <div class="patch-cv-card">

                        <div class="patch-cv-info">

                            <div class="patch-cv-icon">
                                <i class="fa fa-file-pdf"></i>
                            </div>

                            <div>

                                <div class="patch-cv-name">
                                    <?php echo e($nombreCV); ?>
                                </div>

                                <div class="patch-cv-label">
                                    Documento PDF
                                </div>

                            </div>

                        </div>


                        <a
                            href="<?php echo e($urlCV); ?>"
                            class="patch-cv-button"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="fa fa-eye"></i>
                            Ver CV
                        </a>

                    </div>

                <?php else: ?>

                    <div class="patch-empty-state patch-empty-state-small">

                        <div class="patch-empty-state-icon">
                            <i class="fa fa-file-circle-xmark"></i>
                        </div>

                        <h3>
                            Sin CV
                        </h3>

                        <p>
                            Esta postulación no contiene un archivo PDF.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        </div>


        <section class="patch-detail-panel patch-status-panel">

            <div class="patch-detail-panel-header">

                <div>

                    <h2>
                        Estado de la postulación
                    </h2>

                    <p>
                        Actualiza el estado actual del candidato.
                    </p>

                </div>

                <div class="patch-detail-panel-icon">
                    <i class="fa fa-list-check"></i>
                </div>

            </div>


            <form
                method="POST"
                id="formActualizarEstadoDetalle"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="actualizar_estado"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $idPostulacion; ?>"
                >

                <input
                    type="hidden"
                    name="id_bolsa"
                    value="<?php echo $vacanteId; ?>"
                >

                <input
                    type="hidden"
                    name="estado"
                    id="detalleEstado"
                    value="<?php echo e($estadoActual); ?>"
                >


                <div
                    class="patch-status-selector"
                    id="selectorEstadoDetalle"
                >

                    <button
                        type="button"
                        class="patch-status-option <?php echo $estadoActual === 'nueva' ? 'active' : ''; ?>"
                        data-estado="nueva"
                        onclick="seleccionarEstadoDetalle(this)"
                    >

                        <span class="patch-status-option-icon">
                            <i class="fa fa-clock"></i>
                        </span>

                        <span>
                            Por revisar
                        </span>

                    </button>


                    <button
                        type="button"
                        class="patch-status-option <?php echo $estadoActual === 'en_proceso' ? 'active' : ''; ?>"
                        data-estado="en_proceso"
                        onclick="seleccionarEstadoDetalle(this)"
                    >

                        <span class="patch-status-option-icon">
                            <i class="fa fa-spinner"></i>
                        </span>

                        <span>
                            En proceso
                        </span>

                    </button>


                    <button
                        type="button"
                        class="patch-status-option <?php echo $estadoActual === 'entrevista' ? 'active' : ''; ?>"
                        data-estado="entrevista"
                        onclick="seleccionarEstadoDetalle(this)"
                    >

                        <span class="patch-status-option-icon">
                            <i class="fa fa-comments"></i>
                        </span>

                        <span>
                            Entrevista
                        </span>

                    </button>


                    <button
                        type="button"
                        class="patch-status-option <?php echo $estadoActual === 'aceptado' ? 'active' : ''; ?>"
                        data-estado="aceptado"
                        onclick="seleccionarEstadoDetalle(this)"
                    >

                        <span class="patch-status-option-icon">
                            <i class="fa fa-circle-check"></i>
                        </span>

                        <span>
                            Aceptado
                        </span>

                    </button>


                    <button
                        type="button"
                        class="patch-status-option <?php echo $estadoActual === 'descartado' ? 'active' : ''; ?>"
                        data-estado="descartado"
                        onclick="seleccionarEstadoDetalle(this)"
                    >

                        <span class="patch-status-option-icon">
                            <i class="fa fa-circle-xmark"></i>
                        </span>

                        <span>
                            Descartado
                        </span>

                    </button>

                </div>


                <div class="patch-status-footer">

                    <div class="patch-current-status">

                        <span>
                            Estado seleccionado:
                        </span>

                        <strong id="estadoSeleccionadoTexto">
                            <?php echo e(estadoTexto($estadoActual)); ?>
                        </strong>

                    </div>


                    <button
                        type="submit"
                        class="patch-save-status-button"
                    >

                        <i class="fa fa-floppy-disk"></i>

                        Guardar estado

                    </button>

                </div>

            </form>

        </section>

    </div>

</div>


<div
    class="patch-toast <?php echo $mensajeOperacion !== '' ? 'show success' : ($errorOperacion !== '' ? 'show error' : ''); ?>"
    id="patchToast"
>

    <?php if ($mensajeOperacion !== ''): ?>

        <i class="fa fa-circle-check"></i>

        <span>
            <?php echo e($mensajeOperacion); ?>
        </span>

    <?php elseif ($errorOperacion !== ''): ?>

        <i class="fa fa-circle-exclamation"></i>

        <span>
            <?php echo e($errorOperacion); ?>
        </span>

    <?php endif; ?>

</div>


<script>

function seleccionarEstadoDetalle(elemento)
{
    if (!elemento) {
        return;
    }

    const selector =
        document.getElementById(
            'selectorEstadoDetalle'
        );

    const inputEstado =
        document.getElementById(
            'detalleEstado'
        );

    const textoEstado =
        document.getElementById(
            'estadoSeleccionadoTexto'
        );

    if (
        !selector ||
        !inputEstado ||
        !textoEstado
    ) {
        return;
    }

    const estado =
        elemento.getAttribute(
            'data-estado'
        );

    if (!estado) {
        return;
    }

    const textos = {

        nueva:
            'Por revisar',

        en_proceso:
            'En proceso',

        entrevista:
            'Entrevista',

        aceptado:
            'Aceptado',

        descartado:
            'Descartado'

    };

    const opciones =
        selector.querySelectorAll(
            '.patch-status-option'
        );

    opciones.forEach(
        function (opcion) {

            opcion.classList.remove(
                'active'
            );

        }
    );

    elemento.classList.add(
        'active'
    );

    inputEstado.value =
        estado;

    textoEstado.textContent =
        textos[estado] || estado;
}


document.addEventListener(
    'DOMContentLoaded',
    function () {

        const toast =
            document.getElementById(
                'patchToast'
            );

        if (
            !toast ||
            !toast.classList.contains('show')
        ) {
            return;
        }

        setTimeout(
            function () {

                toast.classList.remove(
                    'show'
                );

            },
            4000
        );

    }
);

</script>

</body>
</html>
