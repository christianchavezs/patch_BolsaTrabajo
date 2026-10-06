<?php

require_once __DIR__ . "/../../../includes/config.php";

header('Content-Type: text/html; charset=utf-8');

/* =========================================================
   FUNCIONES AUXILIARES
   ========================================================= */

function e($valor): string
{
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function estadoTexto(string $estado): string
{
    return match ($estado) {
        'nueva' => 'Por revisar',
        'en_proceso' => 'En proceso',
        'entrevista' => 'Entrevista',
        'aceptado' => 'Aceptado',
        'descartado' => 'Descartado',
        default => ucwords(
            str_replace('_', ' ', $estado)
        )
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
        $resultado .= mb_substr(
            $partes[0],
            0,
            1
        );
    }

    if (count($partes) > 1) {
        $ultima =
            $partes[count($partes) - 1];

        $resultado .= mb_substr(
            $ultima,
            0,
            1
        );
    }

    return mb_strtoupper($resultado);
}

function valorSeguro(
    $valor,
    string $default = 'No especificado'
): string {
    $valor = trim(
        (string)($valor ?? '')
    );

    return $valor !== ''
        ? $valor
        : $default;
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
   IDENTIFICADOR DE LA VACANTE
   =========================================================
 *
 * Esta página solamente funciona como dashboard
 * de una vacante.
 *
 * Soportamos:
 *
 * ?vacante=ID
 *
 * y:
 *
 * ?id_bolsa=ID
 *
 * para mantener compatibilidad.
 *
 * ========================================================= */

$idBolsa = 0;

/*
 * Primero usamos ?vacante=ID.
 */
if (isset($_GET['vacante'])) {

    $idBolsa =
        filter_var(
            $_GET['vacante'],
            FILTER_VALIDATE_INT
        );

    if ($idBolsa === false) {
        $idBolsa = 0;
    }
}

/*
 * Si no llegó ?vacante=ID,
 * aceptamos ?id_bolsa=ID.
 */
if ($idBolsa <= 0 && isset($_GET['id_bolsa'])) {

    $idBolsa =
        filter_var(
            $_GET['id_bolsa'],
            FILTER_VALIDATE_INT
        );

    if ($idBolsa === false) {
        $idBolsa = 0;
    }
}

/*
 * Si llega un POST desde algún formulario,
 * conservamos también id_bolsa.
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $idBolsa <= 0 &&
    isset($_POST['id_bolsa'])
) {

    $idBolsa =
        filter_var(
            $_POST['id_bolsa'],
            FILTER_VALIDATE_INT
        );

    if ($idBolsa === false) {
        $idBolsa = 0;
    }
}

/* =========================================================
   VARIABLES
   ========================================================= */

$mensajeOperacion = '';
$errorOperacion = '';

$vacante = null;

$postulacionesVacante = [];

/* =========================================================
   ACTUALIZAR ESTADO
   =========================================================
 *
 * Aunque actualmente el modal utiliza AJAX mediante
 * postulaciones-api.js, conservamos este POST como respaldo.
 *
 * ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'actualizar_estado'
) {

    $idPostulacion =
        isset($_POST['id'])
            ? (int)$_POST['id']
            : 0;

    $estado =
        trim(
            (string)(
                $_POST['estado'] ?? ''
            )
        );

    if ($idPostulacion <= 0) {

        $errorOperacion =
            'La postulación indicada no es válida.';

    } elseif (
        !in_array(
            $estado,
            $estadosPermitidos,
            true
        )
    ) {

        $errorOperacion =
            'El estado seleccionado no es válido.';

    } else {

        $stmt =
            $conn->prepare("
                UPDATE patch_postulaciones
                SET estado = ?
                WHERE id = ?
                LIMIT 1
            ");

        if ($stmt) {

            $stmt->bind_param(
                'si',
                $estado,
                $idPostulacion
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
}

/* =========================================================
   VALIDAR ID DE VACANTE
   ========================================================= */

if ($idBolsa <= 0) {

    http_response_code(400);

    $tituloError =
        'Vacante no válida';

    $descripcionError =
        'No se recibió un identificador válido de la vacante.';

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
            <?php echo e($tituloError); ?> | Bolsa de Trabajo
        </title>

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
                    Vacante no válida
                </h1>

                <p>
                    <?php echo e($descripcionError); ?>
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
   OBTENER VACANTE
   ========================================================= */

$stmtVacante =
    $conn->prepare("
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
        FROM patch_BolsaTrabajo
        WHERE id = ?
        LIMIT 1
    ");

if ($stmtVacante) {

    $stmtVacante->bind_param(
        'i',
        $idBolsa
    );

    if ($stmtVacante->execute()) {

        $resultadoVacante =
            $stmtVacante->get_result();

        if (
            $resultadoVacante &&
            $resultadoVacante->num_rows > 0
        ) {

            $vacante =
                $resultadoVacante->fetch_assoc();

            $resultadoVacante->free();
        }
    }

    $stmtVacante->close();
}

/* =========================================================
   VALIDAR QUE LA VACANTE EXISTA
   ========================================================= */

if (!$vacante) {

    http_response_code(404);

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
            Vacante no encontrada | Bolsa de Trabajo
        </title>

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

        </div>

    </div>

    </body>
    </html>

    <?php
    exit;
}

/* =========================================================
   OBTENER POSTULACIONES DE LA VACANTE
   ========================================================= */

$stmtPostulaciones =
    $conn->prepare("
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

    if ($stmtPostulaciones->execute()) {

        $resultadoPostulaciones =
            $stmtPostulaciones->get_result();

        if ($resultadoPostulaciones) {

            while (
                $fila =
                $resultadoPostulaciones->fetch_assoc()
            ) {

                $postulacionesVacante[] =
                    $fila;
            }

            $resultadoPostulaciones->free();
        }
    }

    $stmtPostulaciones->close();
}

/* =========================================================
   CONTADORES
   ========================================================= */

$totalPostulacionesVacante =
    count($postulacionesVacante);

$pendientesVacante = 0;
$enProcesoVacante = 0;
$entrevistasVacante = 0;
$aceptadosVacante = 0;
$descartadosVacante = 0;

foreach (
    $postulacionesVacante
    as $fila
) {

    $estadoFila =
        trim(
            (string)(
                $fila['estado'] ?? 'nueva'
            )
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

/* =========================================================
   DATOS DE LA VACANTE
   ========================================================= */

$tituloVacante =
    valorSeguro(
        $vacante['titulo'] ?? '',
        'Vacante sin título'
    );

$ubicacionVacante =
    valorSeguro(
        $vacante['ubicacion'] ?? '',
        'No especificada'
    );

$jornadaVacante =
    valorSeguro(
        $vacante['tipo_jornada'] ?? '',
        'No especificada'
    );

$modalidadVacante =
    valorSeguro(
        $vacante['modalidad'] ?? '',
        'No especificada'
    );

$descripcionVacante =
    trim(
        (string)(
            $vacante['descripcion'] ?? ''
        )
    );

$ofreceVacante =
    trim(
        (string)(
            $vacante['lo_que_se_ofrece'] ?? ''
        )
    );

$requisitosVacante =
    trim(
        (string)(
            $vacante['requisitos'] ?? ''
        )
    );

$responsabilidadesVacante =
    trim(
        (string)(
            $vacante['responsabilidades'] ?? ''
        )
    );

$soloImagen =
    (int)(
        $vacante['solo_imagen'] ?? 0
    );

$urlImagen =
    trim(
        (string)(
            $vacante['url_imagen'] ?? ''
        )
    );

/* =========================================================
   ESTADO DE LA VACANTE
   ========================================================= */

$esArchivada =
    (int)(
        $vacante['archivada'] ?? 0
    ) === 1;

$esActiva =
    (int)(
        $vacante['activo'] ?? 0
    ) === 1;

if ($esArchivada) {

    $estadoVacanteTexto =
        'Archivada';

    $estadoVacanteClase =
        'archived';

    $estadoVacanteIcono =
        'fa-box-archive';

} elseif ($esActiva) {

    $estadoVacanteTexto =
        'Activa';

    $estadoVacanteClase =
        'active';

    $estadoVacanteIcono =
        'fa-circle-check';

} else {

    $estadoVacanteTexto =
        'Inactiva';

    $estadoVacanteClase =
        'inactive';

    $estadoVacanteIcono =
        'fa-circle-pause';
}

/* =========================================================
   RUTA DE IMAGEN
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
            '../' .
            $imagenNormalizada;

    } elseif (
        str_starts_with(
            $imagenNormalizada,
            'uploads/'
        )
    ) {

        $rutaImagenVacante =
            $imagenNormalizada;

    } else {

        $rutaImagenVacante =
            'uploads/img_vacante/' .
            ltrim(
                $imagenNormalizada,
                '/'
            );
    }
}

/* =========================================================
   INFORMACIÓN DE LA PÁGINA
   ========================================================= */

$title =
    $tituloVacante .
    ' | Postulaciones';

$description =
    'Dashboard de postulaciones de la vacante ' .
    $tituloVacante .
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

    <!--
        Este CSS contiene también los estilos del modal
        compartido de postulaciones.
    -->
    <link
        rel="stylesheet"
        href="css/Admin_Postulaciones.css"
    >

    <style>

        .patch-vacancy-dashboard-back {
            margin-bottom: 18px;
        }

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
            grid-template-columns: repeat(3,minmax(0,1fr));
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
            grid-template-columns: repeat(5,minmax(0,1fr));
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
            border-color: #11713d;
            color: #ffffff;
        }

        .patch-vacancy-content-dashboard {
            color: #4b5560;
            font-size: 14px;
            line-height: 1.7;
        }

        .patch-vacancy-content-dashboard + .patch-vacancy-content-dashboard {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #edf0f2;
        }

        .patch-vacancy-content-dashboard h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #293139;
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

        @media (max-width: 1100px) {

            .patch-vacancy-dashboard-stats {
                grid-template-columns: repeat(3,minmax(0,1fr));
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
                grid-template-columns: repeat(2,minmax(0,1fr));
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

        <div class="patch-vacancy-dashboard-back">

            <a
                href="Admin_Postulaciones.php"
                class="patch-detail-btn"
            >
                <i class="fa fa-arrow-left"></i>
                Regresar a postulaciones
            </a>

        </div>

        <!-- =====================================================
             ENCABEZADO DE LA VACANTE
             ===================================================== -->

        <div class="patch-vacancy-dashboard-header">

            <div class="patch-vacancy-dashboard-top">

                <div class="patch-vacancy-dashboard-title">

                    <div class="patch-vacancy-dashboard-icon">
                        <i class="fa fa-briefcase"></i>
                    </div>

                    <div>

                        <h1>
                            <?php echo e($tituloVacante); ?>
                        </h1>

                        <p>
                            Dashboard de postulaciones de esta vacante.
                        </p>

                    </div>

                </div>

                <div class="patch-vacancy-dashboard-status">

                    <span
                        class="patch-vacancy-status <?php echo e($estadoVacanteClase); ?>"
                    >
                        <i
                            class="fa <?php echo e($estadoVacanteIcono); ?>"
                        ></i>

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
                            <?php echo e($ubicacionVacante); ?>
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
                            <?php echo e($jornadaVacante); ?>
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
                            <?php echo e($modalidadVacante); ?>
                        </strong>

                    </div>

                </div>

            </div>

        </div>

        <!-- =====================================================
             ESTADÍSTICAS
             ===================================================== -->

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

        <!-- =====================================================
             INFORMACIÓN DE LA VACANTE
             ===================================================== -->

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
                $soloImagen === 1 &&
                $rutaImagenVacante !== ''
            ): ?>

                <img
                    src="<?php echo e($rutaImagenVacante); ?>"
                    alt="<?php echo e($tituloVacante); ?>"
                    class="patch-vacancy-image-dashboard"
                >

            <?php else: ?>

                <?php if ($descripcionVacante !== ''): ?>

                    <div class="patch-vacancy-content-dashboard">

                        <h3>
                            Descripción del puesto
                        </h3>

                        <div>
                            <?php echo $descripcionVacante; ?>
                        </div>

                    </div>

                <?php endif; ?>

                <?php if ($ofreceVacante !== ''): ?>

                    <div class="patch-vacancy-content-dashboard">

                        <h3>
                            Lo que se ofrece
                        </h3>

                        <div>
                            <?php echo $ofreceVacante; ?>
                        </div>

                    </div>

                <?php endif; ?>

                <?php if ($requisitosVacante !== ''): ?>

                    <div class="patch-vacancy-content-dashboard">

                        <h3>
                            Requisitos
                        </h3>

                        <div>
                            <?php echo $requisitosVacante; ?>
                        </div>

                    </div>

                <?php endif; ?>

                <?php if ($responsabilidadesVacante !== ''): ?>

                    <div class="patch-vacancy-content-dashboard">

                        <h3>
                            Responsabilidades
                        </h3>

                        <div>
                            <?php echo $responsabilidadesVacante; ?>
                        </div>

                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </section>

        <!-- =====================================================
             POSTULACIONES DE LA VACANTE
             ===================================================== -->

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

                        <?php foreach (
                            $postulacionesVacante
                            as $fila
                        ): ?>

                            <?php

                            $idFila =
                                (int)(
                                    $fila['id'] ?? 0
                                );

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
                                    (string)(
                                        $fila['estado'] ?? 'nueva'
                                    )
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
                                    (string)(
                                        $fila['cv'] ?? ''
                                    )
                                );

                            $fechaFila =
                                $fila['fecha_postulacion']
                                ?? null;

                            ?>

                            <tr>

                                <td>

                                    <div
                                        class="patch-vacancy-dashboard-candidate"
                                    >

                                        <div
                                            class="patch-vacancy-dashboard-avatar"
                                        >
                                            <?php
                                            echo e(
                                                iniciales(
                                                    $nombreFila
                                                )
                                            );
                                            ?>
                                        </div>

                                        <div>

                                            <strong>
                                                <?php
                                                echo e(
                                                    $nombreFila
                                                );
                                                ?>
                                            </strong>

                                            <span>
                                                <?php
                                                echo e(
                                                    $correoFila
                                                );
                                                ?>
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        $telefonoFila
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        formatearFecha(
                                            $fechaFila
                                        )
                                    );
                                    ?>
                                </td>

                                <td>

                                    <span
                                        class="patch-dashboard-status <?php echo e(estadoClase($estadoFila)); ?>"
                                    >

                                        <i class="fa fa-circle"></i>

                                        <?php
                                        echo e(
                                            estadoTexto(
                                                $estadoFila
                                            )
                                        );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <div
                                        class="patch-dashboard-actions"
                                    >

                                        <?php if ($idFila > 0): ?>

                                            <!--
                                                IMPORTANTE:
                                                Ya no navegamos a:
                                                Admin_Detalle_Postulacion.php?id=ID

                                                Ahora utilizamos el mismo modal
                                                compartido por todo el módulo.
                                            -->

                                            <button
                                                type="button"
                                                class="patch-dashboard-action primary"
                                                onclick="cargarPostulacion(<?php echo $idFila; ?>)"
                                                title="Ver detalle de la postulación"
                                            >
                                                <i class="fa fa-eye"></i>
                                                Ver detalle
                                            </button>

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

    </div>

</div>

<!-- =========================================================
     MODAL ÚNICO DE DETALLE DE POSTULACIÓN
     ========================================================= -->

<?php
require_once __DIR__ .
    "/partials/modal_ver_postulacion.php";
?>

<!-- =========================================================
     FORMULARIO OCULTO DE RESPALDO
     ========================================================= -->

<form
    method="POST"
    id="formActualizarEstado"
    style="display:none;"
>

    <input
        type="hidden"
        name="accion"
        value="actualizar_estado"
    >

    <input
        type="hidden"
        name="id"
        id="estadoPostulacionId"
        value=""
    >

    <input
        type="hidden"
        name="estado"
        id="estadoPostulacionValor"
        value=""
    >

    <input
        type="hidden"
        name="id_bolsa"
        value="<?php echo $idBolsa; ?>"
    >

</form>

<!-- =========================================================
     TOAST
     ========================================================= -->

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

<!-- =========================================================
     JAVASCRIPT
     ========================================================= -->

<script src="js/postulaciones-api.js"></script>
<script src="js/postulaciones-modal.js"></script>

<?php if (
    $mensajeOperacion !== '' ||
    $errorOperacion !== ''
): ?>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const toast =
            document.getElementById(
                'patchToast'
            );

        if (
            !toast ||
            !toast.classList.contains(
                'show'
            )
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

<?php endif; ?>

</body>
</html>