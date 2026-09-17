<?php
require_once __DIR__ . "/../../../../includes/config.php"; // siempre usa config.php
header('Content-Type: application/json; charset=utf-8');

function respuestaJSON($ok, $mensaje = '', $datos = null) {
    echo json_encode(['ok'=>$ok,'mensaje'=>$mensaje,'datos'=>$datos], JSON_UNESCAPED_UNICODE);
    exit;
}

$accion = $_GET['accion'] ?? '';

if ($accion === 'listar') {
    $sql = "SELECT * FROM patch_BolsaTrabajo WHERE archivada=0 ORDER BY fecha_publicacion DESC";
    $result = $conn->query($sql);

    if ($result) {
        $datos = $result->fetch_all(MYSQLI_ASSOC);
        respuestaJSON(true, '', $datos);
    } else {
        respuestaJSON(false, 'Error en la consulta: ' . $conn->error);
    }
}

if ($accion === 'listarArchivadas') {
    $sql = "SELECT * FROM patch_BolsaTrabajo WHERE archivada=1 ORDER BY fecha_publicacion DESC";
    $result = $conn->query($sql);

    if ($result) {
        $datos = $result->fetch_all(MYSQLI_ASSOC);
        respuestaJSON(true, '', $datos);
    } else {
        respuestaJSON(false, 'Error en la consulta: ' . $conn->error);
    }
}

respuestaJSON(false, 'Acción no válida.');
