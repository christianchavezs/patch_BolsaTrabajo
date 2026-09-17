<?php
error_reporting(E_ERROR | E_PARSE); // evita notices/warnings
require_once __DIR__ . "/../../../../includes/config.php"; // conexión centralizada
header('Content-Type: application/json; charset=utf-8');

function respuestaJSON($ok, $mensaje = '', $datos = null) {
    echo json_encode([
        'ok'      => $ok,
        'mensaje' => $mensaje,
        'datos'   => $datos
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Generar token único de 16 caracteres
function generarTokenBolsa($conn) {
    do {
        $token = bin2hex(random_bytes(8));
        $sql   = "SELECT id FROM patch_BolsaTrabajo WHERE token=?";
        $stmt  = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();
    } while ($existe);

    return $token;
}

// Acción: crear vacante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo            = trim($_POST['titulo'] ?? '');
    $loQueSeOfrece     = trim($_POST['lo_que_se_ofrece'] ?? '');
    $requisitos        = trim($_POST['requisitos'] ?? '');
    $responsabilidades = trim($_POST['responsabilidades'] ?? '');
    $fechaCierre       = trim($_POST['fecha_cierre'] ?? '');
    $activo            = (int)($_POST['activo'] ?? 1);

    // Validaciones
    if ($titulo === '')            respuestaJSON(false, 'El título de la vacante es obligatorio.');
    if ($loQueSeOfrece === '')     respuestaJSON(false, 'El campo "Lo que se ofrece" es obligatorio.');
    if ($requisitos === '')        respuestaJSON(false, 'El campo "Requisitos" es obligatorio.');
    if ($responsabilidades === '') respuestaJSON(false, 'El campo "Responsabilidades" es obligatorio.');

    $token = generarTokenBolsa($conn);

    $fechaCierreDB = null;
    if ($fechaCierre !== '') {
        $fechaCierreDB = $fechaCierre . ' 23:59:59';
    }

    // Insertar registro
    $sql = "INSERT INTO patch_BolsaTrabajo 
            (token, titulo, lo_que_se_ofrece, requisitos, responsabilidades, activo, archivada, fecha_cierre) 
            VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $token, $titulo, $loQueSeOfrece, $requisitos, $responsabilidades, $activo, $fechaCierreDB);

    if ($stmt->execute()) {
        // Recuperar registro recién creado
        $sqlGet = "SELECT * FROM patch_BolsaTrabajo WHERE token=?";
        $stmtGet = $conn->prepare($sqlGet);
        $stmtGet->bind_param("s", $token);
        $stmtGet->execute();
        $result = $stmtGet->get_result();
        $vacante = $result->fetch_assoc();
        $stmtGet->close();

        respuestaJSON(true, 'La vacante fue creada correctamente.', $vacante);
    } else {
        respuestaJSON(false, 'No fue posible guardar la vacante.');
    }
}

respuestaJSON(false, 'Acción no válida.');
