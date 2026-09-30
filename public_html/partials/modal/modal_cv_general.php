<div class="modal-cv-general" id="modalCVGeneral" aria-hidden="true">

    <div
        class="modal-cv-general-overlay"
        id="cerrarModalCVGeneral"
    ></div>

    <div
        class="modal-cv-general-contenedor"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalCVGeneralTitulo"
        tabindex="-1"
    >

        <div class="modal-cv-general-header">

            <div>

                <span class="modal-cv-general-kicker">
                    Bolsa de Trabajo
                </span>

                <h2 id="modalCVGeneralTitulo">
                    Envíanos tu CV
                </h2>

                <p>
                    Comparte tu información y currículum para que podamos
                    considerarte en futuras oportunidades.
                </p>

            </div>

            <button
                type="button"
                class="modal-cv-general-cerrar"
                id="botonCerrarModalCVGeneral"
                aria-label="Cerrar formulario de envío de CV"
            >

                <svg
                    viewBox="0 0 24 24"
                    width="22"
                    height="22"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >

                    <path
                        d="M6 6l12 12M18 6L6 18"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />

                </svg>

            </button>

        </div>


        <div class="modal-cv-general-contenido">

            <div
                class="modal-cv-general-mensaje"
                id="mensajeCVGeneral"
                role="alert"
                aria-live="polite"
                hidden
            ></div>


            <form
                id="formularioCVGeneral"
                enctype="multipart/form-data"
                novalidate
            >

                <div class="modal-formulario-grid">


                    <!-- NOMBRE -->

                    <div class="modal-campo modal-campo-completo">

                        <label for="nombreCompletoCVGeneral">

                            Nombre completo

                            <span aria-hidden="true">*</span>

                        </label>

                        <input
                            type="text"
                            id="nombreCompletoCVGeneral"
                            name="nombre_completo"
                            maxlength="255"
                            autocomplete="name"
                            placeholder="Ingresa tu nombre completo"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorNombreCVGeneral"
                        ></small>

                    </div>


                    <!-- CORREO -->

                    <div class="modal-campo">

                        <label for="correoCVGeneral">

                            Correo electrónico

                            <span aria-hidden="true">*</span>

                        </label>

                        <input
                            type="email"
                            id="correoCVGeneral"
                            name="correo"
                            maxlength="255"
                            autocomplete="email"
                            placeholder="ejemplo@correo.com"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorCorreoCVGeneral"
                        ></small>

                    </div>


                    <!-- TELEFONO -->

                    <div class="modal-campo">

                        <label for="telefonoCVGeneral">

                            Teléfono

                            <span aria-hidden="true">*</span>

                        </label>

                        <input
                            type="tel"
                            id="telefonoCVGeneral"
                            name="telefono"
                            maxlength="30"
                            autocomplete="tel"
                            placeholder="Ingresa tu teléfono"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorTelefonoCVGeneral"
                        ></small>

                    </div>


                    <!-- CV -->

                    <div class="modal-campo modal-campo-completo">

                        <label for="cvCVGeneral">

                            Currículum vitae

                            <span aria-hidden="true">*</span>

                        </label>


                        <div class="modal-archivo">

                            <label
                                for="cvCVGeneral"
                                class="modal-archivo-boton"
                            >

                                <span
                                    class="modal-archivo-icono"
                                    aria-hidden="true"
                                >

                                    <svg
                                        viewBox="0 0 24 24"
                                        width="22"
                                        height="22"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >

                                        <path
                                            d="M12 16V4"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />

                                        <path
                                            d="m7.5 8.5 4.5-4.5 4.5 4.5"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />

                                        <path
                                            d="M5 14.5v3A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5v-3"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />

                                    </svg>

                                </span>


                                <span>

                                    <strong>
                                        Seleccionar CV
                                    </strong>

                                    <small>
                                        Únicamente archivos PDF
                                    </small>

                                </span>

                            </label>


                            <input
                                type="file"
                                id="cvCVGeneral"
                                name="cv"
                                accept=".pdf,application/pdf"
                                required
                            >


                            <div
                                class="modal-archivo-seleccionado"
                                id="archivoCVGeneralSeleccionado"
                                hidden
                            >

                                <span
                                    class="modal-archivo-pdf"
                                    aria-hidden="true"
                                >
                                    PDF
                                </span>

                                <span
                                    class="modal-archivo-nombre"
                                    id="nombreArchivoCVGeneral"
                                ></span>

                                <button
                                    type="button"
                                    class="modal-archivo-quitar"
                                    id="quitarArchivoCVGeneral"
                                    aria-label="Quitar archivo"
                                >

                                    <svg
                                        viewBox="0 0 24 24"
                                        width="18"
                                        height="18"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    >

                                        <path
                                            d="M6 6l12 12M18 6L6 18"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />

                                    </svg>

                                </button>

                            </div>

                        </div>


                        <small class="modal-ayuda">
                            Selecciona tu currículum en formato PDF.
                            Tamaño máximo: 5 MB.
                        </small>


                        <small
                            class="modal-error-campo"
                            id="errorCVGeneral"
                        ></small>

                    </div>


                    <!-- COMENTARIOS -->

                    <div class="modal-campo modal-campo-completo">

                        <label for="comentariosCVGeneral">

                            Comentarios

                            <span class="modal-opcional">
                                (opcional)
                            </span>

                        </label>

                        <textarea
                            id="comentariosCVGeneral"
                            name="comentarios"
                            maxlength="2000"
                            rows="5"
                            placeholder="Puedes agregar información adicional que consideres importante."
                        ></textarea>

                        <small
                            class="modal-contador"
                            id="contadorComentariosCVGeneral"
                        >
                            0 / 2000
                        </small>

                        <small
                            class="modal-error-campo"
                            id="errorComentariosCVGeneral"
                        ></small>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="modal-cv-general-footer">

                    <p class="modal-cv-general-obligatorio">

                        <span aria-hidden="true">*</span>

                        Campos obligatorios

                    </p>


                    <div class="modal-cv-general-acciones">

                        <button
                            type="button"
                            class="modal-boton-secundario"
                            id="botonCancelarCVGeneral"
                        >
                            Cancelar
                        </button>


                        <button
                            type="submit"
                            class="modal-boton-principal"
                            id="botonEnviarCVGeneral"
                        >

                            <span class="modal-boton-principal-texto">
                                Enviar CV
                            </span>

                            <span
                                class="modal-boton-spinner"
                                id="spinnerCVGeneral"
                                aria-hidden="true"
                                hidden
                            >

                                <span></span>
                                <span></span>
                                <span></span>

                            </span>

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>


