<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id                = (int)($_POST['id'] ?? 0);
    $titulo            = trim($_POST['titulo'] ?? '');
    $loQueSeOfrece     = trim($_POST['lo_que_se_ofrece'] ?? '');
    $requisitos        = trim($_POST['requisitos'] ?? '');
    $responsabilidades = trim($_POST['responsabilidades'] ?? '');
    $fechaCierre       = trim($_POST['fecha_cierre'] ?? '');
    $activo            = (int)($_POST['activo'] ?? 1);

    // Validaciones
    if ($id <= 0)              respuestaJSON(false, 'La vacante indicada no es válida.');
    if ($titulo === '')        respuestaJSON(false, 'El título de la vacante es obligatorio.');
    if ($loQueSeOfrece === '') respuestaJSON(false, 'El campo "Lo que se ofrece" es obligatorio.');
    if ($requisitos === '')    respuestaJSON(false, 'El campo "Requisitos" es obligatorio.');
    if ($responsabilidades === '') respuestaJSON(false, 'El campo "Responsabilidades" es obligatorio.');

    $fechaCierreDB = null;
    if ($fechaCierre !== '') {
        $fechaCierreDB = $fechaCierre . ' 23:59:59';
    }

    // Actualizar registro
    $sql = "UPDATE patch_BolsaTrabajo 
            SET titulo=?, lo_que_se_ofrece=?, requisitos=?, responsabilidades=?, activo=?, fecha_cierre=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisi", $titulo, $loQueSeOfrece, $requisitos, $responsabilidades, $activo, $fechaCierreDB, $id);

    if ($stmt->execute()) {
        // Recuperar registro actualizado
        $sqlGet = "SELECT * FROM patch_BolsaTrabajo WHERE id=?";
        $stmtGet = $conn->prepare($sqlGet);
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $result = $stmtGet->get_result();
        $vacante = $result->fetch_assoc();
        $stmtGet->close();

        respuestaJSON(true, 'La vacante fue actualizada correctamente.', $vacante);
    } else {
        respuestaJSON(false, 'No fue posible actualizar la vacante.');
    }
}

respuestaJSON(false, 'Acción no válida.');
