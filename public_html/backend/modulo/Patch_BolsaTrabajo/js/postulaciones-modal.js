/**

* ============================================================
* MODAL DE POSTULACIÓN
* Patch_BolsaTrabajo
* ============================================================
*
* Funciones:
* * Abrir detalle de postulación
* * Cerrar modal
* * Mostrar información del candidato
* * Mostrar CV
* * Seleccionar estado
* * Guardar estado
* * Manejar Escape
* * Cerrar al hacer clic fuera del modal
* * Mostrar mensajes Toast
* ============================================================
  */

(function (window, document) {

'use strict';


/**
 * ============================================================
 * VARIABLES
 * ============================================================
 */

let postulacionActual = null;

let estadoSeleccionado = null;

let toastTimer = null;


/**
 * ============================================================
 * OBTENER ELEMENTO
 * ============================================================
 */

function obtenerElemento(id) {

    return document.getElementById(id);

}


/**
 * ============================================================
 * OBTENER INICIALES
 * ============================================================
 */

function obtenerIniciales(nombre) {

    nombre = String(nombre || '').trim();


    if (!nombre) {
        return '?';
    }


    const partes = nombre.split(/\s+/);

    let resultado = partes[0].charAt(0);


    if (partes.length > 1) {

        resultado += partes[
            partes.length - 1
        ].charAt(0);

    }


    return resultado.toUpperCase();

}


/**
 * ============================================================
 * FORMATEAR FECHA
 * ============================================================
 */

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


/**
 * ============================================================
 * OBTENER NOMBRE DEL ARCHIVO
 * ============================================================
 */

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


/**
 * ============================================================
 * OBTENER RUTA DEL CV
 * ============================================================
 */

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


/**
 * ============================================================
 * ABRIR POSTULACIÓN
 * ============================================================
 */

function abrirPostulacion(postulacion) {

    if (!postulacion || typeof postulacion !== 'object') {
        return;
    }


    postulacionActual = postulacion;

    estadoSeleccionado =
        postulacion.estado || 'nueva';


    const modal =
        obtenerElemento('modalPostulacion');


    if (!modal) {
        return;
    }


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


    /**
     * --------------------------------------------------------
     * PERFIL
     * --------------------------------------------------------
     */

    const modalAvatar =
        obtenerElemento('modalAvatar');


    if (modalAvatar) {

        modalAvatar.textContent =
            obtenerIniciales(nombre);

    }


    const modalNombre =
        obtenerElemento('modalNombre');


    if (modalNombre) {

        modalNombre.textContent =
            nombre;

    }


    const modalCorreo =
        obtenerElemento('modalCorreo');


    if (modalCorreo) {

        modalCorreo.textContent =
            correo;

    }


    /**
     * --------------------------------------------------------
     * DATOS DE CONTACTO
     * --------------------------------------------------------
     */

    const detalleCorreo =
        obtenerElemento('detalleCorreo');


    if (detalleCorreo) {

        detalleCorreo.textContent =
            correo;

    }


    const detalleTelefono =
        obtenerElemento('detalleTelefono');


    if (detalleTelefono) {

        detalleTelefono.textContent =
            telefono;

    }


    /**
     * --------------------------------------------------------
     * DATOS DE LA VACANTE
     * --------------------------------------------------------
     */

    const detalleVacante =
        obtenerElemento('detalleVacante');


    if (detalleVacante) {

        detalleVacante.textContent =
            vacante;

    }


    const detalleFecha =
        obtenerElemento('detalleFecha');


    if (detalleFecha) {

        detalleFecha.textContent =
            formatearFechaJS(
                postulacion.fecha_postulacion
            );

    }


    const detalleUbicacion =
        obtenerElemento('detalleUbicacion');


    if (detalleUbicacion) {

        detalleUbicacion.textContent =
            ubicacion;

    }


    const detalleModalidad =
        obtenerElemento('detalleModalidad');


    if (detalleModalidad) {

        detalleModalidad.textContent =
            modalidad;

    }


    /**
     * --------------------------------------------------------
     * COMENTARIOS
     * --------------------------------------------------------
     */

    const comentarios =
        obtenerElemento('detalleComentarios');


    if (comentarios) {

        const textoComentarios =
            String(
                postulacion.comentarios || ''
            ).trim();


        if (textoComentarios !== '') {

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


    /**
     * --------------------------------------------------------
     * CV
     * --------------------------------------------------------
     */

    const cvNombre =
        obtenerNombreArchivo(
            postulacion.cv
        );


    const detalleCVNombre =
        obtenerElemento('detalleCVNombre');


    if (detalleCVNombre) {

        detalleCVNombre.textContent =
            cvNombre || 'CV.pdf';

    }


    const btnCV =
        obtenerElemento('btnVerCV');


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


    /**
     * --------------------------------------------------------
     * ESTADO
     * --------------------------------------------------------
     */

    actualizarSelectorEstado();


    /**
     * --------------------------------------------------------
     * MOSTRAR MODAL
     * --------------------------------------------------------
     */

    modal.classList.add('open');

    modal.setAttribute(
        'aria-hidden',
        'false'
    );


    document.body.style.overflow =
        'hidden';


    /*
     * Colocamos el foco en el botón de cierre
     * para mejorar la navegación mediante teclado.
     */

    const botonCerrar =
        modal.querySelector(
            '.patch-modal-close'
        );


    if (botonCerrar) {

        setTimeout(function () {

            botonCerrar.focus();

        }, 50);

    }

}


/**
 * ============================================================
 * CERRAR POSTULACIÓN
 * ============================================================
 */

function cerrarPostulacion() {

    const modal =
        obtenerElemento('modalPostulacion');


    if (!modal) {
        return;
    }


    modal.classList.remove('open');

    modal.setAttribute(
        'aria-hidden',
        'true'
    );


    document.body.style.overflow =
        '';


    postulacionActual = null;

    estadoSeleccionado = null;

}


/**
 * ============================================================
 * SELECCIONAR ESTADO
 * ============================================================
 */

function seleccionarEstado(button) {

    if (!button) {
        return;
    }


    const estado =
        button.dataset.estado || 'nueva';


    estadoSeleccionado =
        estado;


    actualizarSelectorEstado();

}


/**
 * ============================================================
 * ACTUALIZAR SELECTOR DE ESTADO
 * ============================================================
 */

function actualizarSelectorEstado() {

    document
        .querySelectorAll(
            '.patch-status-option'
        )
        .forEach(function (button) {

            button.classList.toggle(
                'active',
                button.dataset.estado ===
                estadoSeleccionado
            );

        });

}


/**
 * ============================================================
 * GUARDAR ESTADO
 * ============================================================
 */

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


    if (!Number.isFinite(id) || id <= 0) {

        mostrarToast(
            'La postulación indicada no es válida.',
            true
        );

        return;

    }


    /**
     * --------------------------------------------------------
     * Si existe la API AJAX, utilizamos la API.
     * --------------------------------------------------------
     */

    if (
        window.PostulacionesAPI &&
        typeof window.PostulacionesAPI.actualizarEstado ===
            'function'
    ) {

        const botones =
            document.querySelectorAll(
                '.patch-modal-footer-button.primary'
            );


        botones.forEach(function (boton) {

            boton.disabled = true;

        });


        window.PostulacionesAPI
            .actualizarEstado(
                id,
                estadoSeleccionado
            )
            .then(function (respuesta) {

                const mensaje =
                    respuesta &&
                    respuesta.mensaje
                        ? respuesta.mensaje
                        : 'El estado de la postulación se actualizó correctamente.';


                mostrarToast(
                    mensaje,
                    false
                );


                /*
                 * Actualizamos el objeto actual para que
                 * el estado mostrado localmente también
                 * quede sincronizado.
                 */

                postulacionActual.estado =
                    estadoSeleccionado;


                /*
                 * Actualizamos la fila correspondiente
                 * si existe en la página.
                 */

                actualizarEstadoEnListado(
                    id,
                    estadoSeleccionado
                );


                botones.forEach(function (boton) {

                    boton.disabled = false;

                });


                /*
                 * Cerramos el modal después de guardar.
                 */

                setTimeout(function () {

                    cerrarPostulacion();

                }, 500);

            })
            .catch(function (error) {

                const mensaje =
                    error &&
                    error.message
                        ? error.message
                        : 'No fue posible actualizar el estado de la postulación.';


                mostrarToast(
                    mensaje,
                    true
                );


                botones.forEach(function (boton) {

                    boton.disabled = false;

                });

            });


        return;

    }


    /**
     * --------------------------------------------------------
     * Compatibilidad con el formulario POST existente.
     * --------------------------------------------------------
     */

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


    formulario.submit();

}


/**
 * ============================================================
 * ACTUALIZAR ESTADO EN EL LISTADO
 * ============================================================
 */

function actualizarEstadoEnListado(
    id,
    estado
) {

    const filas =
        document.querySelectorAll(
            'tr'
        );


    filas.forEach(function (fila) {

        const botones =
            fila.querySelectorAll(
                '.patch-action-button'
            );


        if (!botones.length) {
            return;
        }


        const boton =
            botones[0];


        const onclick =
            boton.getAttribute(
                'onclick'
            );


        if (
            !onclick ||
            onclick.indexOf(
                'id'
            ) === -1
        ) {
            return;
        }


        /*
         * El listado se vuelve a cargar normalmente
         * después de una actualización. Esta función
         * existe únicamente para mantener la interfaz
         * sincronizada cuando la fila puede identificarse.
         */

    });

}


/**
 * ============================================================
 * MOSTRAR TOAST
 * ============================================================
 */

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
        String(mensaje || '');


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


/**
 * ============================================================
 * CERRAR CON ESCAPE
 * ============================================================
 */

function manejarTecla(event) {

    if (
        event.key === 'Escape' &&
        postulacionActual
    ) {

        cerrarPostulacion();

    }

}


/**
 * ============================================================
 * CERRAR AL HACER CLIC FUERA
 * ============================================================
 */

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


/**
 * ============================================================
 * INICIALIZACIÓN
 * ============================================================
 */

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


/**
 * ============================================================
 * EXPONER FUNCIONES GLOBALMENTE
 * ============================================================
 *
 * Estas funciones son utilizadas directamente desde
 * los botones del HTML mediante onclick().
 */

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


/**
 * ============================================================
 * ARRANQUE
 * ============================================================
 */

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