<style>

    .modal-cv-general {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease, visibility .2s ease;
    }

    .modal-cv-general.activo {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .modal-cv-general-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    .modal-cv-general-contenedor {
        position: relative;
        z-index: 1;
        width: min(100%, 720px);
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
        transform: translateY(18px) scale(.98);
        transition: transform .2s ease;
        outline: none;
    }

    .modal-cv-general.activo .modal-cv-general-contenedor {
        transform: translateY(0) scale(1);
    }

    .modal-cv-general-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 28px 30px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-cv-general-kicker {
        display: block;
        margin-bottom: 6px;
        color: #16834b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .modal-cv-general-header h2 {
        margin: 0;
        color: #17202a;
        font-size: 25px;
        line-height: 1.25;
    }

    .modal-cv-general-header p {
        max-width: 580px;
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    .modal-cv-general-cerrar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        padding: 0;
        border: 0;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    .modal-cv-general-cerrar:hover {
        background: #e2e8f0;
        color: #17202a;
    }

    .modal-cv-general-contenido {
        padding: 26px 30px 30px;
    }

    .modal-cv-general-mensaje {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.5;
    }

    .modal-cv-general-mensaje.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .modal-cv-general-mensaje.exito {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .modal-formulario-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 18px;
    }

    .modal-campo {
        min-width: 0;
    }

    .modal-campo-completo {
        grid-column: 1 / -1;
    }

    .modal-campo label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
    }

    .modal-campo label > span:not(.modal-opcional) {
        color: #dc2626;
    }

    .modal-opcional {
        color: #94a3b8 !important;
        font-size: 12px;
        font-weight: 400;
    }

    .modal-campo input[type="text"],
    .modal-campo input[type="email"],
    .modal-campo input[type="tel"],
    .modal-campo textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: #17202a;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .modal-campo input[type="text"],
    .modal-campo input[type="email"],
    .modal-campo input[type="tel"] {
        height: 46px;
        padding: 0 13px;
    }

    .modal-campo textarea {
        min-height: 120px;
        padding: 12px 13px;
        resize: vertical;
        line-height: 1.5;
    }

    .modal-campo input:focus,
    .modal-campo textarea:focus {
        border-color: #16834b;
        box-shadow: 0 0 0 3px rgba(22, 131, 75, .10);
    }

    .modal-campo.campo-error input,
    .modal-campo.campo-error textarea {
        border-color: #dc2626;
    }

    .modal-error-campo {
        display: block;
        min-height: 17px;
        margin-top: 5px;
        color: #dc2626;
        font-size: 12px;
        line-height: 1.4;
    }

    .modal-archivo {
        width: 100%;
    }

    .modal-archivo-boton {
        display: flex !important;
        align-items: center;
        gap: 13px;
        width: 100%;
        min-height: 76px;
        box-sizing: border-box;
        padding: 14px 16px;
        border: 1px dashed #94a3b8;
        border-radius: 12px;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }

    .modal-archivo-boton:hover {
        border-color: #16834b;
        background: #f0fdf4;
    }

    .modal-archivo-icono {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 9px;
        background: #dcfce7;
        color: #16834b;
    }

    .modal-archivo-boton strong {
        display: block;
        margin-bottom: 3px;
        color: #334155;
        font-size: 14px;
    }

    .modal-archivo-boton small {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 400;
    }

    .modal-archivo input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .modal-archivo-seleccionado {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        background: #f0fdf4;
    }

    .modal-archivo-pdf {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 28px;
        padding: 0 7px;
        box-sizing: border-box;
        border-radius: 6px;
        background: #dc2626;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
    }

    .modal-archivo-nombre {
        min-width: 0;
        flex: 1;
        overflow: hidden;
        color: #334155;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .modal-archivo-quitar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        padding: 0;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #64748b;
        cursor: pointer;
    }

    .modal-archivo-quitar:hover {
        background: #dcfce7;
        color: #166534;
    }

    .modal-ayuda {
        display: block;
        margin-top: 6px;
        color: #94a3b8;
        font-size: 11px;
    }

    .modal-contador {
        display: block;
        margin-top: 5px;
        color: #94a3b8;
        font-size: 11px;
        text-align: right;
    }

    .modal-cv-general-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .modal-cv-general-obligatorio {
        margin: 0;
        color: #94a3b8;
        font-size: 11px;
    }

    .modal-cv-general-obligatorio span {
        color: #dc2626;
    }

    .modal-cv-general-acciones {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .modal-boton-secundario,
    .modal-boton-principal {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition:
            background .15s ease,
            border-color .15s ease,
            opacity .15s ease;
    }

    .modal-boton-secundario {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
    }

    .modal-boton-secundario:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .modal-boton-principal {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 170px;
        border: 1px solid #16834b;
        background: #16834b;
        color: #ffffff;
    }

    .modal-boton-principal:hover {
        background: #126b3d;
        border-color: #126b3d;
    }

    .modal-boton-principal:disabled,
    .modal-boton-secundario:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    .modal-boton-spinner {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .modal-boton-spinner span {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        animation: modalCVGeneralSpinner 1s infinite ease-in-out;
    }

    .modal-boton-spinner span:nth-child(2) {
        animation-delay: .15s;
    }

    .modal-boton-spinner span:nth-child(3) {
        animation-delay: .30s;
    }

    @keyframes modalCVGeneralSpinner {

        0%,
        80%,
        100% {
            opacity: .3;
            transform: translateY(0);
        }

        40% {
            opacity: 1;
            transform: translateY(-3px);
        }

    }

    body.modal-cv-general-abierto {
        overflow: hidden;
    }


    @media (max-width: 650px) {

        .modal-cv-general {
            padding: 12px;
        }

        .modal-cv-general-contenedor {
            max-height: calc(100vh - 24px);
            border-radius: 15px;
        }

        .modal-cv-general-header {
            padding: 22px 20px 18px;
        }

        .modal-cv-general-header h2 {
            font-size: 21px;
        }

        .modal-cv-general-contenido {
            padding: 20px;
        }

        .modal-formulario-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .modal-campo-completo {
            grid-column: auto;
        }

        .modal-cv-general-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .modal-cv-general-acciones {
            width: 100%;
        }

        .modal-boton-secundario,
        .modal-boton-principal {
            flex: 1;
        }

    }


    @media (max-width: 430px) {

        .modal-cv-general-header {
            gap: 12px;
        }

        .modal-cv-general-cerrar {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
        }

        .modal-cv-general-acciones {
            flex-direction: column-reverse;
        }

        .modal-boton-secundario,
        .modal-boton-principal {
            width: 100%;
        }

    }

</style>


<script>

(function () {

    'use strict';


    /* =====================================================
       ELEMENTOS
       ===================================================== */

    const modal =
        document.getElementById('modalCVGeneral');

    const contenedor =
        modal?.querySelector(
            '.modal-cv-general-contenedor'
        );

    const botonAbrir =
        document.getElementById(
            'abrirModalCVGeneral'
        );

    const botonCerrar =
        document.getElementById(
            'botonCerrarModalCVGeneral'
        );

    const botonCancelar =
        document.getElementById(
            'botonCancelarCVGeneral'
        );

    const overlay =
        document.getElementById(
            'cerrarModalCVGeneral'
        );

    const formulario =
        document.getElementById(
            'formularioCVGeneral'
        );

    const campoNombre =
        document.getElementById(
            'nombreCompletoCVGeneral'
        );

    const campoCorreo =
        document.getElementById(
            'correoCVGeneral'
        );

    const campoTelefono =
        document.getElementById(
            'telefonoCVGeneral'
        );

    const campoCV =
        document.getElementById(
            'cvCVGeneral'
        );

    const campoComentarios =
        document.getElementById(
            'comentariosCVGeneral'
        );

    const archivoSeleccionado =
        document.getElementById(
            'archivoCVGeneralSeleccionado'
        );

    const nombreArchivoCV =
        document.getElementById(
            'nombreArchivoCVGeneral'
        );

    const quitarArchivo =
        document.getElementById(
            'quitarArchivoCVGeneral'
        );

    const contadorComentarios =
        document.getElementById(
            'contadorComentariosCVGeneral'
        );

    const mensaje =
        document.getElementById(
            'mensajeCVGeneral'
        );

    const botonEnviar =
        document.getElementById(
            'botonEnviarCVGeneral'
        );

    const textoBotonEnviar =
        botonEnviar?.querySelector(
            '.modal-boton-principal-texto'
        );

    const spinner =
        document.getElementById(
            'spinnerCVGeneral'
        );


    /* =====================================================
       VALIDAR ELEMENTOS
       ===================================================== */

    if (
        !modal ||
        !botonAbrir ||
        !formulario
    ) {
        return;
    }


    /* =====================================================
       VARIABLES
       ===================================================== */

    let ultimoElementoFocus = null;


    const ERRORES = {

        nombre:
            document.getElementById(
                'errorNombreCVGeneral'
            ),

        correo:
            document.getElementById(
                'errorCorreoCVGeneral'
            ),

        telefono:
            document.getElementById(
                'errorTelefonoCVGeneral'
            ),

        cv:
            document.getElementById(
                'errorCVGeneral'
            )

    };


    /* =====================================================
       LIMPIAR ERRORES
       ===================================================== */

    function limpiarErrores() {

        Object.values(ERRORES).forEach(
            function (elemento) {

                if (elemento) {

                    elemento.textContent = '';

                }

            }
        );


        modal
            .querySelectorAll(
                '.modal-campo.campo-error'
            )
            .forEach(
                function (campo) {

                    campo.classList.remove(
                        'campo-error'
                    );

                }
            );


        mensaje.hidden = true;

        mensaje.textContent = '';

        mensaje.className =
            'modal-cv-general-mensaje';

    }


    /* =====================================================
       MOSTRAR ERROR DE CAMPO
       ===================================================== */

    function mostrarError(
        campo,
        elementoError,
        texto
    ) {

        const contenedorCampo =
            campo?.closest(
                '.modal-campo'
            );


        if (contenedorCampo) {

            contenedorCampo.classList.add(
                'campo-error'
            );

        }


        if (elementoError) {

            elementoError.textContent = texto;

        }

    }


    /* =====================================================
       MOSTRAR MENSAJE GENERAL
       ===================================================== */

    function mostrarMensaje(
        texto,
        tipo
    ) {

        mensaje.hidden = false;

        mensaje.textContent = texto;

        mensaje.className =
            'modal-cv-general-mensaje ' +
            (
                tipo === 'exito'
                    ? 'exito'
                    : 'error'
            );

    }


    /* =====================================================
       ABRIR MODAL
       ===================================================== */

    function abrirModal() {

        ultimoElementoFocus =
            document.activeElement;


        limpiarFormulario();


        modal.classList.add('activo');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'modal-cv-general-abierto'
        );


        setTimeout(
            function () {

                campoNombre.focus();

            },
            50
        );

    }


    /* =====================================================
       CERRAR MODAL
       ===================================================== */

    function cerrarModal() {

        if (
            botonEnviar &&
            botonEnviar.disabled
        ) {

            return;

        }


        modal.classList.remove(
            'activo'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'modal-cv-general-abierto'
        );


        limpiarFormulario();


        if (
            ultimoElementoFocus &&
            typeof ultimoElementoFocus.focus ===
                'function'
        ) {

            ultimoElementoFocus.focus();

        } else {

            botonAbrir.focus();

        }

    }


    /* =====================================================
       EVENTOS DEL MODAL
       ===================================================== */

    botonAbrir.addEventListener(
        'click',
        abrirModal
    );

    botonCerrar.addEventListener(
        'click',
        cerrarModal
    );

    botonCancelar.addEventListener(
        'click',
        cerrarModal
    );

    overlay.addEventListener(
        'click',
        cerrarModal
    );


    /* =====================================================
       TECLA ESCAPE Y FOCUS TRAP
       ===================================================== */

    document.addEventListener(
        'keydown',
        function (evento) {

            if (
                !modal.classList.contains(
                    'activo'
                )
            ) {

                return;

            }


            if (
                evento.key === 'Escape'
            ) {

                evento.preventDefault();

                cerrarModal();

                return;

            }


            if (
                evento.key === 'Tab'
            ) {

                const elementosFocus =
                    modal.querySelectorAll(
                        'button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
                    );


                if (
                    !elementosFocus.length
                ) {

                    return;

                }


                const primero =
                    elementosFocus[0];

                const ultimo =
                    elementosFocus[
                        elementosFocus.length - 1
                    ];


                if (
                    evento.shiftKey &&
                    document.activeElement ===
                        primero
                ) {

                    evento.preventDefault();

                    ultimo.focus();

                } else if (
                    !evento.shiftKey &&
                    document.activeElement ===
                        ultimo
                ) {

                    evento.preventDefault();

                    primero.focus();

                }

            }

        }
    );


    /* =====================================================
       SELECCIÓN DE CV
       ===================================================== */

    campoCV.addEventListener(
        'change',
        function () {

            limpiarErrorArchivo();


            const archivo =
                campoCV.files?.[0];


            if (!archivo) {

                archivoSeleccionado.hidden =
                    true;

                nombreArchivoCV.textContent =
                    '';

                return;

            }


            const nombre =
                archivo.name || '';


            const extension =
                nombre
                    .split('.')
                    .pop()
                    .toLowerCase();


            if (
                extension !== 'pdf'
            ) {

                campoCV.value = '';

                archivoSeleccionado.hidden =
                    true;

                nombreArchivoCV.textContent =
                    '';


                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV debe estar en formato PDF.'
                );

                return;

            }


            if (
                archivo.type &&
                archivo.type !==
                    'application/pdf'
            ) {

                campoCV.value = '';

                archivoSeleccionado.hidden =
                    true;

                nombreArchivoCV.textContent =
                    '';


                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El archivo seleccionado no parece ser un PDF válido.'
                );

                return;

            }


            const maximoBytes =
                5 * 1024 * 1024;


            if (
                archivo.size >
                maximoBytes
            ) {

                campoCV.value = '';

                archivoSeleccionado.hidden =
                    true;

                nombreArchivoCV.textContent =
                    '';


                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV no puede superar los 5 MB.'
                );

                return;

            }


            nombreArchivoCV.textContent =
                nombre;

            archivoSeleccionado.hidden =
                false;

        }
    );


    /* =====================================================
       QUITAR CV
       ===================================================== */

    quitarArchivo.addEventListener(
        'click',
        function () {

            campoCV.value = '';

            archivoSeleccionado.hidden =
                true;

            nombreArchivoCV.textContent =
                '';

            limpiarErrorArchivo();

            campoCV.focus();

        }
    );


    /* =====================================================
       LIMPIAR ERROR CV
       ===================================================== */

    function limpiarErrorArchivo() {

        const contenedorCampo =
            campoCV.closest(
                '.modal-campo'
            );


        if (contenedorCampo) {

            contenedorCampo.classList.remove(
                'campo-error'
            );

        }


        if (ERRORES.cv) {

            ERRORES.cv.textContent = '';

        }

    }


    /* =====================================================
       CONTADOR DE COMENTARIOS
       ===================================================== */

    campoComentarios.addEventListener(
        'input',
        function () {

            contadorComentarios.textContent =
                campoComentarios.value.length +
                ' / 2000';

        }
    );


    /* =====================================================
       VALIDAR FORMULARIO
       ===================================================== */

    function validarFormulario() {

        limpiarErrores();


        let valido = true;


        const nombre =
            campoNombre.value.trim();

        const correo =
            campoCorreo.value.trim();

        const telefono =
            campoTelefono.value.trim();

        const archivo =
            campoCV.files?.[0];


        /* NOMBRE */

        if (
            nombre === ''
        ) {

            mostrarError(
                campoNombre,
                ERRORES.nombre,
                'Ingresa tu nombre completo.'
            );

            valido = false;

        }


        /* CORREO */

        if (
            correo === ''
        ) {

            mostrarError(
                campoCorreo,
                ERRORES.correo,
                'Ingresa tu correo electrónico.'
            );

            valido = false;

        } else if (
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/
                .test(correo)
        ) {

            mostrarError(
                campoCorreo,
                ERRORES.correo,
                'Ingresa un correo electrónico válido.'
            );

            valido = false;

        }


        /* TELEFONO */

        if (
            telefono === ''
        ) {

            mostrarError(
                campoTelefono,
                ERRORES.telefono,
                'Ingresa tu número de teléfono.'
            );

            valido = false;

        }


        /* CV */

        if (!archivo) {

            mostrarError(
                campoCV,
                ERRORES.cv,
                'Selecciona tu currículum en formato PDF.'
            );

            valido = false;

        } else {

            const extension =
                (
                    archivo.name
                        .split('.')
                        .pop() ||
                    ''
                ).toLowerCase();


            if (
                extension !== 'pdf'
            ) {

                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV debe estar en formato PDF.'
                );

                valido = false;

            }


            const maximoBytes =
                5 * 1024 * 1024;


            if (
                archivo.size >
                maximoBytes
            ) {

                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV no puede superar los 5 MB.'
                );

                valido = false;

            }

        }


        return valido;

    }


    /* =====================================================
       ESTADO DEL BOTÓN
       ===================================================== */

    function cambiarEstadoEnvio(
        enviando
    ) {

        botonEnviar.disabled =
            enviando;


        if (enviando) {

            textoBotonEnviar.textContent =
                'Enviando...';

            spinner.hidden = false;

        } else {

            textoBotonEnviar.textContent =
                'Enviar CV';

            spinner.hidden = true;

        }

    }


    /* =====================================================
       LIMPIAR FORMULARIO
       ===================================================== */

    function limpiarFormulario() {

        formulario.reset();


        archivoSeleccionado.hidden =
            true;

        nombreArchivoCV.textContent =
            '';


        contadorComentarios.textContent =
            '0 / 2000';


        limpiarErrores();

    }


    /* =====================================================
       ENVÍO DEL CV GENERAL
       ===================================================== */

    formulario.addEventListener(
        'submit',
        async function (evento) {

            evento.preventDefault();


            if (
                !validarFormulario()
            ) {

                return;

            }


            cambiarEstadoEnvio(true);


            try {

                /*
                 * FormData toma automáticamente:
                 *
                 * nombre_completo
                 * correo
                 * telefono
                 * cv
                 * comentarios
                 */

                const datos =
                    new FormData(
                        formulario
                    );


                /*
                 * Endpoint para CV general.
                 *
                 * El archivo se encuentra dentro de
                 * public_html/partials/modal/
                 *
                 * Por eso se utiliza una ruta absoluta
                 * desde la raíz pública del proyecto.
                 */

                const respuesta =
                    await fetch(
                        'backend/modulo/Patch_BolsaTrabajo/api/cv_general.php',
                        {
                            method: 'POST',
                            body: datos
                        }
                    );


                let resultado;


                try {

                    resultado =
                        await respuesta.json();

                } catch (error) {

                    throw new Error(
                        'El servidor devolvió una respuesta no válida.'
                    );

                }


                if (
                    !respuesta.ok ||
                    !resultado.ok
                ) {

                    throw new Error(
                        resultado.mensaje ||
                        'No fue posible enviar tu CV.'
                    );

                }


                /* =========================================
                   CV ENVIADO CORRECTAMENTE
                   ========================================= */

                mostrarMensaje(
                    resultado.mensaje ||
                    'Tu CV fue enviado correctamente.',
                    'exito'
                );


                /*
                 * Limpiamos los datos personales
                 * y el archivo seleccionado.
                 */

                formulario.reset();

                archivoSeleccionado.hidden =
                    true;

                nombreArchivoCV.textContent =
                    '';

                contadorComentarios.textContent =
                    '0 / 2000';


                /*
                 * Volvemos a mostrar el mensaje de éxito
                 * después de limpiar el formulario.
                 */

                mostrarMensaje(
                    resultado.mensaje ||
                    'Tu CV fue enviado correctamente.',
                    'exito'
                );


                /*
                 * Cerramos el modal después de
                 * mostrar la confirmación.
                 */

                setTimeout(
                    function () {

                        if (
                            modal.classList.contains(
                                'activo'
                            )
                        ) {

                            cerrarModal();

                        }

                    },
                    1800
                );


            } catch (error) {

                console.error(
                    'Error al enviar CV general:',
                    error
                );


                mostrarMensaje(
                    error.message ||
                    'Ocurrió un error al enviar tu CV. Inténtalo nuevamente.',
                    'error'
                );


            } finally {

                cambiarEstadoEnvio(false);

            }

        }
    );

})();

</script>

