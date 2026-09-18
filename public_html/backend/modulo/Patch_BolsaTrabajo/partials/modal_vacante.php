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

                    <label class="patch-form-label">
                        Título de la vacante <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="campoTitulo"
                        class="patch-form-input"
                        placeholder="Ej. Ingeniero de Ventas Técnicas"
                        maxlength="255"
                        autocomplete="off"
                        required
                    >

                </div>


                <!-- LO QUE SE OFRECE -->

                <div class="patch-form-group">

                    <label class="patch-form-label">
                        Lo que se ofrece <span class="required">*</span>
                    </label>

                    <div class="patch-editor" data-editor="oferta">

                        <div class="patch-editor-toolbar">

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="bold"
                                title="Negrita"
                            >
                                <i class="fa fa-bold"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="italic"
                                title="Cursiva"
                            >
                                <i class="fa fa-italic"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertUnorderedList"
                                title="Lista con viñetas"
                            >
                                <i class="fa fa-list-ul"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertOrderedList"
                                title="Lista numerada"
                            >
                                <i class="fa fa-list-ol"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="removeFormat"
                                title="Quitar formato"
                            >
                                <i class="fa fa-eraser"></i>
                            </button>

                        </div>

                        <div
                            id="campoOferta"
                            class="patch-editor-content"
                            contenteditable="true"
                            data-placeholder="Describe las prestaciones, beneficios y condiciones que ofrece la empresa."
                        ></div>

                    </div>

                </div>


                <!-- REQUISITOS -->

                <div class="patch-form-group">

                    <label class="patch-form-label">
                        Requisitos <span class="required">*</span>
                    </label>

                    <div class="patch-editor" data-editor="requisitos">

                        <div class="patch-editor-toolbar">

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="bold"
                                title="Negrita"
                            >
                                <i class="fa fa-bold"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="italic"
                                title="Cursiva"
                            >
                                <i class="fa fa-italic"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertUnorderedList"
                                title="Lista con viñetas"
                            >
                                <i class="fa fa-list-ul"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertOrderedList"
                                title="Lista numerada"
                            >
                                <i class="fa fa-list-ol"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="removeFormat"
                                title="Quitar formato"
                            >
                                <i class="fa fa-eraser"></i>
                            </button>

                        </div>

                        <div
                            id="campoRequisitos"
                            class="patch-editor-content"
                            contenteditable="true"
                            data-placeholder="Describe los conocimientos, experiencia, estudios y habilidades requeridas."
                        ></div>

                    </div>

                </div>


                <!-- RESPONSABILIDADES -->

                <div class="patch-form-group">

                    <label class="patch-form-label">
                        Responsabilidades <span class="required">*</span>
                    </label>

                    <div class="patch-editor" data-editor="responsabilidades">

                        <div class="patch-editor-toolbar">

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="bold"
                                title="Negrita"
                            >
                                <i class="fa fa-bold"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="italic"
                                title="Cursiva"
                            >
                                <i class="fa fa-italic"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertUnorderedList"
                                title="Lista con viñetas"
                            >
                                <i class="fa fa-list-ul"></i>
                            </button>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="insertOrderedList"
                                title="Lista numerada"
                            >
                                <i class="fa fa-list-ol"></i>
                            </button>

                            <span class="patch-editor-separator"></span>

                            <button
                                type="button"
                                class="patch-editor-btn"
                                data-command="removeFormat"
                                title="Quitar formato"
                            >
                                <i class="fa fa-eraser"></i>
                            </button>

                        </div>

                        <div
                            id="campoResponsabilidades"
                            class="patch-editor-content"
                            contenteditable="true"
                            data-placeholder="Describe las principales actividades y responsabilidades del puesto."
                        ></div>

                    </div>

                </div>


                <!-- FECHA Y ESTADO -->

                <div class="patch-form-row">

                    <div class="patch-form-group">

                        <label class="patch-form-label">
                            Fecha de cierre
                        </label>

                        <input
                            type="date"
                            id="campoFechaCierre"
                            class="patch-form-input"
                        >

                    </div>


                    <div class="patch-form-group">

                        <label class="patch-form-label">
                            Estado
                        </label>

                        <div class="patch-toggle-wrapper">

                            <div class="patch-toggle-info">

                                <strong>Vacante activa</strong>

                                <span>
                                    Permitirá recibir nuevas postulaciones.
                                </span>

                            </div>

                            <label class="patch-toggle">

                                <input
                                    type="checkbox"
                                    id="campoActivo"
                                    checked
                                >

                                <span class="patch-toggle-slider"></span>

                            </label>

                        </div>

                    </div>

                </div>

            </div>


            <!-- PIE DEL MODAL -->

            <div class="patch-modal-footer">

                <button
                    type="button"
                    class="patch-btn-secondary"
                    id="btnCancelarModal"
                >
                    <i class="fa fa-times"></i>
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="patch-btn-save"
                    id="btnGuardarVacante"
                >
                    <i class="fa fa-save"></i>
                    Guardar vacante
                </button>

            </div>

        </form>

    </div>

</div>


<!-- ==========================================
     FUNCIONALIDAD DEL EDITOR
     ========================================== -->

<script>

document.querySelectorAll('.patch-editor').forEach(function(editor) {

    const contenido = editor.querySelector('.patch-editor-content');

    const botones = editor.querySelectorAll('.patch-editor-btn');


    botones.forEach(function(boton) {

        /*
         * Evita que al hacer clic en el botón
         * se pierda la selección del texto.
         */
        boton.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });


        boton.addEventListener('click', function() {

            const comando = boton.dataset.command;

            contenido.focus();

            document.execCommand(comando, false, null);

            actualizarBotonesEditor(editor);

        });

    });


    contenido.addEventListener('keyup', function() {
        actualizarBotonesEditor(editor);
    });


    contenido.addEventListener('mouseup', function() {
        actualizarBotonesEditor(editor);
    });


    contenido.addEventListener('input', function() {
        actualizarBotonesEditor(editor);
    });

});


function actualizarBotonesEditor(editor) {

    const botones = editor.querySelectorAll('.patch-editor-btn');


    botones.forEach(function(boton) {

        const comando = boton.dataset.command;


        if (
            comando === 'bold' ||
            comando === 'italic'
        ) {

            try {

                boton.classList.toggle(
                    'active',
                    document.queryCommandState(comando)
                );

            } catch (error) {

                boton.classList.remove('active');

            }

        }

    });

}

</script>
