
/**
 * ============================================================
 * MODAL DE POSTULACIÓN
 * Patch_BolsaTrabajo
 * ============================================================
 *
 * Funciones:
 * - Cargar detalle de postulación mediante API
 * - Abrir y cerrar el modal
 * - Mostrar información del candidato y su CV
 * - Seleccionar y guardar el estado
 * - Guardar comentario de seguimiento
 * - Actualizar el listado
 * - Manejar Escape y clic fuera del modal
 * - Mostrar mensajes Toast compatibles con las clases
 *   específicas de Admin_Detalle_Postulacion.php
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
        let resultado = partes[0].charAt(0);

        if (partes.length > 1) {
            resultado += partes[partes.length - 1].charAt(0);
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

        const fechaPartes = partes[0].split('-');

        if (fechaPartes.length !== 3) {
            return valor;
        }

        const hora = partes[1] ? partes[1].substring(0, 5) : '';

        return (
            fechaPartes[2] + '/' +
            fechaPartes[1] + '/' +
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

        return partes[partes.length - 1] || '';
    }

    /* ============================================================
       OBTENER RUTA DEL CV
       ============================================================ */
    function obtenerRutaCV(ruta) {
        if (!ruta) {
            return '#';
        }

        ruta = String(ruta).replace(/\\/g, '/');

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
       MOSTRAR TOAST
       Compatible con clases específicas y genéricas.
       ============================================================ */
    
    function mostrarToast(mensaje, error = false) {
        const toast = obtenerElemento('patchToast');

        if (!toast) {
            console.warn('No se encontró el elemento #patchToast.');
            return;
        }

        if (toastTimer !== null) {
            clearTimeout(toastTimer);
            toastTimer = null;
        }

        /*
        * Detectar la vista mediante la clase del contenedor.
        * No modificar las clases originales del toast de detalle.
        */
        const esDetalle = toast.classList.contains(
            'patch-toast-detalle_postulacion'
        );

        const claseIcono = esDetalle
            ? 'patch-toast-icon-detalle_postulacion'
            : 'patch-toast-icon';

        const claseContenido = esDetalle
            ? 'patch-toast-content-detalle_postulacion'
            : 'patch-toast-content';

        /*
        * Obtener o crear el icono correspondiente a la vista.
        */
        let icono = toast.querySelector('.' + claseIcono);

        if (!icono) {
            icono = document.createElement('div');
            icono.className = claseIcono;
            toast.prepend(icono);
        }

        /*
        * Obtener o crear el contenido correspondiente a la vista.
        */
        let contenido = toast.querySelector('.' + claseContenido);

        if (!contenido) {
            contenido = document.createElement('div');
            contenido.className = claseContenido;
            toast.appendChild(contenido);
        }

        /*
        * Crear o recuperar título y mensaje sin reemplazar
        * las clases específicas de cada vista.
        */
        let titulo = contenido.querySelector('strong');

        if (!titulo) {
            titulo = document.createElement('strong');
            contenido.appendChild(titulo);
        }

        let texto = contenido.querySelector('span');

        if (!texto) {
            texto = document.createElement('span');
            contenido.appendChild(texto);
        }

        /*
        * Actualizar icono, título y mensaje.
        */
        icono.innerHTML = error
            ? '<i class="fa fa-xmark" aria-hidden="true"></i>'
            : '<i class="fa fa-check" aria-hidden="true"></i>';

        titulo.textContent = error
            ? 'No se pudo realizar el cambio'
            : (esDetalle
                ? 'Cambio realizado exitosamente'
                : '¡Cambio realizado!');

        texto.textContent = String(
            mensaje || (
                error
                    ? 'No fue posible completar la operación.'
                    : 'El estado de la postulación se actualizó correctamente.'
            )
        );

        /*
        * Restablecer las clases de estado.
        * En la vista de detalle se conserva su clase base original.
        */
        toast.classList.remove('show', 'success', 'error');

        void toast.offsetWidth;

        toast.classList.add(error ? 'error' : 'success');
        toast.classList.add('show');

        /*
        * Ocultar la notificación después de 3.5 segundos.
        */
        toastTimer = setTimeout(function () {
            toast.classList.remove('show');
            toastTimer = null;
        }, 3500);
    }

    /* ============================================================
       CARGAR POSTULACIÓN
       ============================================================ */
    function cargarPostulacion(id) {
        const postulacionId = parseInt(id, 10);

        if (!Number.isFinite(postulacionId) || postulacionId <= 0) {
            mostrarToast('La postulación indicada no es válida.', true);
            return;
        }

        if (
            !window.PostulacionesAPI ||
            typeof window.PostulacionesAPI.obtenerDetalle !== 'function'
        ) {
            mostrarToast(
                'No fue posible cargar el detalle de la postulación.',
                true
            );
            return;
        }

        window.PostulacionesAPI
            .obtenerDetalle(postulacionId)
            .then(function (respuesta) {
                if (!respuesta || respuesta.ok === false) {
                    throw new Error(
                        respuesta && respuesta.mensaje
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

                abrirPostulacion(respuesta.datos);
            })
            .catch(function (error) {
                mostrarToast(
                    error && error.message
                        ? error.message
                        : 'No fue posible cargar la postulación.',
                    true
                );
            });
    }

    /* ============================================================
       ABRIR POSTULACIÓN
       ============================================================ */
    function abrirPostulacion(postulacion) {
        if (!postulacion || typeof postulacion !== 'object') {
            return;
        }

        postulacionActual = postulacion;
        estadoSeleccionado = postulacion.estado || 'nueva';

        const modal = obtenerElemento('modalPostulacion');

        if (!modal) {
            return;
        }

        document.querySelectorAll(
            '.patch-footer-button.primary, .patch-modal-footer-button.primary'
        ).forEach(function (boton) {
            boton.disabled = false;
        });

        const nombre = postulacion.nombre_completo || 'Sin nombre';
        const correo = postulacion.correo || 'Sin correo';
        const telefono = postulacion.telefono || 'Sin teléfono';
        const vacante =
            postulacion.vacante_titulo ||
            'CV General / Postulación espontánea';
        const ubicacion =
            postulacion.vacante_ubicacion || 'No especificada';
        const modalidad =
            postulacion.vacante_modalidad || 'No especificada';

        const modalAvatar = obtenerElemento('modalAvatar');

        if (modalAvatar) {
            modalAvatar.textContent = obtenerIniciales(nombre);
        }

        const modalNombre = obtenerElemento('modalNombre');

        if (modalNombre) {
            modalNombre.textContent = nombre;
        }

        const modalCorreo = obtenerElemento('modalCorreo');

        if (modalCorreo) {
            modalCorreo.textContent = correo;
        }

        const detalleCorreo = obtenerElemento('detalleCorreo');

        if (detalleCorreo) {
            detalleCorreo.textContent = correo;
        }

        const detalleTelefono = obtenerElemento('detalleTelefono');

        if (detalleTelefono) {
            detalleTelefono.textContent = telefono;
        }

        const detalleVacante = obtenerElemento('detalleVacante');

        if (detalleVacante) {
            detalleVacante.textContent = vacante;
        }

        const detalleFecha = obtenerElemento('detalleFecha');

        if (detalleFecha) {
            detalleFecha.textContent = formatearFechaJS(
                postulacion.fecha_postulacion
            );
        }

        const detalleUbicacion = obtenerElemento('detalleUbicacion');

        if (detalleUbicacion) {
            detalleUbicacion.textContent = ubicacion;
        }

        const detalleModalidad = obtenerElemento('detalleModalidad');

        if (detalleModalidad) {
            detalleModalidad.textContent = modalidad;
        }

        const comentarios = obtenerElemento('detalleComentarios');

        if (comentarios) {
            const textoComentarios = String(
                postulacion.comentarios || ''
            ).trim();

            if (textoComentarios !== '') {
                comentarios.textContent = textoComentarios;
                comentarios.classList.remove('empty');
            } else {
                comentarios.textContent =
                    'El candidato no agregó comentarios.';
                comentarios.classList.add('empty');
            }
        }

        const comentarioSeguimiento = obtenerElemento(
            'comentarioSeguimiento'
        );

        if (comentarioSeguimiento) {
            comentarioSeguimiento.value = String(
                postulacion.comentario_seguimiento || ''
            );
        }

        const cvNombre = obtenerNombreArchivo(postulacion.cv);
        const detalleCVNombre = obtenerElemento('detalleCVNombre');

        if (detalleCVNombre) {
            detalleCVNombre.textContent = cvNombre || 'CV.pdf';
        }

        const btnCV = obtenerElemento('btnVerCV');

        if (btnCV) {
            if (postulacion.cv) {
                btnCV.href = obtenerRutaCV(postulacion.cv);
                btnCV.style.display = 'inline-flex';
            } else {
                btnCV.removeAttribute('href');
                btnCV.style.display = 'none';
            }
        }

        actualizarSelectorEstado();

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const botonCerrar = modal.querySelector('.patch-modal-close');

        if (botonCerrar) {
            setTimeout(function () {
                botonCerrar.focus();
            }, 50);
        }
    }

    /* ============================================================
       CERRAR POSTULACIÓN
       ============================================================ */
    function cerrarPostulacion() {
        const modal = obtenerElemento('modalPostulacion');

        if (!modal) {
            return;
        }

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        postulacionActual = null;
        estadoSeleccionado = null;
    }

    /* ============================================================
       SELECCIONAR ESTADO
       ============================================================ */
    function seleccionarEstado(button) {
        if (!button) {
            return;
        }

        estadoSeleccionado = button.dataset.estado || 'nueva';
        actualizarSelectorEstado();
    }

    /* ============================================================
       ACTUALIZAR SELECTOR DE ESTADO
       ============================================================ */
    function actualizarSelectorEstado() {
        document.querySelectorAll('.patch-status-option').forEach(
            function (button) {
                button.classList.toggle(
                    'active',
                    button.dataset.estado === estadoSeleccionado
                );
            }
        );
    }

    /* ============================================================
       ACTUALIZAR ESTADO EN EL LISTADO
       ============================================================ */
    function actualizarEstadoEnListado(id, estado) {
        const fila = document.querySelector(
            'tr[data-postulacion-id="' + id + '"]'
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

        const estadoActual = estados[estado] || {
            texto: estado,
            clase: ''
        };

        fila.dataset.estado = estado;

        const estadoElemento = fila.querySelector('.patch-status');

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
            estadoElemento.classList.add(estadoActual.clase);
        }

        estadoElemento.dataset.estado = estado;
        estadoElemento.textContent = estadoActual.texto;
    }

    /* ============================================================
       GUARDAR ESTADO Y COMENTARIO
       ============================================================ */
    function guardarEstado() {
        if (!postulacionActual) {
            return;
        }

        if (!estadoSeleccionado) {
            estadoSeleccionado = 'nueva';
        }

        const id = parseInt(postulacionActual.id, 10);

        if (!Number.isFinite(id) || id <= 0) {
            mostrarToast('La postulación indicada no es válida.', true);
            return;
        }

        const campoComentario = obtenerElemento('comentarioSeguimiento');

        const comentarioSeguimiento = campoComentario
            ? String(campoComentario.value || '').trim()
            : '';

        if (
            window.PostulacionesAPI &&
            typeof window.PostulacionesAPI.actualizarEstado === 'function'
        ) {
            const botones = document.querySelectorAll(
                '.patch-footer-button.primary, .patch-modal-footer-button.primary'
            );

            botones.forEach(function (boton) {
                boton.disabled = true;
            });

            window.PostulacionesAPI
                .actualizarEstado(
                    id,
                    estadoSeleccionado,
                    comentarioSeguimiento
                )
                .then(function (respuesta) {
                    if (!respuesta || respuesta.ok === false) {
                        throw new Error(
                            respuesta && respuesta.mensaje
                                ? respuesta.mensaje
                                : 'No fue posible actualizar la postulación.'
                        );
                    }

                    postulacionActual.estado = estadoSeleccionado;
                    postulacionActual.comentario_seguimiento =
                        comentarioSeguimiento;

                    if (
                        respuesta.datos &&
                        typeof respuesta.datos === 'object'
                    ) {
                        if (respuesta.datos.estado) {
                            postulacionActual.estado =
                                respuesta.datos.estado;
                            estadoSeleccionado = respuesta.datos.estado;
                        }

                        if (
                            Object.prototype.hasOwnProperty.call(
                                respuesta.datos,
                                'comentario_seguimiento'
                            )
                        ) {
                            postulacionActual.comentario_seguimiento =
                                String(
                                    respuesta.datos.comentario_seguimiento || ''
                                );
                        }
                    }

                    actualizarEstadoEnListado(
                        id,
                        estadoSeleccionado
                    );

                    botones.forEach(function (boton) {
                        boton.disabled = false;
                    });

                    mostrarToast(
                        respuesta.mensaje ||
                        'Cambios guardados correctamente.',
                        false
                    );

                    setTimeout(function () {
                        cerrarPostulacion();
                    }, 500);
                })
                .catch(function (error) {
                    botones.forEach(function (boton) {
                        boton.disabled = false;
                    });

                    mostrarToast(
                        error && error.message
                            ? error.message
                            : 'No fue posible actualizar la postulación.',
                        true
                    );
                });

            return;
        }

        /* ========================================================
           COMPATIBILIDAD CON FORMULARIO POST
           ======================================================== */
        const inputId = obtenerElemento('estadoPostulacionId');
        const inputEstado = obtenerElemento('estadoPostulacionValor');
        const formulario = obtenerElemento('formActualizarEstado');

        if (!inputId || !inputEstado || !formulario) {
            mostrarToast(
                'No fue posible preparar la actualización del estado.',
                true
            );
            return;
        }

        inputId.value = String(id);
        inputEstado.value = estadoSeleccionado;

        const inputComentario = formulario.querySelector(
            '[name="comentario_seguimiento"]'
        );

        if (inputComentario) {
            inputComentario.value = comentarioSeguimiento;
        }

        formulario.submit();
    }

    /* ============================================================
       CERRAR CON ESCAPE
       ============================================================ */
    function manejarTecla(event) {
        if (event.key === 'Escape' && postulacionActual) {
            cerrarPostulacion();
        }
    }

    /* ============================================================
       CERRAR AL HACER CLIC FUERA
       ============================================================ */
    function manejarClickModal(event) {
        const modal = obtenerElemento('modalPostulacion');

        if (!modal) {
            return;
        }

        if (event.target === modal) {
            cerrarPostulacion();
        }
    }

    /* ============================================================
       INICIALIZACIÓN
       ============================================================ */
    function inicializarModal() {
        const modal = obtenerElemento('modalPostulacion');

        document.addEventListener('keydown', manejarTecla);

        if (modal) {
            modal.addEventListener('click', manejarClickModal);
        }
    }

    /* ============================================================
       EXPONER FUNCIONES GLOBALMENTE
       ============================================================ */
    window.cargarPostulacion = cargarPostulacion;
    window.abrirPostulacion = abrirPostulacion;
    window.cerrarPostulacion = cerrarPostulacion;
    window.seleccionarEstado = seleccionarEstado;
    window.actualizarSelectorEstado = actualizarSelectorEstado;
    window.guardarEstado = guardarEstado;
    window.mostrarToast = mostrarToast;
    window.obtenerIniciales = obtenerIniciales;
    window.formatearFechaJS = formatearFechaJS;
    window.obtenerNombreArchivo = obtenerNombreArchivo;
    window.obtenerRutaCV = obtenerRutaCV;

    /* ============================================================
       ARRANQUE
       ============================================================ */
    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            inicializarModal
        );
    } else {
        inicializarModal();
    }

})(window, document);