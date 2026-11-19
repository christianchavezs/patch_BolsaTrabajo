<?php
declare(strict_types=1);

require_once __DIR__ . "/../../../includes/auth.php";
requerirAdminPagina('../../../login.php');

require_once __DIR__ . "/../../../includes/config.php";
require_once __DIR__ . "/includes/plantillas_helper.php";

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$plantillas = obtenerPlantillasCorreo($conn);

$plantillaPositiva = $plantillas['positivo'] ?? [
    'asunto' => 'Seguimiento a tu postulación en Kombitec - Entrevista laboral',
    'cuerpo' => ''
];

$plantillaNegativa = $plantillas['negativo'] ?? [
    'asunto' => 'Agradecimiento por tu interés en Kombitec - Vacante de {vacante}',
    'cuerpo' => ''
];

function e($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

$title = "Plantillas de Correo | Bolsa de Trabajo";
$description = "Administración de plantillas de correo para seguimiento de candidatos en Kombitec";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <meta name="description" content="<?php echo e($description); ?>">

    <!-- Fuentes -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS del módulo -->
    <link rel="stylesheet" href="css/Admin_Plantillas.css">
</head>
<body>
    <div class="patch-plantillas-wrapper">

        <!-- MENÚ DE USUARIO -->
        <details class="patch-user-menu" id="patchUserMenu">
            <summary aria-label="Abrir menú de usuario" aria-haspopup="true">
                <span class="patch-user-avatar">
                    <i class="fa fa-user" aria-hidden="true"></i>
                </span>
                <span class="patch-user-label"><?php echo e(adminNombre()); ?></span>
                <i class="fa fa-chevron-down patch-user-arrow" aria-hidden="true"></i>
            </summary>
            <div class="patch-user-dropdown">
                <div class="patch-user-info">
                    <strong><?php echo e(adminNombre()); ?></strong>
                    <span><?php echo e(adminCorreo() !== '' ? adminCorreo() : 'Sesión activa'); ?></span>
                </div>
                <div class="patch-user-divider"></div>
                <a href="../../../cerrar_sesion.php">
                    <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </details>

        <div class="patch-plantillas-container">

            <!-- ACCIONES DE RETORNO -->
            <div class="patch-plantillas-actions">
                <a href="Admin_Postulaciones.php" class="patch-plantillas-btn">
                    <i class="fa fa-arrow-left"></i>
                    Regresar a Postulaciones
                </a>
                <a href="Admin_Reportes.php" class="patch-plantillas-btn">
                    <i class="fa fa-chart-pie"></i>
                    Reportes y Estadísticas
                </a>
                <a href="Admin_BolsaTrabajo.php" class="patch-plantillas-btn">
                    <i class="fa fa-briefcase"></i>
                    Ir a Vacantes
                </a>
            </div>

            <!-- ENCABEZADO -->
            <div class="patch-plantillas-header">
                <div class="patch-plantillas-header-content">
                    <div>
                        <div class="patch-breadcrumb">
                            <a href="Admin_BolsaTrabajo.php">Bolsa de Trabajo</a>
                            <i class="fa fa-angle-right"></i>
                            <a href="Admin_Postulaciones.php">Postulaciones</a>
                            <i class="fa fa-angle-right"></i>
                            <span>Plantillas de Correo</span>
                        </div>
                        <h1>Plantillas de Correo para Postulantes</h1>
                        <p>
                            Personaliza las respuestas oficiales de Kombitec para los postulantes. Estas plantillas se cargarán automáticamente al momento de agendar entrevistas o enviar notificaciones desde el panel de postulaciones.
                        </p>
                    </div>
                    <div class="patch-plantillas-header-icon">
                        <i class="fa fa-envelope-open-text"></i>
                    </div>
                </div>
            </div>

            <!-- NAVEGACIÓN POR PESTAÑAS -->
            <div class="patch-tabs-nav">
                <button type="button" class="patch-tab-btn active" data-tab="panel-positivo">
                    <i class="fa fa-circle-check" style="color: #16a34a;"></i>
                    Plantilla Positiva
                    <span class="badge-pill badge-positive">Cita / Entrevista</span>
                </button>
                <button type="button" class="patch-tab-btn" data-tab="panel-negativo">
                    <i class="fa fa-circle-xmark" style="color: #dc2626;"></i>
                    Plantilla Negativa
                    <span class="badge-pill badge-negative">Descarte / Agradecimiento</span>
                </button>
            </div>

            <!-- PANEL: PLANTILLA POSITIVA -->
            <div class="patch-plantilla-panel active" id="panel-positivo">
                <div class="patch-plantilla-card">
                    <div class="patch-card-title">
                        <i class="fa fa-envelope-circle-check" style="color: #16a34a;"></i>
                        <span>Plantilla Positiva (Invitación y Confirmación de Cita)</span>
                    </div>
                    <p class="patch-card-desc">
                        Este correo se enviará a los aspirantes seleccionados para coordinar o formalizar su entrevista laboral (acordada previamente por llamada telefónica).
                    </p>

                    <!-- CAJA DE COMODINES DISPONIBLES -->
                    <div class="patch-variables-box">
                        <div class="patch-variables-title">
                            <i class="fa fa-tags"></i>
                            <span>Etiquetas dinámicas disponibles (haz clic en cualquiera para insertarla en el mensaje):</span>
                        </div>
                        <div class="patch-variables-chips">
                            <span class="patch-var-chip" data-clave="positivo" data-tag="{nombre}">
                                {nombre} <span class="patch-var-desc">(Nombre del candidato)</span>
                            </span>
                            <span class="patch-var-chip" data-clave="positivo" data-tag="{vacante}">
                                {vacante} <span class="patch-var-desc">(Puesto o CV General)</span>
                            </span>
                            <span class="patch-var-chip" data-clave="positivo" data-tag="{fecha_entrevista}">
                                {fecha_entrevista} <span class="patch-var-desc">(Fecha y hora de la cita)</span>
                            </span>
                            <span class="patch-var-chip" data-clave="positivo" data-tag="{empresa}">
                                {empresa} <span class="patch-var-desc">(Kombitec)</span>
                            </span>
                        </div>
                    </div>

                    <form class="form-guardar-plantilla" data-clave="positivo">
                        <div class="patch-form-group">
                            <label class="patch-form-label" for="asunto_positivo">Asunto del correo:</label>
                            <div class="patch-form-help">Puedes incluir etiquetas como {vacante} o {empresa}.</div>
                            <input
                                type="text"
                                id="asunto_positivo"
                                name="asunto"
                                class="patch-input"
                                value="<?php echo e($plantillaPositiva['asunto']); ?>"
                                required
                            >
                        </div>

                        <div class="patch-form-group">
                            <label class="patch-form-label" for="cuerpo_positivo">Cuerpo del correo:</label>
                            <div class="patch-form-help">Redacta el mensaje institucional. Conservará los saltos de línea al enviarse.</div>
                            <textarea
                                id="cuerpo_positivo"
                                name="cuerpo"
                                class="patch-textarea"
                                rows="12"
                                required
                            ><?php echo e($plantillaPositiva['cuerpo']); ?></textarea>
                        </div>

                        <!-- VISTA PREVIA SIMULADA TIPO EMAIL -->
                        <div class="patch-preview-wrapper">
                            <div class="patch-preview-title">
                                <i class="fa fa-eye"></i> Vista previa del correo oficial (con datos de muestra)
                            </div>
                            <div class="patch-preview-card-mockup">
                                <div class="patch-preview-header-mockup">
                                    <strong>KOMBITEC</strong>
                                    <span>Bolsa de Trabajo &middot; Proceso de Selección &middot; Cita de Entrevista</span>
                                </div>
                                <div class="patch-preview-content" id="preview_positivo"></div>
                            </div>
                        </div>

                        <div class="patch-form-actions">
                            <button type="submit" class="patch-submit-btn">
                                <i class="fa fa-floppy-disk"></i>
                                Guardar plantilla positiva
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PANEL: PLANTILLA NEGATIVA -->
            <div class="patch-plantilla-panel" id="panel-negativo">
                <div class="patch-plantilla-card">
                    <div class="patch-card-title">
                        <i class="fa fa-envelope" style="color: #dc2626;"></i>
                        <span>Plantilla Negativa (Agradecimiento y Cierre de Postulación)</span>
                    </div>
                    <p class="patch-card-desc">
                        Este correo se enviará para notificar cordialmente a aquellos aspirantes que no continuarán en el proceso actual de contratación, agradeciéndoles su tiempo y conservando su CV en la cartera de talento.
                    </p>

                    <!-- CAJA DE COMODINES DISPONIBLES -->
                    <div class="patch-variables-box">
                        <div class="patch-variables-title">
                            <i class="fa fa-tags"></i>
                            <span>Etiquetas dinámicas disponibles (haz clic en cualquiera para insertarla en el mensaje):</span>
                        </div>
                        <div class="patch-variables-chips">
                            <span class="patch-var-chip" data-clave="negativo" data-tag="{nombre}">
                                {nombre} <span class="patch-var-desc">(Nombre del candidato)</span>
                            </span>
                            <span class="patch-var-chip" data-clave="negativo" data-tag="{vacante}">
                                {vacante} <span class="patch-var-desc">(Puesto o CV General)</span>
                            </span>
                            <span class="patch-var-chip" data-clave="negativo" data-tag="{empresa}">
                                {empresa} <span class="patch-var-desc">(Kombitec)</span>
                            </span>
                        </div>
                    </div>

                    <form class="form-guardar-plantilla" data-clave="negativo">
                        <div class="patch-form-group">
                            <label class="patch-form-label" for="asunto_negativo">Asunto del correo:</label>
                            <div class="patch-form-help">Puedes incluir etiquetas como {vacante} o {empresa}.</div>
                            <input
                                type="text"
                                id="asunto_negativo"
                                name="asunto"
                                class="patch-input"
                                value="<?php echo e($plantillaNegativa['asunto']); ?>"
                                required
                            >
                        </div>

                        <div class="patch-form-group">
                            <label class="patch-form-label" for="cuerpo_negativo">Cuerpo del correo:</label>
                            <div class="patch-form-help">Redacta el mensaje institucional respetuoso de agradecimiento.</div>
                            <textarea
                                id="cuerpo_negativo"
                                name="cuerpo"
                                class="patch-textarea"
                                rows="12"
                                required
                            ><?php echo e($plantillaNegativa['cuerpo']); ?></textarea>
                        </div>

                        <!-- VISTA PREVIA SIMULADA TIPO EMAIL -->
                        <div class="patch-preview-wrapper">
                            <div class="patch-preview-title">
                                <i class="fa fa-eye"></i> Vista previa del correo oficial (con datos de muestra)
                            </div>
                            <div class="patch-preview-card-mockup">
                                <div class="patch-preview-header-mockup">
                                    <strong>KOMBITEC</strong>
                                    <span>Bolsa de Trabajo &middot; Notificación de Postulación</span>
                                </div>
                                <div class="patch-preview-content" id="preview_negativo"></div>
                            </div>
                        </div>

                        <div class="patch-form-actions">
                            <button type="submit" class="patch-submit-btn">
                                <i class="fa fa-floppy-disk"></i>
                                Guardar plantilla negativa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- TOAST DE NOTIFICACIÓN -->
    <div class="patch-toast" id="patchToast" role="status" aria-live="polite"></div>

    <!-- SCRIPTS -->
    <script src="js/plantillas-admin.js"></script>
</body>
</html>
