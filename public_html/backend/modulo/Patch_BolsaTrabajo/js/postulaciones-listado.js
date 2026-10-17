/**
 * ============================================================
 * LISTADO DE POSTULACIONES
 * Patch_BolsaTrabajo
 * ============================================================
 *
 * Funciones relacionadas con:
 * - Filtros de postulaciones
 * - Búsqueda
 * - Filtrado por vacante y estado
 * - Limpieza de filtros
 * - Paginación independiente de las tablas
 * - Interacciones del listado
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
     * SELECCIONAR VACANTE
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
            buscar: campoBusqueda ? campoBusqueda.value.trim() : '',
            vacante: selectVacante ? selectVacante.value : '0',
            estado: selectEstado ? selectEstado.value : ''
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
            (filtros.vacante !== '' && filtros.vacante !== '0') ||
            filtros.estado !== ''
        );
    }

    /**
     * ============================================================
     * ACTUALIZAR ESTADO VISUAL DE LOS FILTROS
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

        campoBusqueda.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                buscarPostulaciones();
            }
        });
    }

    /**
     * ============================================================
     * CAMBIOS AUTOMÁTICOS DE FILTROS
     * ============================================================
     *
     * El formulario continúa utilizando GET y submit tradicional.
     */

    function configurarFiltros() {
        const selectVacante = obtenerSelectVacante();
        const selectEstado = obtenerSelectEstado();

        if (selectVacante) {
            selectVacante.addEventListener('change', function () {
                actualizarEstadoFiltros();
            });
        }

        if (selectEstado) {
            selectEstado.addEventListener('change', function () {
                actualizarEstadoFiltros();
            });
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

        formulario.addEventListener('submit', function () {
            const botones = formulario.querySelectorAll(
                'button[type="submit"]'
            );

            botones.forEach(function (boton) {
                boton.disabled = true;
            });
        });
    }

    /**
     * ============================================================
     * SCROLL AL LISTADO DESPUÉS DE FILTRAR
     * ============================================================
     */

    function posicionarListado() {
        const panel = document.getElementById('panelPostulaciones');

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

        // No se realiza scroll automáticamente para conservar
        // el comportamiento actual del módulo.
    }

    /**
     * ============================================================
     * PAGINACIÓN: CREAR BOTONES
     * ============================================================
     */

    function crearBotonPagina(texto, pagina, opciones) {
        opciones = opciones || {};

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'patch-pagination-button';
        boton.textContent = String(texto);
        boton.disabled = Boolean(opciones.disabled);

        if (opciones.active) {
            boton.classList.add('is-active');
            boton.setAttribute('aria-current', 'page');
        }

        if (opciones.label) {
            boton.setAttribute('aria-label', opciones.label);
        }

        if (typeof pagina === 'number' && !boton.disabled) {
            boton.addEventListener('click', function () {
                if (typeof opciones.onSelect === 'function') {
                    opciones.onSelect(pagina);
                }
            });
        }

        return boton;
    }

    /**
     * ============================================================
     * PAGINACIÓN: NÚMEROS DE PÁGINA
     * ============================================================
     */

    function obtenerNumerosPagina(paginaActual, totalPaginas) {
        if (totalPaginas <= 0) {
            return [];
        }

        if (totalPaginas <= 7) {
            return Array.from(
                { length: totalPaginas },
                function (_, indice) {
                    return indice + 1;
                }
            );
        }

        const paginas = [1];
        const inicio = Math.max(2, paginaActual - 1);
        const fin = Math.min(totalPaginas - 1, paginaActual + 1);

        if (inicio > 2) {
            paginas.push('...');
        }

        for (let pagina = inicio; pagina <= fin; pagina++) {
            paginas.push(pagina);
        }

        if (fin < totalPaginas - 1) {
            paginas.push('...');
        }

        paginas.push(totalPaginas);

        return paginas;
    }

    /**
     * ============================================================
     * PAGINACIÓN: ACTUALIZAR CONTROLES
     * ============================================================
     */

    function actualizarControlesPaginacion(estado) {
        const totalRegistros = estado.filas.length;
        const totalPaginas = Math.ceil(
            totalRegistros / estado.tamanoPagina
        );

        const paginaActual = totalPaginas > 0
            ? Math.min(Math.max(estado.pagina, 1), totalPaginas)
            : 1;

        estado.pagina = paginaActual;

        if (estado.resumen) {
            if (totalRegistros === 0) {
                estado.resumen.textContent = 'Mostrando 0 registros';
            } else {
                const primero =
                    (paginaActual - 1) * estado.tamanoPagina + 1;

                const ultimo = Math.min(
                    paginaActual * estado.tamanoPagina,
                    totalRegistros
                );

                estado.resumen.textContent =
                    'Mostrando ' + primero + '–' + ultimo +
                    ' de ' + totalRegistros + ' ' +
                    (totalRegistros === 1 ? 'registro' : 'registros');
            }
        }

        if (!estado.controles) {
            return;
        }

        estado.controles.replaceChildren();

        const botonAnterior = crearBotonPagina(
            'Anterior',
            paginaActual - 1,
            {
                disabled: paginaActual <= 1,
                label: 'Ir a la página anterior',
                onSelect: function (pagina) {
                    cambiarPagina(estado, pagina);
                }
            }
        );

        estado.controles.appendChild(botonAnterior);

        const numerosPagina = obtenerNumerosPagina(
            paginaActual,
            totalPaginas
        );

        numerosPagina.forEach(function (pagina) {
            if (pagina === '...') {
                const separador = document.createElement('span');
                separador.className = 'patch-pagination-ellipsis';
                separador.textContent = '…';
                separador.setAttribute('aria-hidden', 'true');
                estado.controles.appendChild(separador);
                return;
            }

            const boton = crearBotonPagina(
                pagina,
                pagina,
                {
                    active: pagina === paginaActual,
                    label: 'Ir a la página ' + pagina,
                    onSelect: function (numeroPagina) {
                        cambiarPagina(estado, numeroPagina);
                    }
                }
            );

            estado.controles.appendChild(boton);
        });

        const botonSiguiente = crearBotonPagina(
            'Siguiente',
            paginaActual + 1,
            {
                disabled:
                    totalPaginas === 0 || paginaActual >= totalPaginas,
                label: 'Ir a la página siguiente',
                onSelect: function (pagina) {
                    cambiarPagina(estado, pagina);
                }
            }
        );

        estado.controles.appendChild(botonSiguiente);
    }

    /**
     * ============================================================
     * PAGINACIÓN: MOSTRAR FILAS DE LA PÁGINA ACTUAL
     * ============================================================
     */

    function mostrarPagina(estado) {
        const totalRegistros = estado.filas.length;
        const totalPaginas = Math.ceil(
            totalRegistros / estado.tamanoPagina
        );

        if (totalPaginas === 0) {
            estado.pagina = 1;
        } else {
            estado.pagina = Math.min(
                Math.max(estado.pagina, 1),
                totalPaginas
            );
        }

        const inicio = (estado.pagina - 1) * estado.tamanoPagina;
        const fin = inicio + estado.tamanoPagina;

        estado.filas.forEach(function (fila, indice) {
            fila.hidden = indice < inicio || indice >= fin;
        });

        actualizarControlesPaginacion(estado);
    }

    /**
     * ============================================================
     * PAGINACIÓN: CAMBIAR PÁGINA
     * ============================================================
     */

    function cambiarPagina(estado, pagina) {
        const totalPaginas = Math.ceil(
            estado.filas.length / estado.tamanoPagina
        );

        if (
            !Number.isFinite(pagina) ||
            pagina < 1 ||
            pagina > totalPaginas ||
            pagina === estado.pagina
        ) {
            return;
        }

        estado.pagina = pagina;
        mostrarPagina(estado);
    }

    /**
     * ============================================================
     * PAGINACIÓN: INICIALIZAR UNA TABLA
     * ============================================================
     *
     * El selector de tamaño está en el encabezado del panel.
     * Los controles de navegación están debajo de la tabla.
     * Cada tabla conserva su propia paginación independiente.
     */

    function inicializarPaginacionTabla(contenedor) {
        const identificador = contenedor.getAttribute('data-pagination');

        if (!identificador) {
            return;
        }

        const tabla = document.querySelector(
            'table[data-paginated-table="' + identificador + '"]'
        );

        if (!tabla) {
            return;
        }

        const cuerpo = tabla.tBodies[0];

        if (!cuerpo) {
            return;
        }

        const filas = Array.from(cuerpo.rows);

        // Buscar el selector fuera del contenedor inferior.
        // Ejemplos: data-page-size="postulaciones"
        //           data-page-size="cv-generales"
        const selectorTamano = document.querySelector(
            '[data-page-size="' + identificador + '"]'
        );

        const resumen = contenedor.querySelector(
            '[data-pagination-summary]'
        );

        const controles = contenedor.querySelector(
            '[data-page-controls]'
        );

        const estado = {
            identificador: identificador,
            tabla: tabla,
            filas: filas,
            pagina: 1,
            tamanoPagina: 10,
            selectorTamano: selectorTamano,
            resumen: resumen,
            controles: controles
        };

        if (selectorTamano) {
            const tamanoInicial = parseInt(selectorTamano.value, 10);

            if ([10, 25, 50].includes(tamanoInicial)) {
                estado.tamanoPagina = tamanoInicial;
            }

            selectorTamano.addEventListener('change', function () {
                const nuevoTamano = parseInt(
                    selectorTamano.value,
                    10
                );

                if (![10, 25, 50].includes(nuevoTamano)) {
                    selectorTamano.value = String(estado.tamanoPagina);
                    return;
                }

                estado.tamanoPagina = nuevoTamano;
                estado.pagina = 1;
                mostrarPagina(estado);
            });
        }

        mostrarPagina(estado);
    }

    /**
     * ============================================================
     * PAGINACIÓN: INICIALIZAR TODAS LAS TABLAS
     * ============================================================
     */

    function inicializarPaginacion() {
        const contenedores = document.querySelectorAll(
            '[data-pagination]'
        );

        contenedores.forEach(function (contenedor) {
            inicializarPaginacionTabla(contenedor);
        });
    }

    /**
     * ============================================================
     * INICIALIZACIÓN GENERAL
     * ============================================================
     */

    function inicializarListado() {
        configurarBusqueda();
        configurarFiltros();
        configurarFormulario();
        actualizarEstadoFiltros();
        posicionarListado();
        inicializarPaginacion();
    }

    /**
     * ============================================================
     * EXPONER FUNCIONES GLOBALMENTE
     * ============================================================
     *
     * Se conservan porque el HTML utiliza estas funciones mediante
     * atributos onclick() y otros scripts del módulo pueden llamarlas.
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
