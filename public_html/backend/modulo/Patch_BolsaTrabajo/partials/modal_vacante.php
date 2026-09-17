<div class="patch-modal" id="modalVacante" aria-hidden="true">
    <div class="patch-modal-container" role="dialog" aria-modal="true">

        <!-- CABECERA DEL MODAL -->
        <div class="patch-modal-header">
            <h2 class="patch-modal-title">
                <i class="fa fa-briefcase"></i>
                <span id="tituloModal">Nueva vacante</span>
            </h2>
            <button type="button" class="patch-modal-close" id="btnCerrarModal" title="Cerrar">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- FORMULARIO -->
        <form id="formVacante">
            <div class="patch-modal-body">

                <input type="hidden" id="campoId" value="">

                <div class="patch-form-group">
                    <label class="patch-form-label">Título de la vacante <span class="required">*</span></label>
                    <input type="text" id="campoTitulo" class="patch-form-input"
                           placeholder="Ej. Ingeniero de Ventas Técnicas"
                           maxlength="255" autocomplete="off" required>
                </div>

                <div class="patch-form-group">
                    <label class="patch-form-label">Lo que se ofrece <span class="required">*</span></label>
                    <textarea id="campoOferta" class="patch-form-textarea"
                              placeholder="Describe las prestaciones, beneficios y condiciones que ofrece la empresa."
                              required></textarea>
                </div>

                <div class="patch-form-group">
                    <label class="patch-form-label">Requisitos <span class="required">*</span></label>
                    <textarea id="campoRequisitos" class="patch-form-textarea"
                              placeholder="Describe los conocimientos, experiencia, estudios y habilidades requeridas."
                              required></textarea>
                </div>

                <div class="patch-form-group">
                    <label class="patch-form-label">Responsabilidades <span class="required">*</span></label>
                    <textarea id="campoResponsabilidades" class="patch-form-textarea"
                              placeholder="Describe las principales actividades y responsabilidades del puesto."
                              required></textarea>
                </div>

                <div class="patch-form-row">
                    <div class="patch-form-group">
                        <label class="patch-form-label">Fecha de cierre</label>
                        <input type="date" id="campoFechaCierre" class="patch-form-input">
                    </div>

                    <div class="patch-form-group">
                        <label class="patch-form-label">Estado</label>
                        <div class="patch-toggle-wrapper">
                            <div class="patch-toggle-info">
                                <strong>Vacante activa</strong>
                                <span>Permitirá recibir nuevas postulaciones.</span>
                            </div>
                            <label class="patch-toggle">
                                <input type="checkbox" id="campoActivo" checked>
                                <span class="patch-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PIE DEL MODAL -->
            <div class="patch-modal-footer">
                <button type="button" class="patch-btn-secondary" id="btnCancelarModal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="patch-btn-save" id="btnGuardarVacante">
                    <i class="fa fa-save"></i> Guardar vacante
                </button>
            </div>
        </form>
    </div>
</div>
