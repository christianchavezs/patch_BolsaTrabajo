/**
 * ============================================================
 * JAVASCRIPT - ADMINISTRACIÓN DE PLANTILLAS DE CORREO
 * Proyecto: Patch_BolsaTrabajo
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/js/plantillas-admin.js
 * ============================================================
 */
(function (window, document) {
    'use strict';

    let toastTimer = null;

    function mostrarToast(mensaje, esError = false) {
        const toast = document.getElementById('patchToast');
        if (!toast) return;

        if (toastTimer !== null) {
            clearTimeout(toastTimer);
            toastTimer = null;
        }

        toast.className = 'patch-toast ' + (esError ? 'error' : 'success');
        toast.innerHTML = (esError ? '<i class="fa fa-circle-exclamation"></i>' : '<i class="fa fa-circle-check"></i>') +
                          '<span>' + String(mensaje || '') + '</span>';

        toast.classList.add('show');

        toastTimer = setTimeout(function () {
            toast.classList.remove('show');
            toastTimer = null;
        }, 3500);
    }

    // Inicializar pestañas
    function initTabs() {
        const tabBtns = document.querySelectorAll('.patch-tab-btn');
        tabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.dataset.tab;
                tabBtns.forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.patch-plantilla-panel').forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                const targetPanel = document.getElementById(targetId);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });
    }

    // Datos ficticios para simulación de vista previa
    const datosMuestra = {
        nombre: 'Lic. Fernando Morales',
        vacante: 'Desarrollador Web Senior',
        fecha_entrevista: 'Lunes, 16 de Noviembre de 2026 a las 10:30 hrs',
        empresa: 'Kombitec S.A. de C.V.'
    };

    function formatearVistaPrevia(texto) {
        let seguro = String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Inyectar datos en negrita respetando la identidad Kombitec
        seguro = seguro
            .replace(/\{nombre\}/g, '<strong style="color: #0b3b27;">' + datosMuestra.nombre + '</strong>')
            .replace(/\{vacante\}/g, '<strong style="color: #0f633d;">' + datosMuestra.vacante + '</strong>')
            .replace(/\{fecha_entrevista\}/g, '<strong style="color: #0f633d;">' + datosMuestra.fecha_entrevista + '</strong>')
            .replace(/\{empresa\}/g, '<strong style="color: #0b3b27;">' + datosMuestra.empresa + '</strong>');

        // Formatear saltos de línea
        seguro = seguro.replace(/\n/g, '<br>');

        // Si detecta la línea de cita de entrevista, convertirla en tarjeta destacada con fecha y horario en negrita
        const regexCita = /(?:📅\s*)?Cita de entrevista:\s*([^<]+)/i;
        if (regexCita.test(seguro)) {
            seguro = seguro.replace(regexCita, function (match, p1) {
                return '<div style="background: #f4fcf7; border: 1px solid #bbf7d0; border-left: 5px solid #159a5b; padding: 14px 18px; border-radius: 8px; margin: 16px 0; box-shadow: 0 2px 6px rgba(21, 154, 91, 0.08);">' +
                       '<div style="color: #12834d; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">📅 Cita de entrevista confirmada</div>' +
                       '<div style="font-size: 14.5px; color: #17211c;">' +
                       '<strong>Fecha y Horario:</strong> <span style="color: #0f633d; font-weight: 700;">' + p1.trim() + '</span>' +
                       '</div>' +
                       '</div>';
            });
        }

        return seguro;
    }

    // Conectar vistas previas en tiempo real
    function initPreviews() {
        ['positivo', 'negativo'].forEach(function (clave) {
            const textarea = document.getElementById('cuerpo_' + clave);
            const previewEl = document.getElementById('preview_' + clave);

            function updatePreview() {
                if (textarea && previewEl) {
                    previewEl.innerHTML = formatearVistaPrevia(textarea.value);
                }
            }

            if (textarea) {
                textarea.addEventListener('input', updatePreview);
                updatePreview();
            }
        });
    }

    // Insertar comodín en la posición actual del cursor
    function initChips() {
        document.querySelectorAll('.patch-var-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                const clave = this.dataset.clave;
                const tag = this.dataset.tag;
                const textarea = document.getElementById('cuerpo_' + clave);
                if (!textarea) return;

                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;

                textarea.value = text.substring(0, start) + tag + text.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + tag.length;
                textarea.focus();

                // Disparar input event para actualizar vista previa
                textarea.dispatchEvent(new Event('input'));
            });
        });
    }

    // Guardado por AJAX de plantillas
    function initForms() {
        document.querySelectorAll('.form-guardar-plantilla').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const btnSubmit = form.querySelector('.patch-submit-btn');
                const clave = form.dataset.clave;
                const asuntoInput = form.querySelector('[name="asunto"]');
                const cuerpoInput = form.querySelector('[name="cuerpo"]');

                if (!asuntoInput || !cuerpoInput) return;

                const asunto = asuntoInput.value.trim();
                const cuerpo = cuerpoInput.value.trim();

                if (!asunto) {
                    mostrarToast('Por favor ingresa el asunto del correo.', true);
                    asuntoInput.focus();
                    return;
                }

                if (!cuerpo) {
                    mostrarToast('El cuerpo del correo no puede estar vacío.', true);
                    cuerpoInput.focus();
                    return;
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
                }

                const formData = new FormData();
                formData.append('clave', clave);
                formData.append('asunto', asunto);
                formData.append('cuerpo', cuerpo);

                fetch('api/plantillas_correo.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    if (data && data.ok) {
                        mostrarToast(data.mensaje || 'Plantilla guardada correctamente.', false);
                    } else {
                        mostrarToast((data && data.mensaje) ? data.mensaje : 'No se pudo guardar la plantilla.', true);
                    }
                })
                .catch(function (err) {
                    mostrarToast('Error al conectar con el servidor: ' + err.message, true);
                })
                .finally(function () {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fa fa-floppy-disk"></i> Guardar plantilla';
                    }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        initPreviews();
        initChips();
        initForms();
    });

})(window, document);
