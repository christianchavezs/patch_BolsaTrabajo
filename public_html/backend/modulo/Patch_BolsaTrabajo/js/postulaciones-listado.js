/**

* ============================================================
* LISTADO DE POSTULACIONES
* Patch_BolsaTrabajo
* ============================================================
*
* Funciones relacionadas con:
* * Filtros de postulaciones
* * Búsqueda
* * Filtrado por vacante
* * Filtrado por estado
* * Limpieza de filtros
* * Interacciones del listado
* ============================================================
  */

(function (window, document) {


'use strict';


/**
 * ============================================================
 * OBTENER ELEMENTOS
 * ============================================================
 */

function obtenerFormularioFiltros() {

    return document.getElementById('formFiltros');

}


function obtenerSelectVacante() {

    return document.getElementById('filtroVacante');

}


function obtenerSelectEstado() {

    return document.getElementById('filtroEstado');

}


function obtenerCampoBusqueda() {

    return document.getElementById('buscar');

}


/**
 * ============================================================
 * FILTRAR POR VACANTE
 * ============================================================
 */

function filtrarVacante(id) {

    const select = obtenerSelectVacante();
    const formulario = obtenerFormularioFiltros();

    if (!select || !formulario) {
        return;
    }


    const idVacante = parseInt(id, 10);

    if (!Number.isFinite(idVacante) || idVacante <= 0) {
        return;
    }


    select.value = String(idVacante);


    formulario.submit();

}


/**
 * ============================================================
 * FILTRAR POR ESTADO
 * ============================================================
 */

function filtrarEstado(estado) {

    const select = obtenerSelectEstado();
    const formulario = obtenerFormularioFiltros();

    if (!select || !formulario) {
        return;
    }


    estado = String(estado || '').trim();


    select.value = estado;


    formulario.submit();

}


/**
 * ============================================================
 * REALIZAR BÚSQUEDA
 * ============================================================
 */

function buscarPostulaciones() {

    const formulario = obtenerFormularioFiltros();

    if (!formulario) {
        return;
    }


    formulario.submit();

}


/**
 * ============================================================
 * LIMPIAR FILTROS
 * ============================================================
 */

function limpiarFiltros() {

    const formulario = obtenerFormularioFiltros();

    if (!formulario) {

        window.location.href = 'Admin_Postulaciones.php';

        return;

    }


    const url = new URL(
        'Admin_Postulaciones.php',
        window.location.href
    );


    window.location.href = url.toString();

}


/**
 * ============================================================
 * SELECCIONAR TODAS LAS OPCIONES DEL FILTRO
 * ============================================================
 */

function establecerFiltroVacante(id) {

    const select = obtenerSelectVacante();

    if (!select) {
        return;
    }


    const idVacante = parseInt(id, 10);

    if (!Number.isFinite(idVacante)) {
        select.value = '0';
        return;
    }


    select.value = String(idVacante);

}


/**
 * ============================================================
 * OBTENER FILTROS ACTUALES
 * ============================================================
 */

function obtenerFiltrosActuales() {

    const campoBusqueda = obtenerCampoBusqueda();
    const selectVacante = obtenerSelectVacante();
    const selectEstado = obtenerSelectEstado();


    return {

        buscar: campoBusqueda
            ? campoBusqueda.value.trim()
            : '',

        vacante: selectVacante
            ? selectVacante.value
            : '0',

        estado: selectEstado
            ? selectEstado.value
            : ''

    };

}


/**
 * ============================================================
 * DETECTAR SI EXISTEN FILTROS
 * ============================================================
 */

function existenFiltrosActivos() {

    const filtros = obtenerFiltrosActuales();


    return (
        filtros.buscar !== '' ||
        (
            filtros.vacante !== '' &&
            filtros.vacante !== '0'
        ) ||
        filtros.estado !== ''
    );

}


/**
 * ============================================================
 * ACTUALIZAR VISUALMENTE EL ESTADO DE LOS FILTROS
 * ============================================================
 */

function actualizarEstadoFiltros() {

    const formulario = obtenerFormularioFiltros();

    if (!formulario) {
        return;
    }


    formulario.classList.toggle(
        'has-filters',
        existenFiltrosActivos()
    );

}


/**
 * ============================================================
 * ENTER EN BÚSQUEDA
 * ============================================================
 */

function configurarBusqueda() {

    const campoBusqueda = obtenerCampoBusqueda();

    if (!campoBusqueda) {
        return;
    }


    campoBusqueda.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                buscarPostulaciones();

            }

        }
    );

}


/**
 * ============================================================
 * CAMBIOS AUTOMÁTICOS DE FILTROS
 * ============================================================
 *
 * El formulario continúa utilizando GET y submit tradicional.
 * Esto conserva el comportamiento actual del módulo.
 */

function configurarFiltros() {

    const selectVacante = obtenerSelectVacante();
    const selectEstado = obtenerSelectEstado();


    if (selectVacante) {

        selectVacante.addEventListener(
            'change',
            function () {

                actualizarEstadoFiltros();

            }
        );

    }


    if (selectEstado) {

        selectEstado.addEventListener(
            'change',
            function () {

                actualizarEstadoFiltros();

            }
        );

    }

}


/**
 * ============================================================
 * EVITAR DOBLE ENVÍO ACCIDENTAL
 * ============================================================
 */

function configurarFormulario() {

    const formulario = obtenerFormularioFiltros();

    if (!formulario) {
        return;
    }


    formulario.addEventListener(
        'submit',
        function () {

            const botones = formulario.querySelectorAll(
                'button[type="submit"]'
            );


            botones.forEach(function (boton) {

                boton.disabled = true;

            });

        }
    );

}


/**
 * ============================================================
 * SCROLL AL LISTADO DESPUÉS DE FILTRAR
 * ============================================================
 */

function posicionarListado() {

    const panel = document.getElementById(
        'panelPostulaciones'
    );

    if (!panel) {
        return;
    }


    const parametros = new URLSearchParams(
        window.location.search
    );


    const tieneFiltros =
        parametros.has('buscar') ||
        parametros.has('vacante') ||
        parametros.has('estado');


    if (!tieneFiltros) {
        return;
    }


    /*
     * No hacemos scroll automáticamente.
     *
     * La función queda preparada para futuras necesidades
     * sin modificar el comportamiento actual del módulo.
     */

}


/**
 * ============================================================
 * INICIALIZACIÓN
 * ============================================================
 */

function inicializarListado() {

    configurarBusqueda();

    configurarFiltros();

    configurarFormulario();

    actualizarEstadoFiltros();

    posicionarListado();

}


/**
 * ============================================================
 * EXPONER FUNCIONES GLOBALMENTE
 * ============================================================
 *
 * Se mantienen globales porque son utilizadas directamente
 * desde los elementos HTML del módulo mediante onclick().
 */

window.filtrarVacante = filtrarVacante;

window.filtrarEstado = filtrarEstado;

window.buscarPostulaciones = buscarPostulaciones;

window.limpiarFiltros = limpiarFiltros;

window.establecerFiltroVacante = establecerFiltroVacante;

window.obtenerFiltrosActuales = obtenerFiltrosActuales;

window.existenFiltrosActivos = existenFiltrosActivos;


/**
 * ============================================================
 * ARRANQUE
 * ============================================================
 */

if (document.readyState === 'loading') {

    document.addEventListener(
        'DOMContentLoaded',
        inicializarListado
    );

} else {

    inicializarListado();

}


})(window, document);
