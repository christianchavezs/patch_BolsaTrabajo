<?php
declare(strict_types=1);

require_once __DIR__ . "/../../../../includes/auth.php";
requerirAdminApi();

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . "/../../../../includes/config.php";
require_once __DIR__ . "/../../../../includes/correo.php";
require_once __DIR__ . "/../includes/plantillas_helper.php";

header('Content-Type: application/json; charset=utf-8');

function respuestaJSON(bool $ok, string $mensaje = '', $datos = []): void
{
    echo json_encode([
        'ok'      => $ok,
        'mensaje' => $mensaje,
        'datos'   => $datos
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(false, 'Método HTTP no permitido.');
}

$idPostulacion   = filter_input(INPUT_POST, 'id_postulacion', FILTER_VALIDATE_INT);
$tipoCorreo      = trim((string)($_POST['tipo_correo'] ?? ''));
$asunto          = trim((string)($_POST['asunto'] ?? ''));
$cuerpo          = trim((string)($_POST['cuerpo'] ?? ''));
$fechaEntrevista = trim((string)($_POST['fecha_entrevista'] ?? ''));
$actualizarEstado = isset($_POST['actualizar_estado']) && $_POST['actualizar_estado'] === '1';

if ($idPostulacion === false || $idPostulacion === null || $idPostulacion <= 0) {
    respuestaJSON(false, 'El identificador de la postulación no es válido.');
}

if ($tipoCorreo !== 'positivo' && $tipoCorreo !== 'negativo') {
    respuestaJSON(false, 'El tipo de correo no es válido (debe ser positivo o negativo).');
}

if ($asunto === '') {
    respuestaJSON(false, 'El asunto del correo no puede estar vacío.');
}

if ($cuerpo === '') {
    respuestaJSON(false, 'El cuerpo del correo no puede estar vacío.');
}

// Asegurar estructura
asegurarEstructuraPlantillas($conn);

// Consultar la postulación
$sql = "
    SELECT
        p.id,
        p.id_bolsa,
        p.nombre_completo,
        p.correo,
        p.telefono,
        p.estado,
        p.comentario_seguimiento,
        b.titulo AS vacante_titulo
    FROM patch_postulaciones p
    LEFT JOIN patch_BolsaTrabajo b ON b.id = p.id_bolsa
    WHERE p.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respuestaJSON(false, 'Error al preparar la consulta del postulante.');
}

$stmt->bind_param('i', $idPostulacion);
$stmt->execute();
$res = $stmt->get_result();
$postulacion = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$postulacion) {
    respuestaJSON(false, 'No se encontró la postulación especificada.');
}

$correoDestino = trim((string)($postulacion['correo'] ?? ''));
if ($correoDestino === '' || !filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
    respuestaJSON(false, 'El postulante no cuenta con un correo electrónico válido registrado.');
}

$nombreCandidato = trim((string)($postulacion['nombre_completo'] ?? 'Candidato'));
$tituloVacante   = trim((string)($postulacion['vacante_titulo'] ?? 'Bolsa de Trabajo / CV General'));

// Formatear cuerpo a HTML conservando saltos de línea y resaltando datos clave en negrita
$cuerpoFormateado = nl2br(htmlspecialchars($cuerpo, ENT_QUOTES, 'UTF-8'));

// 1. Poner en negrita el nombre del aspirante
if ($nombreCandidato !== '') {
    $cuerpoFormateado = preg_replace(
        '/(?<!<strong>)(' . preg_quote(htmlspecialchars($nombreCandidato, ENT_QUOTES, 'UTF-8'), '/') . ')(?!<\/strong>)/iu',
        '<strong style="color: #0f172a;">$1</strong>',
        $cuerpoFormateado
    );
}

// 2. Poner en negrita la vacante / posición
if ($tituloVacante !== '') {
    $cuerpoFormateado = preg_replace(
        '/(?<!<strong>)(' . preg_quote(htmlspecialchars($tituloVacante, ENT_QUOTES, 'UTF-8'), '/') . ')(?!<\/strong>)/iu',
        '<strong style="color: #0f172a;">$1</strong>',
        $cuerpoFormateado
    );
}

// 3. Poner en negrita el nombre de la empresa Kombitec
$cuerpoFormateado = preg_replace(
    '/(?<!<strong>)(Kombitec(?:\s+S\.A\.\s+de\s+C\.V\.)?)(?!<\/strong>)/iu',
    '<strong style="color: #0b3b27;">$1</strong>',
    $cuerpoFormateado
);

// 4. Transformar la cita de entrevista en una tarjeta visual elegante destacando fecha y horario en negrita
$cuerpoFormateado = preg_replace_callback(
    '/(?:📅\s*)?Cita de entrevista:\s*([^\n<]+)/iu',
    function ($matches) {
        $detalleTexto = trim($matches[1]);
        $htmlCita = '';

        if (preg_match('/^(.*?)\s+a\s+las\s+(.*?)$/iu', $detalleTexto, $partes)) {
            $fechaPart = trim($partes[1]);
            $horaPart = trim($partes[2]);
            $htmlCita = '<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin-top: 6px; font-size: 14px;">' .
                        '<tr><td style="padding: 3px 0; color: #17211c;"><strong>📅 Fecha de la cita:</strong> <span style="color: #0f633d; font-weight: 700;">' . $fechaPart . '</span></td></tr>' .
                        '<tr><td style="padding: 3px 0; color: #17211c;"><strong>⏰ Horario:</strong> <span style="color: #0f633d; font-weight: 700;">' . $horaPart . '</span></td></tr>' .
                        '</table>';
        } else {
            $htmlCita = '<div style="margin-top: 4px; font-size: 15px; color: #0f633d; font-weight: 700;">' . $detalleTexto . '</div>';
        }

        return '<div style="background: #f4fcf7; border: 1px solid #bbf7d0; border-left: 5px solid #159a5b; padding: 16px 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(21, 154, 91, 0.08);">' .
               '<strong style="display: block; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #12834d; margin-bottom: 4px;">' .
               'Cita de Entrevista Agendada</strong>' .
               $htmlCita .
               '</div>';
    },
    $cuerpoFormateado
);

