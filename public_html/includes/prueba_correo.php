<?php

require_once __DIR__ . "/correo.php";

try {

    $destinatario = 'christianchavez394@gmail.com';

    $asunto = 'Prueba SMTP - Bolsa de Trabajo Kombitec';

    $mensaje = '
        <h2>Prueba de correo</h2>

        <p>
            Este correo confirma que la integración de
            PHPMailer con Gmail SMTP funciona correctamente.
        </p>

        <p>
            <strong>Proyecto:</strong> Bolsa de Trabajo Kombitec
        </p>

        <p>
            <strong>Estado:</strong> Prueba exitosa
        </p>
    ';

    enviarCorreo(
        $destinatario,
        $asunto,
        $mensaje
    );

    echo "Correo enviado correctamente.";

} catch (Throwable $e) {

    echo "Error al enviar el correo:<br><br>";
    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );
}