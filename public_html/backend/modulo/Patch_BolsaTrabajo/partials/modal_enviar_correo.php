<!-- MODAL DE ENVÍO DE CORREO AL POSTULANTE -->
<div class="patch-modal" id="modalEnviarCorreo" aria-hidden="true">
    <div class="patch-modal-container patch-modal-correo" role="dialog" aria-modal="true" aria-labelledby="modalTituloEnviarCorreo">

        <!-- CABECERA DEL MODAL -->
        <div class="patch-modal-header">
            <div class="patch-modal-header-info">
                <div class="patch-modal-badge-wrapper">
                    <h2 id="modalTituloEnviarCorreo">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <span>Enviar Correo al Postulante</span>
                    </h2>
                    <span id="badgeTipoCorreo" class="patch-pill-tipo positive">
                        <i class="fa fa-circle-check"></i> Plantilla Positiva
                    </span>
                </div>
                <p id="modalSubtituloEnviarCorreo">
                    Confirma o personaliza el mensaje institucional antes de enviarlo.
                </p>
            </div>

            <button
                type="button"
                class="patch-modal-close"
                onclick="cerrarModalEnviarCorreo()"
                aria-label="Cerrar ventana de envío de correo"
            >
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="patch-modal-body">

            <!-- RESUMEN DEL CANDIDATO -->
            <div class="patch-correo-candidate-card">
                <div class="patch-correo-candidate-avatar" id="correoAvatar">
                    ?
                </div>
                <div class="patch-correo-candidate-info">
                    <strong id="correoNombreCandidato">Nombre del Candidato</strong>
                    <div class="patch-correo-candidate-details">
                        <span>
                            <i class="fa fa-envelope"></i>
                            <span id="correoEmailDestino">correo@ejemplo.com</span>
                        </span>
                        <span>
                            <i class="fa fa-briefcase"></i>
                            <span id="correoVacanteCandidato">Vacante</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN EXCLUSIVA DE CORREO POSITIVO: FECHA Y HORA DE CITA -->
            <div class="patch-modal-section" id="seccionCitaEntrevista" style="display: none;">
                <label class="patch-form-label" for="selectorFechaEntrevista">
                    <i class="fa fa-calendar-check" style="color: #16a34a;"></i>
                    Fecha y hora de la entrevista acordada:
                </label>
                <p class="patch-form-tip">
                    Ingresa la fecha y hora que acordaste con el candidato en la llamada telefónica. Se insertará automáticamente en el cuerpo del correo.
                </p>
                <div class="patch-date-input-wrapper">
                    <input
                        type="datetime-local"
                        id="selectorFechaEntrevista"
                        class="patch-input patch-date-input"
                    >
                </div>
            </div>

            <!-- ASUNTO DEL CORREO -->
            <div class="patch-modal-section">
                <label class="patch-form-label" for="campoAsuntoCorreo">
                    Asunto del correo:
                </label>
                <input
                    type="text"
                    id="campoAsuntoCorreo"
                    class="patch-input"
                    placeholder="Asunto del correo"
                >
            </div>

            <!-- CUERPO DEL CORREO (BLOQUEADO POR DEFECTO + BOTÓN PARA EDITAR) -->
            <div class="patch-modal-section">
                <div class="patch-toolbar-mensaje">
                    <label class="patch-form-label" for="campoCuerpoCorreo" style="margin-bottom: 0;">
                        Mensaje a enviar:
                    </label>
                    <div class="patch-toolbar-actions">
                        <span id="badgeEstadoBloqueo" class="patch-badge-lock locked">
                            <i class="fa fa-lock"></i> Bloqueado (Plantilla estándar)
                        </span>
                        <button
                            type="button"
                            id="btnToggleEditarCorreo"
                            class="patch-btn-edit-email"
                            onclick="toggleEdicionCorreo()"
                            title="Habilitar edición para personalizar este mensaje"
                        >
                            <i class="fa fa-pen-to-square"></i>
                            <span>Editar correo</span>
                        </button>
                    </div>
                </div>

                <div class="patch-textarea-wrapper">
                    <textarea
                        id="campoCuerpoCorreo"
                        class="patch-textarea patch-textarea-email readonly"
                        rows="12"
                        readonly
                    ></textarea>
                </div>

                <div class="patch-email-notice" id="avisoEdicionCorreo">
                    <i class="fa fa-circle-info"></i>
                    <span>
                        Por seguridad, el texto está protegido con la plantilla oficial. Si deseas agregar notas o requerimientos adicionales para esta persona, pulsa <strong>"Editar correo"</strong>.
                    </span>
                </div>
            </div>

            <!-- OPCIÓN DE ACTUALIZAR ESTADO AUTOMÁTICAMENTE -->
            <div class="patch-modal-section" style="padding-top: 5px;">
                <label class="patch-checkbox-label">
                    <input type="checkbox" id="checkActualizarEstado" checked>
                    <span id="textoActualizarEstado">
                        Actualizar automáticamente el estado del aspirante a <strong>"Entrevista"</strong>
                    </span>
                </label>
            </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="patch-modal-footer">
            <button
                type="button"
                class="patch-modal-footer-button secondary"
                onclick="cerrarModalEnviarCorreo()"
            >
                <i class="fa fa-xmark"></i>
                Cancelar
            </button>

            <button
                type="button"
                id="btnEnviarCorreoConfirmar"
                class="patch-modal-footer-button primary"
                onclick="enviarCorreoCandidato()"
            >
                <i class="fa fa-paper-plane"></i>
                <span>Enviar correo</span>
            </button>
        </div>

    </div>
</div>