$etiquetaTipo = ($tipoCorreo === 'positivo') ? 'Proceso de Selección &middot; Cita de Entrevista' : 'Notificación de Postulación';

$htmlMensaje = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') . '</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4fcf7; font-family: \'Segoe UI\', Roboto, Arial, Helvetica, sans-serif; color: #1e293b; line-height: 1.65;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4fcf7; padding: 35px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 99, 61, 0.12); border: 1px solid #dff7e9;">
                    
                    <!-- Banner Verde Corporativo Kombitec -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0b3b27 0%, #12834d 52%, #22b86a 100%); padding: 28px 32px; text-align: left; border-bottom: 4px solid #62d995;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 1px; line-height: 1.2;">
                                            KOMBITEC
                                        </div>
                                        <div style="margin-top: 4px; color: #dff7e9; font-size: 13.5px; font-weight: 500;">
                                            Bolsa de Trabajo &middot; ' . $etiquetaTipo . '
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cuerpo del Mensaje -->
                    <tr>
                        <td style="padding: 34px 32px; font-size: 15.5px; color: #334155; line-height: 1.7;">
                            ' . $cuerpoFormateado . '
                        </td>
                    </tr>

                    <!-- Footer institucional en tonos verdes suaves -->
                    <tr>
                        <td style="background-color: #f4fcf7; padding: 22px 32px; border-top: 1px solid #dff7e9; font-size: 12.5px; color: #475569; text-align: center;">
                            <p style="margin: 0 0 6px 0;">
                                Este mensaje fue enviado por el equipo de Recursos Humanos de <strong style="color: #0b3b27;">Kombitec</strong> respecto a tu postulación para <strong style="color: #0f633d;">' . htmlspecialchars($tituloVacante, ENT_QUOTES, 'UTF-8') . '</strong>.
                            </p>
                            <p style="margin: 0; color: #718178; font-size: 11.5px;">
                                &copy; ' . date('Y') . ' Kombitec S.A. de C.V. &middot; Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
';

// Intentar enviar el correo
try {
    $enviado = enviarCorreo(
        $correoDestino,
        $asunto,
        $htmlMensaje
    );

    if (!$enviado) {
        respuestaJSON(false, 'No se pudo enviar el correo al destinatario.');
    }
} catch (\Throwable $th) {
    error_log('Error en enviar_correo_postulacion: ' . $th->getMessage());
    respuestaJSON(false, 'Error al enviar el correo: ' . $th->getMessage());
}

// Registrar en la base de datos
$nuevoEstado = $postulacion['estado'];
$notaRegistro = '[' . date('d/m/Y H:i') . '] Correo (' . ($tipoCorreo === 'positivo' ? 'Positivo / Entrevista' : 'Negativo / Descarte') . ') enviado a ' . $correoDestino;

if ($tipoCorreo === 'positivo' && $fechaEntrevista !== '') {
    $timestamp = strtotime($fechaEntrevista);
    $fechaSql = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    $fechaTexto = $timestamp ? date('d/m/Y H:i', $timestamp) : $fechaEntrevista;
    $notaRegistro .= ' | Cita agendada: ' . $fechaTexto;

    if ($actualizarEstado || $postulacion['estado'] === 'nueva' || $postulacion['estado'] === 'en_proceso') {
        $nuevoEstado = 'entrevista';
    }

    $comentariosSeg = trim((string)($postulacion['comentario_seguimiento'] ?? ''));
    $comentariosSegFinal = ($comentariosSeg !== '' ? $comentariosSeg . "\n" : '') . $notaRegistro;

    $sqlUpdate = "
        UPDATE patch_postulaciones
        SET
            estado = ?,
            fecha_entrevista = ?,
            fecha_ultimo_correo = NOW(),
            tipo_ultimo_correo = ?,
            comentario_seguimiento = ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('ssssi', $nuevoEstado, $fechaSql, $tipoCorreo, $comentariosSegFinal, $idPostulacion);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
} else {
    if ($tipoCorreo === 'negativo' && $actualizarEstado) {
        $nuevoEstado = 'descartado';
    }

    $comentariosSeg = trim((string)($postulacion['comentario_seguimiento'] ?? ''));
    $comentariosSegFinal = ($comentariosSeg !== '' ? $comentariosSeg . "\n" : '') . $notaRegistro;

    $sqlUpdate = "
        UPDATE patch_postulaciones
        SET
            estado = ?,
            fecha_ultimo_correo = NOW(),
            tipo_ultimo_correo = ?,
            comentario_seguimiento = ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('sssi', $nuevoEstado, $tipoCorreo, $comentariosSegFinal, $idPostulacion);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
}

respuestaJSON(true, 'Correo enviado exitosamente a ' . $correoDestino . '.', [
    'id'                   => $idPostulacion,
    'estado'               => $nuevoEstado,
    'tipo_correo'          => $tipoCorreo,
    'fecha_ultimo_correo'  => date('Y-m-d H:i:s'),
    'comentario_seguimiento' => $comentariosSegFinal
]);
