<?php

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . "/../../../../includes/config.php";
require_once __DIR__ . "/../../../../includes/correo.php";

header('Content-Type: application/json; charset=utf-8');


/* =========================================================
   RESPUESTA JSON
   ========================================================= */

function respuestaJSON($ok, $mensaje = '', $datos = [])
{
    echo json_encode([
        'ok'      => $ok,
        'mensaje' => $mensaje,
        'datos'   => $datos
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================================
   VALIDAR MÉTODO
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    respuestaJSON(
        false,
        'Método de solicitud no permitido.'
    );
}


/* =========================================================
   DATOS RECIBIDOS
   ========================================================= */

$token       = trim((string)($_POST['token'] ?? ''));
$nombre      = trim((string)($_POST['nombre_completo'] ?? ''));
$correo      = trim((string)($_POST['correo'] ?? ''));
$telefono    = trim((string)($_POST['telefono'] ?? ''));
$comentarios = trim((string)($_POST['comentarios'] ?? ''));


/* =========================================================
   VALIDAR TOKEN
   ========================================================= */

if ($token === '') {

    respuestaJSON(
        false,
        'No se recibió el identificador de la vacante.'
    );
}


/* =========================================================
   VALIDAR CAMPOS OBLIGATORIOS
   ========================================================= */

if ($nombre === '') {

    respuestaJSON(
        false,
        'El nombre completo es obligatorio.'
    );
}

if ($correo === '') {

    respuestaJSON(
        false,
        'El correo electrónico es obligatorio.'
    );
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

    respuestaJSON(
        false,
        'El correo electrónico no es válido.'
    );
}

if ($telefono === '') {

    respuestaJSON(
        false,
        'El teléfono es obligatorio.'
    );
}


/* =========================================================
   BUSCAR VACANTE
   ========================================================= */

$sqlVacante = "
    SELECT id, titulo
    FROM patch_BolsaTrabajo
    WHERE token = ?
      AND activo = 1
      AND archivada = 0
    LIMIT 1
";

$stmtVacante = $conn->prepare($sqlVacante);

if (!$stmtVacante) {

    respuestaJSON(
        false,
        'No fue posible preparar la consulta de la vacante.'
    );
}

$stmtVacante->bind_param(
    's',
    $token
);

$stmtVacante->execute();

$resultadoVacante = $stmtVacante->get_result();

if (
    !$resultadoVacante ||
    $resultadoVacante->num_rows === 0
) {

    $stmtVacante->close();

    respuestaJSON(
        false,
        'La vacante no existe, está inactiva o ya fue archivada.'
    );
}

$vacante = $resultadoVacante->fetch_assoc();

$idBolsa       = (int)$vacante['id'];
$tituloVacante = (string)$vacante['titulo'];

$stmtVacante->close();


/* =========================================================
   VALIDAR ARCHIVO CV
   ========================================================= */

if (!isset($_FILES['cv'])) {

    respuestaJSON(
        false,
        'Debes adjuntar tu CV en formato PDF.'
    );
}

$archivoCV = $_FILES['cv'];


/* =========================================================
   VALIDAR ERROR DE SUBIDA
   ========================================================= */

if ($archivoCV['error'] !== UPLOAD_ERR_OK) {

    switch ($archivoCV['error']) {

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:

            respuestaJSON(
                false,
                'El archivo PDF es demasiado grande.'
            );

        case UPLOAD_ERR_PARTIAL:

            respuestaJSON(
                false,
                'El archivo PDF se cargó de forma incompleta.'
            );

        case UPLOAD_ERR_NO_FILE:

            respuestaJSON(
                false,
                'Debes adjuntar tu CV en formato PDF.'
            );

        default:

            respuestaJSON(
                false,
                'Ocurrió un error al cargar el archivo PDF.'
            );
    }
}


/* =========================================================
   VALIDAR QUE SEA UN ARCHIVO SUBIDO
   ========================================================= */

if (!is_uploaded_file($archivoCV['tmp_name'])) {

    respuestaJSON(
        false,
        'El archivo enviado no es válido.'
    );
}


/* =========================================================
   VALIDAR EXTENSIÓN
   ========================================================= */

$nombreOriginal = $archivoCV['name'];

$extension = strtolower(
    pathinfo(
        $nombreOriginal,
        PATHINFO_EXTENSION
    )
);

if ($extension !== 'pdf') {

    respuestaJSON(
        false,
        'El CV debe estar en formato PDF.'
    );
}


/* =========================================================
   VALIDAR TIPO MIME REAL
   ========================================================= */

$finfo = new finfo(FILEINFO_MIME_TYPE);

$tipoMime = $finfo->file(
    $archivoCV['tmp_name']
);

if ($tipoMime !== 'application/pdf') {

    respuestaJSON(
        false,
        'El archivo enviado no es un PDF válido.'
    );
}


/* =========================================================
   VALIDAR TAMAÑO
   Máximo: 5 MB
   ========================================================= */

$maximoBytes = 5 * 1024 * 1024;

if ((int)$archivoCV['size'] > $maximoBytes) {

    respuestaJSON(
        false,
        'El CV no puede superar los 5 MB.'
    );
}


/* =========================================================
   CARPETA DE POSTULACIONES
   ========================================================= */

$directorioCV = __DIR__ . "/../uploads/postulaciones";


/* =========================================================
   CREAR CARPETA SI NO EXISTE
   ========================================================= */

if (!is_dir($directorioCV)) {

    if (!mkdir($directorioCV, 0755, true)) {

        respuestaJSON(
            false,
            'No fue posible crear la carpeta para guardar el CV.'
        );
    }
}


/* =========================================================
   VERIFICAR QUE LA CARPETA SEA ESCRIBIBLE
   ========================================================= */

if (!is_writable($directorioCV)) {

    respuestaJSON(
        false,
        'La carpeta destinada para guardar los CV no tiene permisos de escritura.'
    );
}


/* =========================================================
   GENERAR NOMBRE ÚNICO PARA EL CV
   ========================================================= */

$nombreArchivo =
    'cv_' .
    date('Ymd_His') .
    '_' .
    bin2hex(random_bytes(8)) .
    '.pdf';


/* =========================================================
   RUTA FÍSICA DEL ARCHIVO
   ========================================================= */

$rutaFisicaCV =
    $directorioCV .
    "/" .
    $nombreArchivo;


/* =========================================================
   RUTA QUE SE GUARDARÁ EN LA BD
   ========================================================= */

$rutaBD =
    'backend/modulo/Patch_BolsaTrabajo/uploads/postulaciones/' .
    $nombreArchivo;


/* =========================================================
   MOVER ARCHIVO
   ========================================================= */

if (
    !move_uploaded_file(
        $archivoCV['tmp_name'],
        $rutaFisicaCV
    )
) {

    respuestaJSON(
        false,
        'No fue posible guardar el archivo PDF.'
    );
}


/* =========================================================
   INSERTAR POSTULACIÓN
   ========================================================= */

$sqlPostulacion = "
    INSERT INTO patch_postulaciones (
        id_bolsa,
        nombre_completo,
        correo,
        telefono,
        cv,
        comentarios,
        estado
    )
    VALUES (?, ?, ?, ?, ?, ?, 'nueva')
";

$stmtPostulacion = $conn->prepare(
    $sqlPostulacion
);

if (!$stmtPostulacion) {

    // Eliminar archivo para evitar archivos huérfanos.

    if (file_exists($rutaFisicaCV)) {

        unlink($rutaFisicaCV);
    }

    respuestaJSON(
        false,
        'No fue posible preparar el registro de la postulación.'
    );
}


/* =========================================================
   VINCULAR DATOS
   ========================================================= */

$stmtPostulacion->bind_param(
    'isssss',
    $idBolsa,
    $nombre,
    $correo,
    $telefono,
    $rutaBD,
    $comentarios
);


/* =========================================================
   EJECUTAR INSERT
   ========================================================= */

if (!$stmtPostulacion->execute()) {

    // Eliminar archivo para evitar archivos huérfanos.

    if (file_exists($rutaFisicaCV)) {

        unlink($rutaFisicaCV);
    }

    $stmtPostulacion->close();

    respuestaJSON(
        false,
        'No fue posible guardar la postulación.'
    );
}


/* =========================================================
   ID DE LA POSTULACIÓN
   ========================================================= */

$idPostulacion =
    (int)$stmtPostulacion->insert_id;

$stmtPostulacion->close();


/* =========================================================
   PREPARAR CORREO
   ========================================================= */

$asuntoCorreo =
    'Nueva postulación - ' .
    $tituloVacante;


/* =========================================================
   FECHA DE POSTULACIÓN
   ========================================================= */

$fechaPostulacion =
    date('d/m/Y H:i:s');


/* =========================================================
   ESCAPAR DATOS PARA EL HTML
   ========================================================= */

$nombreHTML = htmlspecialchars(
    $nombre,
    ENT_QUOTES,
    'UTF-8'
);

$correoHTML = htmlspecialchars(
    $correo,
    ENT_QUOTES,
    'UTF-8'
);

$telefonoHTML = htmlspecialchars(
    $telefono,
    ENT_QUOTES,
    'UTF-8'
);

$tituloVacanteHTML = htmlspecialchars(
    $tituloVacante,
    ENT_QUOTES,
    'UTF-8'
);

$comentariosHTML = nl2br(
    htmlspecialchars(
        $comentarios,
        ENT_QUOTES,
        'UTF-8'
    )
);

$fechaHTML = htmlspecialchars(
    $fechaPostulacion,
    ENT_QUOTES,
    'UTF-8'
);


/* =========================================================
   CUERPO DEL CORREO
   ========================================================= */

$mensajeCorreo = '

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Nueva postulación</title>

</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f7f5;
    font-family:Arial,Helvetica,sans-serif;
    color:#333;
">

    <div style="
        max-width:700px;
        margin:30px auto;
        background:#ffffff;
        border-radius:10px;
        overflow:hidden;
        border:1px solid #e1e7e3;
    ">

        <div style="
            background:#198754;
            padding:24px 30px;
            color:#ffffff;
        ">

            <h1 style="
                margin:0;
                font-size:24px;
            ">
                Nueva postulación
            </h1>

            <p style="
                margin:8px 0 0;
                font-size:15px;
            ">
                Bolsa de Trabajo Kombitec
            </p>

        </div>


        <div style="
            padding:30px;
        ">

            <p style="
                margin-top:0;
                font-size:16px;
            ">
                Se ha recibido una nueva postulación para
                la siguiente vacante:
            </p>


            <div style="
                background:#f3f8f5;
                border-left:4px solid #198754;
                padding:15px 18px;
                margin:20px 0;
            ">

                <strong style="
                    font-size:18px;
                ">
                    ' . $tituloVacanteHTML . '
                </strong>

            </div>


            <h2 style="
                font-size:18px;
                color:#198754;
                margin-top:30px;
            ">
                Información del candidato
            </h2>


            <table
                cellpadding="8"
                cellspacing="0"
                width="100%"
                style="
                    border-collapse:collapse;
                    font-size:15px;
                "
            >

                <tr>

                    <td style="
                        width:150px;
                        font-weight:bold;
                        border-bottom:1px solid #eeeeee;
                    ">
                        Nombre:
                    </td>

                    <td style="
                        border-bottom:1px solid #eeeeee;
                    ">
                        ' . $nombreHTML . '
                    </td>

                </tr>


                <tr>

                    <td style="
                        font-weight:bold;
                        border-bottom:1px solid #eeeeee;
                    ">
                        Correo:
                    </td>

                    <td style="
                        border-bottom:1px solid #eeeeee;
                    ">
                        ' . $correoHTML . '
                    </td>

                </tr>


                <tr>

                    <td style="
                        font-weight:bold;
                        border-bottom:1px solid #eeeeee;
                    ">
                        Teléfono:
                    </td>

                    <td style="
                        border-bottom:1px solid #eeeeee;
                    ">
                        ' . $telefonoHTML . '
                    </td>

                </tr>


                <tr>

                    <td style="
                        font-weight:bold;
                        border-bottom:1px solid #eeeeee;
                    ">
                        Fecha:
                    </td>

                    <td style="
                        border-bottom:1px solid #eeeeee;
                    ">
                        ' . $fechaHTML . '
                    </td>

                </tr>


            </table>


            <h2 style="
                font-size:18px;
                color:#198754;
                margin-top:30px;
            ">
                Comentarios
            </h2>


            <div style="
                background:#f8f8f8;
                border:1px solid #e5e5e5;
                border-radius:6px;
                padding:15px;
                min-height:50px;
            ">

                ' .
                (
                    $comentariosHTML !== ''
                    ? $comentariosHTML
                    : '<span style="color:#888;">Sin comentarios.</span>'
                )
                . '

            </div>


            <div style="
                margin-top:30px;
                padding-top:20px;
                border-top:1px solid #eeeeee;
                color:#666;
                font-size:13px;
            ">

                <p style="margin:0;">
                    El CV del candidato se encuentra
                    adjunto a este correo en formato PDF.
                </p>

                <p style="margin:8px 0 0;">
                    ID de postulación:
                    <strong>#' . $idPostulacion . '</strong>
                </p>

            </div>

        </div>

    </div>

</body>

</html>
';


/* =========================================================
   DESTINATARIO DE PRUEBA
   =========================================================

   Durante las pruebas utilizaremos tu cuenta personal.

   Posteriormente solamente cambiaremos este correo
   por la cuenta oficial que defina Kombitec.
   ========================================================= */

$correoDestino =
    'christianchavez394@gmail.com';


/* =========================================================
   ENVIAR CORREO
   ========================================================= */

$correoEnviado = false;

try {

    $correoEnviado = enviarCorreo(
        $correoDestino,
        $asuntoCorreo,
        $mensajeCorreo,
        $rutaFisicaCV,
        $nombreArchivo
    );

} catch (Throwable $e) {

    $correoEnviado = false;
}


/* =========================================================
   RESPUESTA FINAL
   ========================================================= */

if ($correoEnviado) {

    respuestaJSON(
        true,
        'Tu postulación fue enviada correctamente.',
        [
            'id'             => $idPostulacion,
            'id_bolsa'       => $idBolsa,
            'titulo_vacante' => $tituloVacante,
            'cv'             => $rutaBD,
            'correo_enviado' => true
        ]
    );
}


/* =========================================================
   POSTULACIÓN GUARDADA PERO CORREO NO ENVIADO
   ========================================================= */

respuestaJSON(
    true,
    'Tu postulación fue registrada correctamente, pero no fue posible enviar la notificación por correo.',
    [
        'id'             => $idPostulacion,
        'id_bolsa'       => $idBolsa,
        'titulo_vacante' => $tituloVacante,
        'cv'             => $rutaBD,
        'correo_enviado' => false
    ]
);