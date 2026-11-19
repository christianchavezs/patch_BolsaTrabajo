/**
 * ============================================================
 * JAVASCRIPT - DASHBOARD DE REPORTES Y ESTADÍSTICAS
 * Proyecto: Patch_BolsaTrabajo (Kombitec)
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/js/reportes-admin.js
 * ============================================================
 */

(function () {
    'use strict';

    // Leer datos inyectados desde PHP
    var scriptDatos = document.getElementById('datosReportesJSON');
    if (!scriptDatos) {
        console.warn('No se encontraron los datos estadísticos para los gráficos.');
        return;
    }

    var datos;
    try {
        datos = JSON.parse(scriptDatos.textContent || '{}');
    } catch (e) {
        console.error('Error al parsear datos JSON de reportes:', e);
        return;
    }

    // Configuración global de Chart.js
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
        Chart.defaults.color = '#52635a';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(11, 59, 39, 0.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold', size: 13 };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 10;
        Chart.defaults.plugins.legend.labels.boxHeight = 10;
    }

    // ------------------------------------------------------------
    // 1. GRÁFICO DE ESTADOS DE CANDIDATOS (DOUGHNUT)
    // ------------------------------------------------------------
    var canvasEstado = document.getElementById('chartEstadoPostulantes');
    if (canvasEstado && datos.estados) {
        var totalCandidatos = datos.estados.data.reduce(function (a, b) { return a + b; }, 0);

        new Chart(canvasEstado, {
            type: 'doughnut',
            data: {
                labels: datos.estados.labels,
                datasets: [{
                    data: datos.estados.data,
                    backgroundColor: datos.estados.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 14,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var val = context.raw || 0;
                                var pct = totalCandidatos > 0 ? ((val / totalCandidatos) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ------------------------------------------------------------
    // 2. GRÁFICO DE TOP VACANTES CON MAYOR DEMANDA (BAR HORIZONTAL)
    // ------------------------------------------------------------
    var canvasTop = document.getElementById('chartTopVacantes');
    if (canvasTop && datos.topVacantes && datos.topVacantes.labels.length > 0) {
        new Chart(canvasTop, {
            type: 'bar',
            data: {
                labels: datos.topVacantes.labels,
                datasets: [
                    {
                        label: 'Total Postulaciones',
                        data: datos.topVacantes.data,
                        backgroundColor: '#159a5b',
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Aceptados / Contratados',
                        data: datos.topVacantes.aceptados,
                        backgroundColor: '#22b86a',
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11.5, weight: '500' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 12 }
                    }
                }
            }
        });
    } else if (canvasTop) {
        // En caso de estar vacío
        var ctxEmpty = canvasTop.getContext('2d');
        if (ctxEmpty) {
            ctxEmpty.font = '14px Roboto, sans-serif';
            ctxEmpty.fillStyle = '#94a3b8';
            ctxEmpty.textAlign = 'center';
            ctxEmpty.fillText('Sin datos registrados en este período', canvasTop.width / 2, canvasTop.height / 2);
        }
    }

    // ------------------------------------------------------------
    // 3. GRÁFICO DE TENDENCIA MENSUAL (LINE CON GRADIENTE VERDE)
    // ------------------------------------------------------------
    var canvasTend = document.getElementById('chartTendenciaMensual');
    if (canvasTend && datos.tendencia && datos.tendencia.labels.length > 0) {
        var ctxTend = canvasTend.getContext('2d');
        var gradientVerde = ctxTend.createLinearGradient(0, 0, 0, 260);
        gradientVerde.addColorStop(0, 'rgba(34, 184, 106, 0.35)');
        gradientVerde.addColorStop(1, 'rgba(34, 184, 106, 0.00)');

        new Chart(canvasTend, {
            type: 'line',
            data: {
                labels: datos.tendencia.labels,
                datasets: [
                    {
                        label: 'Postulaciones Recibidas',
                        data: datos.tendencia.totales,
                        borderColor: '#159a5b',
                        backgroundColor: gradientVerde,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#12834d',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 6.5
                    },
                    {
                        label: 'Candidatos en Entrevista',
                        data: datos.tendencia.entrevistas,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        pointBackgroundColor: '#7c3aed',
                        pointRadius: 3.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 12 }
                    }
                }
            }
        });
    } else if (canvasTend) {
        var ctxEmpty2 = canvasTend.getContext('2d');
        if (ctxEmpty2) {
            ctxEmpty2.font = '14px Roboto, sans-serif';
            ctxEmpty2.fillStyle = '#94a3b8';
            ctxEmpty2.textAlign = 'center';
            ctxEmpty2.fillText('Sin historial registrado en este período', canvasTend.width / 2, canvasTend.height / 2);
        }
    }

    // ------------------------------------------------------------
    // 4. GRÁFICO DE MODALIDADES (DOUGHNUT)
    // ------------------------------------------------------------
    var canvasMod = document.getElementById('chartModalidades');
    if (canvasMod && datos.modalidades && datos.modalidades.labels.length > 0) {
        new Chart(canvasMod, {
            type: 'doughnut',
            data: {
                labels: datos.modalidades.labels,
                datasets: [{
                    data: datos.modalidades.data,
                    backgroundColor: datos.modalidades.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }

    // Auto-cierre del menú de usuario al hacer clic fuera
    var userMenu = document.getElementById('patchUserMenu');
    if (userMenu) {
        document.addEventListener('click', function (e) {
            if (!userMenu.contains(e.target)) {
                userMenu.removeAttribute('open');
            }
        });
    }
})();
