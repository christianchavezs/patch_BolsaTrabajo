<?php
declare(strict_types=1);

/**
 * ============================================================
 * HELPER DE PLANTILLAS DE CORREO
 * Proyecto: Patch_BolsaTrabajo
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/includes/plantillas_helper.php
 * ============================================================
 */

/**
 * Asegura que la tabla de plantillas de correo y las columnas necesarias existan en la BD.
 */
function asegurarEstructuraPlantillas(mysqli $conn): void
{
    // 1. Tabla de plantillas de correo
    $sqlTabla = "
        CREATE TABLE IF NOT EXISTS patch_plantillas_correo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            asunto VARCHAR(255) NOT NULL,
            cuerpo TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    @$conn->query($sqlTabla);

    // 2. Insertar plantillas predeterminadas si no existen
    $plantillasDefault = [
        'positivo' => [
            'nombre' => 'Correo Positivo / Cita de Entrevista',
            'asunto' => 'Seguimiento a tu postulación en Kombitec - Entrevista laboral',
            'cuerpo' => "Estimado/a {nombre},

Agradecemos tu interés en formar parte de nuestro equipo para la vacante de {vacante} en {empresa}.

Conforme a lo acordado previamente por llamada telefónica, te confirmamos la fecha y hora de tu entrevista de trabajo:

📅 Cita de entrevista: {fecha_entrevista}

Por favor, responde a este correo confirmando de recibido y tu asistencia a la cita programada. En caso de requerir algún ajuste de horario o tener alguna duda previa, no dudes en responder a este mensaje.

Te deseamos el mayor de los éxitos en este proceso.

Atentamente,
Equipo de Recursos Humanos
{empresa}"
        ],
        'negativo' => [
            'nombre' => 'Correo Negativo / Agradecimiento y Descarte',
            'asunto' => 'Agradecimiento por tu interés en Kombitec - Vacante de {vacante}',
            'cuerpo' => "Estimado/a {nombre},

Agradecemos sinceramente el tiempo y entusiasmo que dedicaste al postularte para la posición de {vacante} en {empresa}.

Queremos informarte que en esta ocasión hemos decidido continuar el proceso de selección con perfiles que se alinean más estrechamente a los requerimientos técnicos y operativos inmediatos de la vacante.

Conservaremos tu currículum vitae en nuestra base de talento para futuras convocatorias que se adecúen a tu perfil y competencias.

Te deseamos el mejor de los éxitos en tus metas profesionales y personales.

Atentamente,
Equipo de Recursos Humanos
{empresa}"
        ]
    ];

    foreach ($plantillasDefault as $clave => $item) {
        $checkSql = "SELECT id FROM patch_plantillas_correo WHERE clave = ? LIMIT 1";
        if ($stmt = $conn->prepare($checkSql)) {
            $stmt->bind_param('s', $clave);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $insertSql = "INSERT INTO patch_plantillas_correo (clave, nombre, asunto, cuerpo) VALUES (?, ?, ?, ?)";
                if ($stmtInsert = $conn->prepare($insertSql)) {
                    $stmtInsert->bind_param('ssss', $clave, $item['nombre'], $item['asunto'], $item['cuerpo']);
                    @$stmtInsert->execute();
                    $stmtInsert->close();
                }
            }
            $stmt->close();
        }
    }

    // 3. Asegurar columnas de fecha de entrevista y seguimiento en patch_postulaciones
    $columnasDeseadas = [
        'fecha_entrevista'    => 'DATETIME NULL DEFAULT NULL AFTER estado',
        'fecha_ultimo_correo' => 'DATETIME NULL DEFAULT NULL AFTER fecha_entrevista',
        'tipo_ultimo_correo'  => 'VARCHAR(50) NULL DEFAULT NULL AFTER fecha_ultimo_correo'
    ];

    foreach ($columnasDeseadas as $columna => $definicion) {
        $res = @$conn->query("SHOW COLUMNS FROM patch_postulaciones LIKE '{$columna}'");
        if ($res && $res->num_rows === 0) {
            @$conn->query("ALTER TABLE patch_postulaciones ADD COLUMN {$columna} {$definicion}");
        }
    }
}

/**
 * Obtiene todas las plantillas registradas indexadas por clave.
 */
function obtenerPlantillasCorreo(mysqli $conn): array
{
    asegurarEstructuraPlantillas($conn);

    $sql = "SELECT id, clave, nombre, asunto, cuerpo, updated_at FROM patch_plantillas_correo ORDER BY id ASC";
    $result = $conn->query($sql);
    $plantillas = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plantillas[$row['clave']] = $row;
        }
        $result->free();
    }

    return $plantillas;
}

/**
 * Guarda o actualiza una plantilla de correo.
 */
function guardarPlantillaCorreo(mysqli $conn, string $clave, string $asunto, string $cuerpo): bool
{
    asegurarEstructuraPlantillas($conn);

    $sql = "UPDATE patch_plantillas_correo SET asunto = ?, cuerpo = ?, updated_at = NOW() WHERE clave = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('sss', $asunto, $cuerpo, $clave);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
