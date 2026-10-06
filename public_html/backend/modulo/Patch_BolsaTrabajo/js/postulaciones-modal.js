/**
 * ============================================================
 * MODAL DE POSTULACIÓN
 * Patch_BolsaTrabajo
 * ============================================================
 *
 * Funciones:
 * - Cargar detalle de postulación mediante API
 * - Abrir detalle de postulación
 * - Cerrar modal
 * - Mostrar información del candidato
 * - Mostrar CV
 * - Seleccionar estado
 * - Guardar estado y comentario de seguimiento
 * - Actualizar listado en tiempo real
 * - Manejar Escape
 * - Cerrar al hacer clic fuera del modal
 * - Mostrar mensajes Toast
 *
 * ============================================================
 */

(function (window, document) {

    'use strict';


    /* ============================================================
       VARIABLES
       ============================================================ */

    let postulacionActual = null;
    let estadoSeleccionado = null;
    let toastTimer = null;


    /* ============================================================
       OBTENER ELEMENTO
       ============================================================ */

    function obtenerElemento(id) {

        return document.getElementById(id);

    }


    /* ============================================================
       OBTENER INICIALES
       ============================================================ */

    function obtenerIniciales(nombre) {

        nombre = String(nombre || '').trim();

        if (!nombre) {

            return '?';

        }

        const partes = nombre.split(/\s+/);

        let resultado =
            partes[0].charAt(0);

        if (partes.length > 1) {

            resultado += partes[
                partes.length - 1
            ].charAt(0);

        }

        return resultado.toUpperCase();

    }


    /* ============================================================
       FORMATEAR FECHA
       ============================================================ */

    function formatearFechaJS(fecha) {

        if (!fecha) {

            return 'Sin fecha';

        }

        const valor = String(fecha);

        const partes = valor.split(' ');

        if (partes.length < 2) {

            return valor;

        }

        const fechaPartes =
            partes[0].split('-');

        if (fechaPartes.length !== 3) {

            return valor;

        }

        const hora = partes[1]
            ? partes[1].substring(0, 5)
            : '';

        return (
            fechaPartes[2] +
            '/' +
            fechaPartes[1] +
            '/' +
            fechaPartes[0] +
            (hora ? ' ' + hora : '')
        );

    }


    /* ============================================================
       OBTENER NOMBRE DEL ARCHIVO
       ============================================================ */

    function obtenerNombreArchivo(ruta) {

        if (!ruta) {

            return '';

        }

        const partes = String(ruta)
            .replace(/\\/g, '/')
            .split('/');

        return partes[
            partes.length - 1
        ] || '';

    }


    /* ============================================================
       OBTENER RUTA DEL CV
       ============================================================ */

    function obtenerRutaCV(ruta) {

        if (!ruta) {

            return '#';

        }

        ruta = String(ruta)
            .replace(/\\/g, '/');

        if (
            ruta.indexOf('http://') === 0 ||
            ruta.indexOf('https://') === 0
        ) {

            return ruta;

        }

        if (ruta.indexOf('/') === 0) {

            return ruta;

        }

        if (ruta.indexOf('backend/') === 0) {

            return '../' + ruta;

        }

        return '../../../../../' + ruta;

    }


    /* ============================================================
       CARGAR POSTULACIÓN
       ============================================================
       
       Esta función es utilizada directamente por el botón
       "Ver detalle" del listado.
       
       Ejemplo:
       
       onclick="cargarPostulacion(15)"
       
       ============================================================ */

    function cargarPostulacion(id) {

        const postulacionId =
            parseInt(id, 10);

        if (
            !Number.isFinite(postulacionId) ||
            postulacionId <= 0
        ) {

            mostrarToast(
                'La postulación indicada no es válida.',
                true
            );

            return;

        }


        /* --------------------------------------------------------
           Verificar que exista la API
           -------------------------------------------------------- */

        if (
            !window.PostulacionesAPI ||
            typeof window.PostulacionesAPI.obtenerDetalle !==
                'function'
        ) {

            mostrarToast(
                'No fue posible cargar el detalle de la postulación.',
                true
            );

            return;

        }


        /* --------------------------------------------------------
           Obtener detalle actualizado desde la API
           -------------------------------------------------------- */

        window.PostulacionesAPI
            .obtenerDetalle(postulacionId)

            .then(function (respuesta) {

                if (
                    !respuesta ||
                    respuesta.ok === false
                ) {

                    throw new Error(
                        respuesta &&
                        respuesta.mensaje
                            ? respuesta.mensaje
                            : 'No fue posible obtener la información de la postulación.'
                    );

                }


                if (
                    !respuesta.datos ||
                    typeof respuesta.datos !== 'object'
                ) {

                    throw new Error(
                        'La respuesta del servidor no contiene los datos de la postulación.'
                    );

                }


                /*
                 * Abrimos el modal con los datos recién obtenidos.
                 *
                 * Esto es importante porque así cada vez que se
                 * vuelve a abrir una postulación se consulta el
                 * estado y comentario de seguimiento actuales.
                 */

                abrirPostulacion(
                    respuesta.datos
                );

            })

            .catch(function (error) {

                const mensaje =
                    error &&
                    error.message
                        ? error.message
                        : 'No fue posible cargar la postulación.';

                mostrarToast(
                    mensaje,
                    true
                );

            });

    }


    /* ============================================================
       ABRIR POSTULACIÓN
       ============================================================ */

    function abrirPostulacion(postulacion) {

        if (
            !postulacion ||
            typeof postulacion !== 'object'
        ) {

            return;

        }


        postulacionActual =
            postulacion;


        estadoSeleccionado =
            postulacion.estado ||
            'nueva';


        const modal =
            obtenerElemento(
                'modalPostulacion'
            );


        if (!modal) {

            return;

        }


        /*
         * --------------------------------------------------------
         * REACTIVAR BOTÓN DE GUARDAR
         * --------------------------------------------------------
         *
         * El botón se deshabilita durante el guardado.
         * Como el modal se reutiliza, aquí lo habilitamos
         * nuevamente cada vez que se abre.
         */

        const botonesGuardar =
            document.querySelectorAll(
                '.patch-footer-button.primary, .patch-modal-footer-button.primary'
            );


        botonesGuardar.forEach(
            function (boton) {

                boton.disabled = false;

            }
        );


        const nombre =
            postulacion.nombre_completo ||
            'Sin nombre';


        const correo =
            postulacion.correo ||
            'Sin correo';


        const telefono =
            postulacion.telefono ||
            'Sin teléfono';


        const vacante =
            postulacion.vacante_titulo ||
            'CV General / Postulación espontánea';


        const ubicacion =
            postulacion.vacante_ubicacion ||
            'No especificada';


        const modalidad =
            postulacion.vacante_modalidad ||
            'No especificada';


        /* ========================================================
           PERFIL
           ======================================================== */

        const modalAvatar =
            obtenerElemento(
                'modalAvatar'
            );


        if (modalAvatar) {

            modalAvatar.textContent =
                obtenerIniciales(
                    nombre
                );

        }


        const modalNombre =
            obtenerElemento(
                'modalNombre'
            );


        if (modalNombre) {

            modalNombre.textContent =
                nombre;

        }


        const modalCorreo =
            obtenerElemento(
                'modalCorreo'
            );


        if (modalCorreo) {

            modalCorreo.textContent =
                correo;

        }


        /* ========================================================
           DATOS DE CONTACTO
           ======================================================== */

        const detalleCorreo =
            obtenerElemento(
                'detalleCorreo'
            );


        if (detalleCorreo) {

            detalleCorreo.textContent =
                correo;

        }


        const detalleTelefono =
            obtenerElemento(
                'detalleTelefono'
            );


        if (detalleTelefono) {

            detalleTelefono.textContent =
                telefono;

        }


        /* ========================================================
           DATOS DE LA VACANTE
           ======================================================== */

        const detalleVacante =
            obtenerElemento(
                'detalleVacante'
            );


        if (detalleVacante) {

            detalleVacante.textContent =
                vacante;

        }


        const detalleFecha =
            obtenerElemento(
                'detalleFecha'
            );


        if (detalleFecha) {

            detalleFecha.textContent =
                formatearFechaJS(
                    postulacion.fecha_postulacion
                );

        }


        const detalleUbicacion =
            obtenerElemento(
                'detalleUbicacion'
            );


        if (detalleUbicacion) {

            detalleUbicacion.textContent =
                ubicacion;

        }


        const detalleModalidad =
            obtenerElemento(
                'detalleModalidad'
            );


        if (detalleModalidad) {

            detalleModalidad.textContent =
                modalidad;

        }


        /* ========================================================
           COMENTARIOS DEL CANDIDATO
           ======================================================== */

        const comentarios =
            obtenerElemento(
                'detalleComentarios'
            );


        if (comentarios) {

            const textoComentarios =
                String(
                    postulacion.comentarios ||
                    ''
                ).trim();


            if (
                textoComentarios !== ''
            ) {

                comentarios.textContent =
                    textoComentarios;

                comentarios.classList.remove(
                    'empty'
                );

            } else {

                comentarios.textContent =
                    'El candidato no agregó comentarios.';

                comentarios.classList.add(
                    'empty'
                );

            }

        }


        /* ========================================================
           COMENTARIO DE SEGUIMIENTO
           ======================================================== */

        const comentarioSeguimiento =
            obtenerElemento(
                'comentarioSeguimiento'
            );


        if (comentarioSeguimiento) {

            comentarioSeguimiento.value =
                String(
                    postulacion.comentario_seguimiento ||
                    ''
                );

        }


        /* ========================================================
           CV
           ======================================================== */

        const cvNombre =
            obtenerNombreArchivo(
                postulacion.cv
            );


        const detalleCVNombre =
            obtenerElemento(
                'detalleCVNombre'
            );


        if (detalleCVNombre) {

            detalleCVNombre.textContent =
                cvNombre ||
                'CV.pdf';

        }


        const btnCV =
            obtenerElemento(
                'btnVerCV'
            );


        if (btnCV) {

            if (postulacion.cv) {

                btnCV.href =
                    obtenerRutaCV(
                        postulacion.cv
                    );

                btnCV.style.display =
                    'inline-flex';

            } else {

                btnCV.removeAttribute(
                    'href'
                );

                btnCV.style.display =
                    'none';

            }

        }


        /* ========================================================
           ESTADO
           ======================================================== */

        actualizarSelectorEstado();


        /* ========================================================
           MOSTRAR MODAL
           ======================================================== */

        modal.classList.add(
            'open'
        );


        modal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.style.overflow =
            'hidden';


        /* ========================================================
           FOCO
           ======================================================== */

        const botonCerrar =
            modal.querySelector(
                '.patch-modal-close'
            );


        if (botonCerrar) {

            setTimeout(
                function () {

                    botonCerrar.focus();

                },
                50
            );

        }

    }


    /* ============================================================
       CERRAR POSTULACIÓN
       ============================================================ */

    function cerrarPostulacion() {

        const modal =
            obtenerElemento(
                'modalPostulacion'
            );


        if (!modal) {

            return;

        }


        modal.classList.remove(
            'open'
        );


        modal.setAttribute(
            'aria-hidden',
            'true'
        );


        document.body.style.overflow =
            '';


        postulacionActual =
            null;


        estadoSeleccionado =
            null;

    }


    /* ============================================================
       SELECCIONAR ESTADO
       ============================================================ */

    function seleccionarEstado(button) {

        if (!button) {

            return;

        }


        const estado =
            button.dataset.estado ||
            'nueva';


        estadoSeleccionado =
            estado;


        actualizarSelectorEstado();

    }


    /* ============================================================
       ACTUALIZAR SELECTOR DE ESTADO
       ============================================================ */

    function actualizarSelectorEstado() {

        document
            .querySelectorAll(
                '.patch-status-option'
            )
            .forEach(
                function (button) {

                    button.classList.toggle(
                        'active',
                        button.dataset.estado ===
                        estadoSeleccionado
                    );

                }
            );

    }


    /* ============================================================
       GUARDAR ESTADO Y COMENTARIO
       ============================================================ */

    function guardarEstado() {

        if (!postulacionActual) {

            return;

        }


        if (!estadoSeleccionado) {

            estadoSeleccionado =
                'nueva';

        }


        const id =
            parseInt(
                postulacionActual.id,
                10
            );


        if (
            !Number.isFinite(id) ||
            id <= 0
        ) {

            mostrarToast(
                'La postulación indicada no es válida.',
                true
            );

            return;

        }


        /* ========================================================
           OBTENER COMENTARIO DE SEGUIMIENTO
           ======================================================== */

        const campoComentario =
            obtenerElemento(
                'comentarioSeguimiento'
            );


        const comentarioSeguimiento =
            campoComentario
                ? String(
                    campoComentario.value ||
                    ''
                ).trim()
                : '';


        /* ========================================================
           API AJAX
           ======================================================== */

        if (
            window.PostulacionesAPI &&
            typeof window.PostulacionesAPI.actualizarEstado ===
                'function'
        ) {

            const botones =
                document.querySelectorAll(
                    '.patch-footer-button.primary, .patch-modal-footer-button.primary'
                );


            /*
             * Deshabilitar mientras se procesa
             * la petición para evitar dobles envíos.
             */

            botones.forEach(
                function (boton) {

                    boton.disabled = true;

                }
            );


            window.PostulacionesAPI
                .actualizarEstado(
                    id,
                    estadoSeleccionado,
                    comentarioSeguimiento
                )

                .then(
                    function (respuesta) {

                        if (
                            !respuesta ||
                            respuesta.ok === false
                        ) {

                            throw new Error(
                                respuesta &&
                                respuesta.mensaje
                                    ? respuesta.mensaje
                                    : 'No fue posible actualizar la postulación.'
                            );

                        }


                        /*
                         * ------------------------------------------------
                         * ACTUALIZAR OBJETO LOCAL
                         * ------------------------------------------------
                         */

                        postulacionActual.estado =
                            estadoSeleccionado;


                        postulacionActual.comentario_seguimiento =
                            comentarioSeguimiento;


                        /*
                         * Si la API devuelve los datos actualizados,
                         * aprovechamos la respuesta.
                         */

                        if (
                            respuesta.datos &&
                            typeof respuesta.datos ===
                                'object'
                        ) {

                            if (
                                respuesta.datos.estado
                            ) {

                                postulacionActual.estado =
                                    respuesta.datos.estado;

                                estadoSeleccionado =
                                    respuesta.datos.estado;

                            }


                            if (
                                Object.prototype.hasOwnProperty.call(
                                    respuesta.datos,
                                    'comentario_seguimiento'
                                )
                            ) {

                                postulacionActual.comentario_seguimiento =
                                    String(
                                        respuesta.datos.comentario_seguimiento ||
                                        ''
                                    );

                            }

                        }


                        /*
                         * ------------------------------------------------
                         * ACTUALIZAR FILA DEL LISTADO
                         * ------------------------------------------------
                         */

                        actualizarEstadoEnListado(
                            id,
                            estadoSeleccionado
                        );


                        /*
                         * ------------------------------------------------
                         * REACTIVAR BOTÓN
                         * ------------------------------------------------
                         */

                        botones.forEach(
                            function (boton) {

                                boton.disabled =
                                    false;

                            }
                        );


                        /*
                         * ------------------------------------------------
                         * MENSAJE
                         * ------------------------------------------------
                         */

                        mostrarToast(
                            respuesta.mensaje ||
                            'Cambios guardados correctamente.',
                            false
                        );


                        /*
                         * ------------------------------------------------
                         * CERRAR MODAL
                         * ------------------------------------------------
                         */

                        setTimeout(
                            function () {

                                cerrarPostulacion();

                            },
                            500
                        );

                    }
                )

                .catch(
                    function (error) {

                        const mensaje =
                            error &&
                            error.message
                                ? error.message
                                : 'No fue posible actualizar la postulación.';


                        /*
                         * Reactivar botón si ocurrió
                         * algún error.
                         */

                        botones.forEach(
                            function (boton) {

                                boton.disabled =
                                    false;

                            }
                        );


                        mostrarToast(
                            mensaje,
                            true
                        );

                    }
                );


            return;

        }


        /* ========================================================
           COMPATIBILIDAD CON FORMULARIO POST
           ======================================================== */

        const inputId =
            obtenerElemento(
                'estadoPostulacionId'
            );


        const inputEstado =
            obtenerElemento(
                'estadoPostulacionValor'
            );


        const formulario =
            obtenerElemento(
                'formActualizarEstado'
            );


        if (
            !inputId ||
            !inputEstado ||
            !formulario
        ) {

            mostrarToast(
                'No fue posible preparar la actualización del estado.',
                true
            );

            return;

        }


        inputId.value =
            String(id);


        inputEstado.value =
            estadoSeleccionado;


        /*
         * Si existe el campo de comentario en el
         * formulario oculto, también lo actualizamos.
         */

        const inputComentario =
            formulario.querySelector(
                '[name="comentario_seguimiento"]'
            );


        if (inputComentario) {

            inputComentario.value =
                comentarioSeguimiento;

        }


        formulario.submit();

    }


    /* ============================================================
       ACTUALIZAR ESTADO EN EL LISTADO
       ============================================================ */

    function actualizarEstadoEnListado(
        id,
        estado
    ) {

        const fila =
            document.querySelector(
                'tr[data-postulacion-id="' +
                id +
                '"]'
            );


        if (!fila) {

            console.warn(
                'No se encontró la fila de la postulación:',
                id
            );

            return;

        }


        const estados = {

            nueva: {
                texto: 'Por revisar',
                clase: 'review'
            },

            en_proceso: {
                texto: 'En proceso',
                clase: 'process'
            },

            entrevista: {
                texto: 'Entrevista',
                clase: 'interview'
            },

            aceptado: {
                texto: 'Aceptado',
                clase: 'accepted'
            },

            descartado: {
                texto: 'Descartado',
                clase: 'rejected'
            }

        };


        const estadoActual =
            estados[estado] ||
            {
                texto: estado,
                clase: ''
            };


        /* --------------------------------------------------------
           ACTUALIZAR DATA
           -------------------------------------------------------- */

        fila.dataset.estado =
            estado;


        /* --------------------------------------------------------
           ACTUALIZAR TEXTO Y CLASE
           -------------------------------------------------------- */

        const estadoElemento =
            fila.querySelector(
                '.patch-status'
            );


        if (!estadoElemento) {

            console.warn(
                'No se encontró el elemento visual del estado para la postulación:',
                id
            );

            return;

        }


        estadoElemento.classList.remove(
            'review',
            'process',
            'interview',
            'accepted',
            'rejected'
        );


        if (estadoActual.clase) {

            estadoElemento.classList.add(
                estadoActual.clase
            );

        }


        estadoElemento.dataset.estado =
            estado;


        estadoElemento.textContent =
            estadoActual.texto;

    }


    /* ============================================================
       MOSTRAR TOAST
       ============================================================ */

    function mostrarToast(
        mensaje,
        error = false
    ) {

        const toast =
            obtenerElemento(
                'patchToast'
            );


        if (!toast) {

            return;

        }


        clearTimeout(
            toastTimer
        );


        toast.textContent =
            String(
                mensaje || ''
            );


        toast.classList.toggle(
            'error',
            Boolean(error)
        );


        toast.classList.add(
            'show'
        );


        toastTimer =
            setTimeout(
                function () {

                    toast.classList.remove(
                        'show'
                    );

                },
                3500
            );

    }


    /* ============================================================
       CERRAR CON ESCAPE
       ============================================================ */

    function manejarTecla(event) {

        if (
            event.key === 'Escape' &&
            postulacionActual
        ) {

            cerrarPostulacion();

        }

    }


    /* ============================================================
       CERRAR AL HACER CLIC FUERA
       ============================================================ */

    function manejarClickModal(event) {

        const modal =
            obtenerElemento(
                'modalPostulacion'
            );


        if (!modal) {

            return;

        }


        if (
            event.target === modal
        ) {

            cerrarPostulacion();

        }

    }


    /* ============================================================
       INICIALIZACIÓN
       ============================================================ */

    function inicializarModal() {

        const modal =
            obtenerElemento(
                'modalPostulacion'
            );


        document.addEventListener(
            'keydown',
            manejarTecla
        );


        if (modal) {

            modal.addEventListener(
                'click',
                manejarClickModal
            );

        }

    }


    /* ============================================================
       EXPONER FUNCIONES GLOBALMENTE
       ============================================================ */

    window.cargarPostulacion =
        cargarPostulacion;


    window.abrirPostulacion =
        abrirPostulacion;


    window.cerrarPostulacion =
        cerrarPostulacion;


    window.seleccionarEstado =
        seleccionarEstado;


    window.actualizarSelectorEstado =
        actualizarSelectorEstado;


    window.guardarEstado =
        guardarEstado;


    window.mostrarToast =
        mostrarToast;


    window.obtenerIniciales =
        obtenerIniciales;


    window.formatearFechaJS =
        formatearFechaJS;


    window.obtenerNombreArchivo =
        obtenerNombreArchivo;


    window.obtenerRutaCV =
        obtenerRutaCV;


    /* ============================================================
       ARRANQUE
       ============================================================ */

    if (
        document.readyState ===
        'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            inicializarModal
        );

    } else {

        inicializarModal();

    }


})(window, document);
