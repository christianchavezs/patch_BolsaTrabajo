/**
 * ============================================================
 * JAVASCRIPT - MODAL DE EXPORTACIÓN A EXCEL
 * Proyecto: Patch_BolsaTrabajo (Kombitec)
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/js/exportar-excel.js
 * ============================================================
 */

(function (window, document) {
    'use strict';

    function obtenerElemento(id) {
        return document.getElementById(id);
    }

    /**
     * Abre el modal de exportación a Excel.
     * @param {Object|string} opciones - Opciones de configuración o tipo directo ('postulaciones', 'vacantes', 'reporte_general')
     */
    function abrirModalExportar(opciones) {
        opciones = opciones || {};
        if (typeof opciones === 'string') {
            opciones = { tipo: opciones };
        }

        const modal = obtenerElemento('modalExportarExcel');
        if (!modal) return;

        // Si la página actual tiene filtros activos, preseleccionarlos
        const selectVacantePagina = obtenerElemento('filtroVacante') || obtenerElemento('filtroVacanteSelect');
        const selectEstadoPagina = obtenerElemento('filtroEstado');
        const inputFechaInicioPagina = obtenerElemento('filtroFechaInicio');
        const inputFechaFinPagina = obtenerElemento('filtroFechaFin');

        const exportarVacante = obtenerElemento('exportarFiltroVacante');
        const exportarEstado = obtenerElemento('exportarFiltroEstado');
        const exportarFechaInicio = obtenerElemento('exportarFechaInicio');
        const exportarFechaFin = obtenerElemento('exportarFechaFin');

        if (exportarVacante && selectVacantePagina) {
            exportarVacante.value = selectVacantePagina.value || '0';
        }

        if (exportarEstado && selectEstadoPagina) {
            exportarEstado.value = selectEstadoPagina.value || 'todas';
        }

        if (exportarFechaInicio && inputFechaInicioPagina && inputFechaInicioPagina.value) {
            exportarFechaInicio.value = inputFechaInicioPagina.value;
        }

        if (exportarFechaFin && inputFechaFinPagina && inputFechaFinPagina.value) {
            exportarFechaFin.value = inputFechaFinPagina.value;
        }

        // Si se pasan opciones directas
        if (opciones.vacante !== undefined && exportarVacante) {
            exportarVacante.value = opciones.vacante;
        }

        if (opciones.estado !== undefined && exportarEstado) {
            exportarEstado.value = opciones.estado;
        }

        if (opciones.tipo) {
            const radioTipo = document.querySelector(`input[name="tipo"][value="${opciones.tipo}"]`);
            if (radioTipo) {
                radioTipo.checked = true;
                actualizarCamposExportar(opciones.tipo);
            }
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    /**
     * Cierra el modal de exportación.
     */
    function cerrarModalExportar() {
        const modal = obtenerElemento('modalExportarExcel');
        if (!modal) return;

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    /**
     * Alterna la configuración visual según si se exportan candidatos, vacantes o reporte general.
     */
    function actualizarCamposExportar(tipo) {
        const cards = document.querySelectorAll('.patch-export-type-card');
        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === tipo) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });

        const grupoVacante = obtenerElemento('grupoExportarVacante');
        const grupoEstado = obtenerElemento('grupoExportarEstado');
        const selectEstado = obtenerElemento('exportarFiltroEstado');

        if (tipo === 'vacantes') {
            if (grupoVacante) grupoVacante.style.display = 'none';
            if (grupoEstado) grupoEstado.style.display = 'block';

            if (selectEstado) {
                selectEstado.innerHTML = `
                    <option value="todas">Todas las vacantes</option>
                    <option value="activas">Solo vacantes activas</option>
                    <option value="inactivas">Solo vacantes inactivas</option>
                    <option value="archivadas">Solo vacantes archivadas</option>
                `;
            }
        } else if (tipo === 'reporte_general') {
            if (grupoVacante) grupoVacante.style.display = 'block';
            if (grupoEstado) grupoEstado.style.display = 'none';
        } else {
            // 'postulaciones'
            if (grupoVacante) grupoVacante.style.display = 'block';
            if (grupoEstado) grupoEstado.style.display = 'block';

            if (selectEstado) {
                selectEstado.innerHTML = `
                    <option value="todas">Todos los estados</option>
                    <option value="nueva">Por revisar (Nuevas)</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="entrevista">Entrevista agendada</option>
                    <option value="aceptado">Aceptado / Contratado</option>
                    <option value="descartado">Descartado</option>
                `;
            }
        }
    }

    /**
     * Define rangos rápidos de fecha.
     */
    function definirRangoFecha(atajo) {
        const inputInicio = obtenerElemento('exportarFechaInicio');
        const inputFin = obtenerElemento('exportarFechaFin');
        if (!inputInicio || !inputFin) return;

        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaHoy = `${yyyy}-${mm}-${dd}`;

        if (atajo === 'todo') {
            inputInicio.value = '';
            inputFin.value = '';
        } else if (atajo === 'mes_actual') {
            inputInicio.value = `${yyyy}-${mm}-01`;
            inputFin.value = fechaHoy;
        } else if (atajo === '3_meses') {
            const hace3Meses = new Date();
            hace3Meses.setMonth(hace3Meses.getMonth() - 3);
            const m3 = String(hace3Meses.getMonth() + 1).padStart(2, '0');
            const d3 = String(hace3Meses.getDate()).padStart(2, '0');
            inputInicio.value = `${hace3Meses.getFullYear()}-${m3}-${d3}`;
            inputFin.value = fechaHoy;
        } else if (atajo === 'anio_actual') {
            inputInicio.value = `${yyyy}-01-01`;
            inputFin.value = fechaHoy;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = obtenerElemento('modalExportarExcel');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    cerrarModalExportar();
                }
            });
        }

        // Al enviar el formulario, cerrar el modal después de 800ms
        const form = obtenerElemento('formExportarExcel');
        if (form) {
            form.addEventListener('submit', function () {
                setTimeout(cerrarModalExportar, 800);
            });
        }
    });

    window.abrirModalExportar = abrirModalExportar;
    window.cerrarModalExportar = cerrarModalExportar;
    window.actualizarCamposExportar = actualizarCamposExportar;
    window.definirRangoFecha = definirRangoFecha;

})(window, document);
