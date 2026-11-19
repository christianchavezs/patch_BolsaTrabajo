<?php
declare(strict_types=1);

require_once __DIR__ . "/../../../../includes/auth.php";
requerirAdminApi();

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . "/../../../../includes/config.php";
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

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($metodo === 'GET') {
    try {
        $plantillas = obtenerPlantillasCorreo($conn);
        respuestaJSON(true, 'Plantillas obtenidas correctamente.', $plantillas);
    } catch (\Throwable $th) {
        error_log('Error al obtener plantillas: ' . $th->getMessage());
        respuestaJSON(false, 'No fue posible obtener las plantillas de correo.');
    }
}

if ($metodo === 'POST') {
    $clave  = trim((string)($_POST['clave'] ?? ''));
    $asunto = trim((string)($_POST['asunto'] ?? ''));
    $cuerpo = trim((string)($_POST['cuerpo'] ?? ''));

    if ($clave !== 'positivo' && $clave !== 'negativo') {
        respuestaJSON(false, 'La clave de plantilla especificada no es válida.');
    }

    if ($asunto === '') {
        respuestaJSON(false, 'El asunto del correo no puede estar vacío.');
    }

    if ($cuerpo === '') {
        respuestaJSON(false, 'El cuerpo del correo no puede estar vacío.');
    }

    try {
        $exito = guardarPlantillaCorreo($conn, $clave, $asunto, $cuerpo);
        if ($exito) {
            respuestaJSON(true, 'Plantilla guardada exitosamente.', [
                'clave'  => $clave,
                'asunto' => $asunto,
                'cuerpo' => $cuerpo
            ]);
        } else {
            respuestaJSON(false, 'No se pudo actualizar la plantilla en la base de datos.');
        }
    } catch (\Throwable $th) {
        error_log('Error al guardar plantilla: ' . $th->getMessage());
        respuestaJSON(false, 'Ocurrió un error al guardar la plantilla.');
    }
}

respuestaJSON(false, 'Método HTTP no soportado.');
