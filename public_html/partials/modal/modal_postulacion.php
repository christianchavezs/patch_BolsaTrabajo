<div class="modal-postulacion" id="modalPostulacion" aria-hidden="true">

    <div class="modal-postulacion-overlay" id="cerrarModalPostulacion"></div>

    <div
        class="modal-postulacion-contenedor"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalPostulacionTitulo"
        tabindex="-1"
    >

        <div class="modal-postulacion-header">

            <div>
                <span class="modal-postulacion-kicker">Postulación</span>

                <h2 id="modalPostulacionTitulo">
                    Postúlate a esta vacante
                </h2>

                <p id="modalPostulacionVacante"></p>
            </div>

            <button
                type="button"
                class="modal-postulacion-cerrar"
                id="botonCerrarModalPostulacion"
                aria-label="Cerrar formulario de postulación"
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


        <div class="modal-postulacion-contenido">

            <div
                class="modal-postulacion-mensaje"
                id="mensajePostulacion"
                role="alert"
                aria-live="polite"
                hidden
            ></div>


            <form
                id="formularioPostulacion"
                enctype="multipart/form-data"
                novalidate
            >

                <!-- Token de la vacante -->
                <input
                    type="hidden"
                    name="token"
                    id="tokenVacantePostulacion"
                    value=""
                >


                <div class="modal-formulario-grid">


                    <!-- NOMBRE -->
                    <div class="modal-campo modal-campo-completo">

                        <label for="nombreCompletoPostulacion">
                            Nombre completo
                            <span aria-hidden="true">*</span>
                        </label>

                        <input
                            type="text"
                            id="nombreCompletoPostulacion"
                            name="nombre_completo"
                            maxlength="255"
                            autocomplete="name"
                            placeholder="Ingresa tu nombre completo"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorNombrePostulacion"
                        ></small>

                    </div>


                    <!-- CORREO -->
                    <div class="modal-campo">

                        <label for="correoPostulacion">
                            Correo electrónico
                            <span aria-hidden="true">*</span>
                        </label>

                        <input
                            type="email"
                            id="correoPostulacion"
                            name="correo"
                            maxlength="255"
                            autocomplete="email"
                            placeholder="ejemplo@correo.com"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorCorreoPostulacion"
                        ></small>

                    </div>


                    <!-- TELEFONO -->
                    <div class="modal-campo">

                        <label for="telefonoPostulacion">
                            Teléfono
                            <span aria-hidden="true">*</span>
                        </label>

                        <input
                            type="tel"
                            id="telefonoPostulacion"
                            name="telefono"
                            maxlength="30"
                            autocomplete="tel"
                            placeholder="Ingresa tu teléfono"
                            required
                        >

                        <small
                            class="modal-error-campo"
                            id="errorTelefonoPostulacion"
                        ></small>

                    </div>


                    <!-- CV -->
                    <div class="modal-campo modal-campo-completo">

                        <label for="cvPostulacion">
                            Currículum vitae
                            <span aria-hidden="true">*</span>
                        </label>


                        <div class="modal-archivo">

                            <label
                                for="cvPostulacion"
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
                                    <strong>Seleccionar CV</strong>
                                    <small>Únicamente archivos PDF</small>
                                </span>

                            </label>


                            <input
                                type="file"
                                id="cvPostulacion"
                                name="cv"
                                accept=".pdf,application/pdf"
                                required
                            >


                            <div
                                class="modal-archivo-seleccionado"
                                id="archivoCVSeleccionado"
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
                                    id="nombreArchivoCV"
                                ></span>

                                <button
                                    type="button"
                                    class="modal-archivo-quitar"
                                    id="quitarArchivoCV"
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
                            Selecciona tu currículum en formato PDF. Tamaño máximo: 5 MB.
                        </small>


                        <small
                            class="modal-error-campo"
                            id="errorCVPostulacion"
                        ></small>

                    </div>


                    <!-- COMENTARIOS -->
                    <div class="modal-campo modal-campo-completo">

                        <label for="comentariosPostulacion">
                            Comentarios
                            <span class="modal-opcional">(opcional)</span>
                        </label>

                        <textarea
                            id="comentariosPostulacion"
                            name="comentarios"
                            maxlength="2000"
                            rows="5"
                            placeholder="Puedes agregar información adicional que consideres importante."
                        ></textarea>

                        <small
                            class="modal-contador"
                            id="contadorComentarios"
                        >
                            0 / 2000
                        </small>

                        <small
                            class="modal-error-campo"
                            id="errorComentariosPostulacion"
                        ></small>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="modal-postulacion-footer">

                    <p class="modal-postulacion-obligatorio">
                        <span aria-hidden="true">*</span>
                        Campos obligatorios
                    </p>


                    <div class="modal-postulacion-acciones">

                        <button
                            type="button"
                            class="modal-boton-secundario"
                            id="botonCancelarPostulacion"
                        >
                            Cancelar
                        </button>


                        <button
                            type="submit"
                            class="modal-boton-principal"
                            id="botonEnviarPostulacion"
                        >

                            <span class="modal-boton-principal-texto">
                                Enviar postulación
                            </span>

                            <span
                                class="modal-boton-spinner"
                                id="spinnerPostulacion"
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

    .modal-postulacion {
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

    .modal-postulacion.activo {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .modal-postulacion-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    .modal-postulacion-contenedor {
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

    .modal-postulacion.activo .modal-postulacion-contenedor {
        transform: translateY(0) scale(1);
    }

    .modal-postulacion-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 28px 30px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-postulacion-kicker {
        display: block;
        margin-bottom: 6px;
        color: #16834b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .modal-postulacion-header h2 {
        margin: 0;
        color: #17202a;
        font-size: 25px;
        line-height: 1.25;
    }

    .modal-postulacion-header p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    .modal-postulacion-cerrar {
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

    .modal-postulacion-cerrar:hover {
        background: #e2e8f0;
        color: #17202a;
    }

    .modal-postulacion-contenido {
        padding: 26px 30px 30px;
    }

    .modal-postulacion-mensaje {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.5;
    }

    .modal-postulacion-mensaje.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .modal-postulacion-mensaje.exito {
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

    .modal-postulacion-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .modal-postulacion-obligatorio {
        margin: 0;
        color: #94a3b8;
        font-size: 11px;
    }

    .modal-postulacion-obligatorio span {
        color: #dc2626;
    }

    .modal-postulacion-acciones {
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
        transition: background .15s ease, border-color .15s ease, opacity .15s ease;
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
        animation: modalPostulacionSpinner 1s infinite ease-in-out;
    }

    .modal-boton-spinner span:nth-child(2) {
        animation-delay: .15s;
    }

    .modal-boton-spinner span:nth-child(3) {
        animation-delay: .30s;
    }

    @keyframes modalPostulacionSpinner {

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

    body.modal-postulacion-abierto {
        overflow: hidden;
    }


    @media (max-width: 650px) {

        .modal-postulacion {
            padding: 12px;
        }

        .modal-postulacion-contenedor {
            max-height: calc(100vh - 24px);
            border-radius: 15px;
        }

        .modal-postulacion-header {
            padding: 22px 20px 18px;
        }

        .modal-postulacion-header h2 {
            font-size: 21px;
        }

        .modal-postulacion-contenido {
            padding: 20px;
        }

        .modal-formulario-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .modal-campo-completo {
            grid-column: auto;
        }

        .modal-postulacion-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .modal-postulacion-acciones {
            width: 100%;
        }

        .modal-boton-secundario,
        .modal-boton-principal {
            flex: 1;
        }
    }


    @media (max-width: 430px) {

        .modal-postulacion-header {
            gap: 12px;
        }

        .modal-postulacion-cerrar {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
        }

        .modal-postulacion-acciones {
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

    const modal = document.getElementById('modalPostulacion');
    const contenedor = modal?.querySelector('.modal-postulacion-contenedor');

    const botonAbrir = document.getElementById('abrirModalPostulacion');
    const botonCerrar = document.getElementById('botonCerrarModalPostulacion');
    const botonCancelar = document.getElementById('botonCancelarPostulacion');
    const overlay = document.getElementById('cerrarModalPostulacion');

    const formulario = document.getElementById('formularioPostulacion');

    const tokenInput = document.getElementById('tokenVacantePostulacion');
    const vacanteTexto = document.getElementById('modalPostulacionVacante');

    const campoNombre = document.getElementById('nombreCompletoPostulacion');
    const campoCorreo = document.getElementById('correoPostulacion');
    const campoTelefono = document.getElementById('telefonoPostulacion');
    const campoCV = document.getElementById('cvPostulacion');
    const campoComentarios = document.getElementById('comentariosPostulacion');

    const archivoSeleccionado = document.getElementById('archivoCVSeleccionado');
    const nombreArchivoCV = document.getElementById('nombreArchivoCV');
    const quitarArchivo = document.getElementById('quitarArchivoCV');

    const contadorComentarios = document.getElementById('contadorComentarios');

    const mensaje = document.getElementById('mensajePostulacion');

    const botonEnviar = document.getElementById('botonEnviarPostulacion');
    const textoBotonEnviar = botonEnviar?.querySelector('.modal-boton-principal-texto');
    const spinner = document.getElementById('spinnerPostulacion');


    /* =====================================================
       VALIDAR ELEMENTOS
       ===================================================== */

    if (!modal || !botonAbrir || !formulario) {
        return;
    }


    /* =====================================================
       VARIABLES
       ===================================================== */

    let ultimoElementoFocus = null;


    const ERRORES = {
        nombre: document.getElementById('errorNombrePostulacion'),
        correo: document.getElementById('errorCorreoPostulacion'),
        telefono: document.getElementById('errorTelefonoPostulacion'),
        cv: document.getElementById('errorCVPostulacion')
    };


    /* =====================================================
       LIMPIAR ERRORES
       ===================================================== */

    function limpiarErrores() {

        Object.values(ERRORES).forEach(function (elemento) {

            if (elemento) {
                elemento.textContent = '';
            }

        });


        document
            .querySelectorAll('.modal-campo.campo-error')
            .forEach(function (campo) {

                campo.classList.remove('campo-error');

            });


        mensaje.hidden = true;
        mensaje.textContent = '';
        mensaje.className = 'modal-postulacion-mensaje';

    }


    /* =====================================================
       MOSTRAR ERROR DE CAMPO
       ===================================================== */

    function mostrarError(campo, elementoError, texto) {

        const contenedorCampo = campo?.closest('.modal-campo');

        if (contenedorCampo) {
            contenedorCampo.classList.add('campo-error');
        }

        if (elementoError) {
            elementoError.textContent = texto;
        }

    }


    /* =====================================================
       MOSTRAR MENSAJE GENERAL
       ===================================================== */

    function mostrarMensaje(texto, tipo) {

        mensaje.hidden = false;
        mensaje.textContent = texto;

        mensaje.className =
            'modal-postulacion-mensaje ' +
            (tipo === 'exito' ? 'exito' : 'error');

    }


    /* =====================================================
       ABRIR MODAL
       ===================================================== */

    function abrirModal() {

        ultimoElementoFocus = document.activeElement;


        const token = botonAbrir.dataset.vacanteToken || '';
        const titulo = botonAbrir.dataset.vacanteTitulo || '';


        tokenInput.value = token;


        vacanteTexto.textContent = titulo
            ? titulo
            : 'Vacante disponible';


        limpiarErrores();


        modal.classList.add('activo');

        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('modal-postulacion-abierto');


        setTimeout(function () {

            campoNombre.focus();

        }, 50);

    }


    /* =====================================================
       CERRAR MODAL
       ===================================================== */

    function cerrarModal() {

        if (botonEnviar && botonEnviar.disabled) {
            return;
        }


        modal.classList.remove('activo');

        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('modal-postulacion-abierto');


        limpiarErrores();


        if (
            ultimoElementoFocus &&
            typeof ultimoElementoFocus.focus === 'function'
        ) {

            ultimoElementoFocus.focus();

        } else {

            botonAbrir.focus();

        }

    }


    /* =====================================================
       EVENTOS DEL MODAL
       ===================================================== */

    botonAbrir.addEventListener('click', abrirModal);

    botonCerrar.addEventListener('click', cerrarModal);

    botonCancelar.addEventListener('click', cerrarModal);

    overlay.addEventListener('click', cerrarModal);


    /* =====================================================
       TECLA ESCAPE Y FOCUS TRAP
       ===================================================== */

    document.addEventListener('keydown', function (evento) {

        if (!modal.classList.contains('activo')) {
            return;
        }


        if (evento.key === 'Escape') {

            evento.preventDefault();

            cerrarModal();

            return;
        }


        if (evento.key === 'Tab') {

            const elementosFocus = modal.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );


            if (!elementosFocus.length) {
                return;
            }


            const primero = elementosFocus[0];
            const ultimo = elementosFocus[elementosFocus.length - 1];


            if (
                evento.shiftKey &&
                document.activeElement === primero
            ) {

                evento.preventDefault();

                ultimo.focus();

            } else if (
                !evento.shiftKey &&
                document.activeElement === ultimo
            ) {

                evento.preventDefault();

                primero.focus();

            }

        }

    });


    /* =====================================================
       SELECCIÓN DE CV
       ===================================================== */

    campoCV.addEventListener('change', function () {

        limpiarErrorArchivo();


        const archivo = campoCV.files?.[0];


        if (!archivo) {

            archivoSeleccionado.hidden = true;
            nombreArchivoCV.textContent = '';

            return;
        }


        const nombre = archivo.name || '';

        const extension =
            nombre.split('.').pop().toLowerCase();


        if (extension !== 'pdf') {

            campoCV.value = '';

            archivoSeleccionado.hidden = true;

            nombreArchivoCV.textContent = '';


            mostrarError(
                campoCV,
                ERRORES.cv,
                'El CV debe estar en formato PDF.'
            );

            return;
        }


        if (
            archivo.type &&
            archivo.type !== 'application/pdf'
        ) {

            campoCV.value = '';

            archivoSeleccionado.hidden = true;

            nombreArchivoCV.textContent = '';


            mostrarError(
                campoCV,
                ERRORES.cv,
                'El archivo seleccionado no parece ser un PDF válido.'
            );

            return;
        }


        const maximoBytes = 5 * 1024 * 1024;


        if (archivo.size > maximoBytes) {

            campoCV.value = '';

            archivoSeleccionado.hidden = true;

            nombreArchivoCV.textContent = '';


            mostrarError(
                campoCV,
                ERRORES.cv,
                'El CV no puede superar los 5 MB.'
            );

            return;
        }


        nombreArchivoCV.textContent = nombre;

        archivoSeleccionado.hidden = false;

    });


    /* =====================================================
       QUITAR CV
       ===================================================== */

    quitarArchivo.addEventListener('click', function () {

        campoCV.value = '';

        archivoSeleccionado.hidden = true;

        nombreArchivoCV.textContent = '';

        limpiarErrorArchivo();

        campoCV.focus();

    });


    /* =====================================================
       LIMPIAR ERROR CV
       ===================================================== */

    function limpiarErrorArchivo() {

        const contenedorCampo =
            campoCV.closest('.modal-campo');


        if (contenedorCampo) {

            contenedorCampo.classList.remove('campo-error');

        }


        if (ERRORES.cv) {

            ERRORES.cv.textContent = '';

        }

    }


    /* =====================================================
       CONTADOR DE COMENTARIOS
       ===================================================== */

    campoComentarios.addEventListener('input', function () {

        contadorComentarios.textContent =
            campoComentarios.value.length + ' / 2000';

    });


    /* =====================================================
       VALIDAR FORMULARIO
       ===================================================== */

    function validarFormulario() {

        limpiarErrores();


        let valido = true;


        const nombre = campoNombre.value.trim();

        const correo = campoCorreo.value.trim();

        const telefono = campoTelefono.value.trim();

        const archivo = campoCV.files?.[0];


        /* NOMBRE */

        if (nombre === '') {

            mostrarError(
                campoNombre,
                ERRORES.nombre,
                'Ingresa tu nombre completo.'
            );

            valido = false;

        }


        /* CORREO */

        if (correo === '') {

            mostrarError(
                campoCorreo,
                ERRORES.correo,
                'Ingresa tu correo electrónico.'
            );

            valido = false;

        } else if (
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)
        ) {

            mostrarError(
                campoCorreo,
                ERRORES.correo,
                'Ingresa un correo electrónico válido.'
            );

            valido = false;

        }


        /* TELEFONO */

        if (telefono === '') {

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
                (archivo.name.split('.').pop() || '')
                .toLowerCase();


            if (extension !== 'pdf') {

                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV debe estar en formato PDF.'
                );

                valido = false;

            }


            const maximoBytes = 5 * 1024 * 1024;


            if (archivo.size > maximoBytes) {

                mostrarError(
                    campoCV,
                    ERRORES.cv,
                    'El CV no puede superar los 5 MB.'
                );

                valido = false;

            }

        }


        /* TOKEN */

        if (!tokenInput.value.trim()) {

            mostrarMensaje(
                'No fue posible identificar la vacante. Recarga la página e inténtalo nuevamente.',
                'error'
            );

            valido = false;

        }


        return valido;

    }


    /* =====================================================
       ESTADO DEL BOTÓN
       ===================================================== */

    function cambiarEstadoEnvio(enviando) {

        botonEnviar.disabled = enviando;


        if (enviando) {

            textoBotonEnviar.textContent = 'Enviando...';

            spinner.hidden = false;

        } else {

            textoBotonEnviar.textContent =
                'Enviar postulación';

            spinner.hidden = true;

        }

    }


    /* =====================================================
       LIMPIAR FORMULARIO
       ===================================================== */

    function limpiarFormulario() {

        formulario.reset();


        tokenInput.value =
            botonAbrir.dataset.vacanteToken || '';


        archivoSeleccionado.hidden = true;

        nombreArchivoCV.textContent = '';


        contadorComentarios.textContent =
            '0 / 2000';


        limpiarErrores();

    }


    /* =====================================================
       ENVÍO DE POSTULACIÓN
       ===================================================== */

    formulario.addEventListener('submit', async function (evento) {

        evento.preventDefault();


        if (!validarFormulario()) {
            return;
        }


        cambiarEstadoEnvio(true);


        try {

            /*
             * FormData toma automáticamente:
             *
             * token
             * nombre_completo
             * correo
             * telefono
             * cv
             * comentarios
             */

            const datos = new FormData(formulario);


            /*
             * Endpoint de postulación.
             *
             * Detalle_Vacante.php se encuentra dentro de
             * public_html, por lo que esta ruta apunta a:
             *
             * public_html/backend/modulo/Patch_BolsaTrabajo/api/postular.php
             */

            const respuesta = await fetch(
                'backend/modulo/Patch_BolsaTrabajo/api/postular.php',
                {
                    method: 'POST',
                    body: datos
                }
            );


            /*
             * Intentamos interpretar la respuesta
             * como JSON.
             */

            let resultado;


            try {

                resultado = await respuesta.json();

            } catch (error) {

                throw new Error(
                    'El servidor devolvió una respuesta no válida.'
                );

            }


            /*
             * Si HTTP no fue exitoso o la API respondió
             * ok = false, mostramos el mensaje recibido.
             */

            if (!respuesta.ok || !resultado.ok) {

                throw new Error(
                    resultado.mensaje ||
                    'No fue posible enviar la postulación.'
                );

            }


            /* =============================================
               POSTULACIÓN EXITOSA
               ============================================= */

            mostrarMensaje(
                resultado.mensaje ||
                'Tu postulación fue enviada correctamente.',
                'exito'
            );


            /*
             * Limpiamos los campos para que el formulario
             * no conserve información sensible.
             */

            formulario.reset();


            archivoSeleccionado.hidden = true;

            nombreArchivoCV.textContent = '';


            contadorComentarios.textContent =
                '0 / 2000';


            limpiarErrores();


            /*
             * Volvemos a mostrar el mensaje de éxito porque
             * limpiarErrores() también limpia el mensaje.
             */

            mostrarMensaje(
                resultado.mensaje ||
                'Tu postulación fue enviada correctamente.',
                'exito'
            );


            /*
             * Después de enviar correctamente dejamos el
             * modal visible durante un momento para que el
             * usuario pueda leer la confirmación.
             */

            setTimeout(function () {

                if (modal.classList.contains('activo')) {

                    cerrarModal();

                }

            }, 1800);


        } catch (error) {

            console.error(
                'Error al enviar postulación:',
                error
            );


            mostrarMensaje(
                error.message ||
                'Ocurrió un error al enviar la postulación. Inténtalo nuevamente.',
                'error'
            );


        } finally {

            cambiarEstadoEnvio(false);

        }

    });


})();

</script>