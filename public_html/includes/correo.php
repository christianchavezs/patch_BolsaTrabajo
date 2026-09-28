<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Envía un correo utilizando Gmail mediante SMTP.
 *
 * @param string      $destinatario
 * @param string      $asunto
 * @param string      $mensaje
 * @param string|null $archivoAdjunto
 * @param string|null $nombreAdjunto
 *
 * @return bool
 * @throws Exception
 */
function enviarCorreo(
    string $destinatario,
    string $asunto,
    string $mensaje,
    ?string $archivoAdjunto = null,
    ?string $nombreAdjunto = null
): bool {

    $mail = new PHPMailer(true);

    // =========================================================
    // CONFIGURACIÓN SMTP
    // =========================================================

    $correoSMTP = 'christianchavez394@gmail.com';
    $contrasenaAplicacion = 'uflrkymxmdcnddgc';

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $correoSMTP;
    $mail->Password   = $contrasenaAplicacion;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // =========================================================
    // CONFIGURACIÓN GENERAL
    // =========================================================

    $mail->CharSet = 'UTF-8';

    // =========================================================
    // REMITENTE
    // =========================================================

    $mail->setFrom(
        $correoSMTP,
        'Bolsa de Trabajo Kombitec'
    );

    // =========================================================
    // DESTINATARIO
    // =========================================================

    $mail->addAddress($destinatario);

    // =========================================================
    // ARCHIVO ADJUNTO
    // =========================================================

    if (
        $archivoAdjunto !== null &&
        $archivoAdjunto !== '' &&
        file_exists($archivoAdjunto)
    ) {

        $mail->addAttachment(
            $archivoAdjunto,
            $nombreAdjunto ?: basename($archivoAdjunto)
        );
    }

    // =========================================================
    // CONTENIDO
    // =========================================================

    $mail->isHTML(true);

    $mail->Subject = $asunto;

    $mail->Body = $mensaje;

    $mail->AltBody = strip_tags($mensaje);

    // =========================================================
    // ENVIAR
    // =========================================================

    return $mail->send();
}