/**
 * ============================================================
 * JAVASCRIPT - MODAL DE ENVÍO DE CORREO A POSTULANTES
 * Proyecto: Patch_BolsaTrabajo
 * Archivo: public_html/backend/modulo/Patch_BolsaTrabajo/js/correo-postulacion.js
 * ============================================================
 */

(function (window, document) {
    'use strict';

    let cachePlantillas = null;
    let tipoActual = 'positivo'; // 'positivo' | 'negativo'
    let edicionManualActivada = false;

    const EMPRESA_NOMBRE = 'Kombitec';

    function obtenerElemento(id) {
        return document.getElementById(id);
    }

    /**
     * Formatea un valor datetime (YYYY-MM-DDTHH:MM) en texto legible en español.
     * Ejemplo: "Jueves, 12 de Noviembre de 2026 a las 10:30 hrs"
     */
    function formatearFechaHoraLegible(valorDatetime) {
        if (!valorDatetime) return '[Fecha y hora por definir]';

        try {
            const fechaObj = new Date(valorDatetime);
            if (isNaN(fechaObj.getTime())) return valorDatetime;

            const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];

            const diaSemana = dias[fechaObj.getDay()];
            const diaMes = fechaObj.getDate();
            const mes = meses[fechaObj.getMonth()];
            const anio = fechaObj.getFullYear();

            let horas = fechaObj.getHours();
            let minutos = fechaObj.getMinutes();
            const horasStr = horas < 10 ? '0' + horas : horas;
            const minutosStr = minutos < 10 ? '0' + minutos : minutos;

            return diaSemana + ', ' + diaMes + ' de ' + mes + ' de ' + anio + ' a las ' + horasStr + ':' + minutosStr + ' hrs';
        } catch (e) {
            return valorDatetime;
        }
    }

    /**
     * Carga las plantillas desde el servidor si no están en caché.
     */
    async function cargarPlantillas() {
        if (cachePlantillas) return cachePlantillas;

        try {
            const res = await fetch('api/plantillas_correo.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.ok && data.datos) {
                cachePlantillas = data.datos;
                return cachePlantillas;
            }
        } catch (e) {
            console.error('Error al cargar plantillas de correo:', e);
        }

        return null;
    }

    /**
     * Genera el texto reemplazando las variables con los datos del postulante.
     */
    function construirMensaje(textoBase, postulacion, fechaEntrevistaTexto) {
        const nombre = (postulacion.nombre_completo || 'Candidato').trim();
        const vacanteObj = postulacion.vacante || {};
        const vacante = (vacanteObj.titulo || postulacion.vacante_titulo || 'Bolsa de Trabajo / CV General').trim();
        const fechaCita = fechaEntrevistaTexto || '[Fecha y hora por definir en el selector]';

        return String(textoBase || '')
            .replace(/\{nombre\}/g, nombre)
            .replace(/\{vacante\}/g, vacante)
            .replace(/\{fecha_entrevista\}/g, fechaCita)
            .replace(/\{empresa\}/g, EMPRESA_NOMBRE);
    }

    /**
     * Abre el modal de envío de correo configurado según el tipo ('positivo' o 'negativo').
     */
    async function abrirModalEnviarCorreo(tipo) {
        const modal = obtenerElemento('modalEnviarCorreo');
        if (!modal) return;

        tipoActual = (tipo === 'negativo') ? 'negativo' : 'positivo';
        edicionManualActivada = false;

        const postulacion = typeof window.obtenerPostulacionActual === 'function'
            ? window.obtenerPostulacionActual()
            : null;

        if (!postulacion) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Por favor selecciona primero un postulante.', true);
            }
            return;
        }

        // 1. Cargar las plantillas si no se han obtenido
        const plantillas = await cargarPlantillas();
        const plantillaActual = (plantillas && plantillas[tipoActual]) ? plantillas[tipoActual] : {
            asunto: (tipoActual === 'positivo')
                ? 'Seguimiento a tu postulación en Kombitec - Entrevista laboral'
                : 'Agradecimiento por tu interés en Kombitec - Vacante de {vacante}',
            cuerpo: (tipoActual === 'positivo')
                ? 'Estimado/a {nombre},\n\nConforme a lo acordado telefónicamente, confirmamos tu entrevista el {fecha_entrevista}.\n\nAtentamente,\nEquipo de Recursos Humanos\n{empresa}'
                : 'Estimado/a {nombre},\n\nAgradecemos tu interés en {vacante} en {empresa}.\n\nAtentamente,\nEquipo de Recursos Humanos\n{empresa}'
        };

        // 2. Llenar tarjeta del candidato
        const nombreCandidato = postulacion.nombre_completo || 'Sin nombre';
        const correoCandidato = postulacion.correo || 'Sin correo registrado';
        const vacanteObj = postulacion.vacante || {};
        const vacanteTitulo = vacanteObj.titulo || postulacion.vacante_titulo || 'CV General';

        const elAvatar = obtenerElemento('correoAvatar');
        if (elAvatar) {
            elAvatar.textContent = typeof window.obtenerIniciales === 'function'
                ? window.obtenerIniciales(nombreCandidato)
                : '?';
        }

        const elNombre = obtenerElemento('correoNombreCandidato');
        if (elNombre) elNombre.textContent = nombreCandidato;

        const elEmail = obtenerElemento('correoEmailDestino');
        if (elEmail) elEmail.textContent = correoCandidato;

        const elVacante = obtenerElemento('correoVacanteCandidato');
        if (elVacante) elVacante.textContent = vacanteTitulo;

        // 3. Ajustar badge y secciones según tipo
        const badgeTipo = obtenerElemento('badgeTipoCorreo');
        const seccionCita = obtenerElemento('seccionCitaEntrevista');
        const textoActualizarEstado = obtenerElemento('textoActualizarEstado');
        const checkActualizar = obtenerElemento('checkActualizarEstado');
        const selectorFecha = obtenerElemento('selectorFechaEntrevista');

        if (checkActualizar) checkActualizar.checked = true;

        if (tipoActual === 'positivo') {
            if (badgeTipo) {
                badgeTipo.className = 'patch-pill-tipo positive';
                badgeTipo.innerHTML = '<i class="fa fa-circle-check"></i> Plantilla Positiva / Cita';
            }
            if (seccionCita) seccionCita.style.display = 'block';
            if (textoActualizarEstado) {
                textoActualizarEstado.innerHTML = 'Actualizar automáticamente el estado del aspirante a <strong>"Entrevista"</strong>';
            }

            // Si el postulante ya tenía una fecha registrada
            if (selectorFecha) {
                if (postulacion.fecha_entrevista) {
                    try {
                        const d = new Date(postulacion.fecha_entrevista);
                        if (!isNaN(d.getTime())) {
                            const anio = d.getFullYear();
                            const mes = String(d.getMonth() + 1).padStart(2, '0');
                            const dia = String(d.getDate()).padStart(2, '0');
                            const hora = String(d.getHours()).padStart(2, '0');
                            const min = String(d.getMinutes()).padStart(2, '0');
                            selectorFecha.value = `${anio}-${mes}-${dia}T${hora}:${min}`;
                        } else {
                            selectorFecha.value = '';
                        }
                    } catch (e) {
                        selectorFecha.value = '';
                    }
                } else {
                    selectorFecha.value = '';
                }
            }
        } else {
            if (badgeTipo) {
                badgeTipo.className = 'patch-pill-tipo negative';
                badgeTipo.innerHTML = '<i class="fa fa-circle-xmark"></i> Plantilla Negativa / Descarte';
            }
            if (seccionCita) seccionCita.style.display = 'none';
            if (textoActualizarEstado) {
                textoActualizarEstado.innerHTML = 'Actualizar automáticamente el estado del aspirante a <strong>"Descartado"</strong>';
            }
            if (selectorFecha) selectorFecha.value = '';
        }

        // 4. Asunto y cuerpo procesados
        const fechaTextoInicial = (selectorFecha && selectorFecha.value)
            ? formatearFechaHoraLegible(selectorFecha.value)
            : '';

        const campoAsunto = obtenerElemento('campoAsuntoCorreo');
        if (campoAsunto) {
            campoAsunto.value = construirMensaje(plantillaActual.asunto, postulacion, fechaTextoInicial);
        }

        const campoCuerpo = obtenerElemento('campoCuerpoCorreo');
        if (campoCuerpo) {
            campoCuerpo.value = construirMensaje(plantillaActual.cuerpo, postulacion, fechaTextoInicial);
            campoCuerpo.readOnly = true;
            campoCuerpo.classList.add('readonly');
        }

        // 5. Restablecer estado de bloqueo
        actualizarEstadoBloqueoVisual(true);

        // 6. Mostrar modal
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    /**
     * Cierra el modal de envío de correo.
     */
    function cerrarModalEnviarCorreo() {
        const modal = obtenerElemento('modalEnviarCorreo');
        if (!modal) return;

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    /**
     * Actualiza la interfaz visual del estado de bloqueo / edición.
     */
    function actualizarEstadoBloqueoVisual(estaBloqueado) {
        const badge = obtenerElemento('badgeEstadoBloqueo');
        const boton = obtenerElemento('btnToggleEditarCorreo');
        const textarea = obtenerElemento('campoCuerpoCorreo');
        const aviso = obtenerElemento('avisoEdicionCorreo');

        if (estaBloqueado) {
            if (badge) {
                badge.className = 'patch-badge-lock locked';
                badge.innerHTML = '<i class="fa fa-lock"></i> Bloqueado (Plantilla oficial)';
            }
            if (boton) {
                boton.className = 'patch-btn-edit-email';
                boton.innerHTML = '<i class="fa fa-pen-to-square"></i> <span>Editar correo</span>';
                boton.title = 'Habilitar edición para personalizar este mensaje únicamente para este postulante';
            }
            if (textarea) {
                textarea.readOnly = true;
                textarea.classList.add('readonly');
            }
            if (aviso) {
                aviso.innerHTML = '<i class="fa fa-circle-info"></i> <span>Por seguridad, el texto está protegido con la plantilla oficial. Si deseas agregar notas o requerimientos adicionales, pulsa <strong>"Editar correo"</strong>.</span>';
            }
        } else {
            if (badge) {
                badge.className = 'patch-badge-lock unlocked';
                badge.innerHTML = '<i class="fa fa-lock-open"></i> Edición personalizada activa';
            }
            if (boton) {
                boton.className = 'patch-btn-edit-email active';
                boton.innerHTML = '<i class="fa fa-rotate-left"></i> <span>Restablecer plantilla</span>';
                boton.title = 'Descartar cambios personalizados y volver a la plantilla predeterminada';
            }
            if (textarea) {
                textarea.readOnly = false;
                textarea.classList.remove('readonly');
                textarea.focus();
            }
            if (aviso) {
                aviso.innerHTML = '<i class="fa fa-pen" style="color: #2563eb;"></i> <span><strong>Modo de edición activado:</strong> Los cambios que realices aquí se enviarán únicamente a este candidato sin modificar la plantilla general.</span>';
            }
        }
    }

    /**
     * Alterna entre bloqueado (plantilla oficial) y editable (personalizado).
     */
    function toggleEdicionCorreo() {
        const textarea = obtenerElemento('campoCuerpoCorreo');
        if (!textarea) return;

        if (textarea.readOnly) {
            // Desbloquear
            edicionManualActivada = true;
            actualizarEstadoBloqueoVisual(false);
        } else {
            // Re-bloquear y restaurar plantilla
            const postulacion = typeof window.obtenerPostulacionActual === 'function'
                ? window.obtenerPostulacionActual()
                : null;
            if (!postulacion) return;

            const selectorFecha = obtenerElemento('selectorFechaEntrevista');
            const fechaTexto = (selectorFecha && selectorFecha.value)
                ? formatearFechaHoraLegible(selectorFecha.value)
                : '';

            const plantillaActual = (cachePlantillas && cachePlantillas[tipoActual]) ? cachePlantillas[tipoActual] : null;
            if (plantillaActual) {
                textarea.value = construirMensaje(plantillaActual.cuerpo, postulacion, fechaTexto);
            }

            edicionManualActivada = false;
            actualizarEstadoBloqueoVisual(true);
        }
    }

    /**
     * Maneja el cambio de fecha y hora de la entrevista acordada.
     */
    function initSelectorFecha() {
        const selectorFecha = obtenerElemento('selectorFechaEntrevista');
        if (!selectorFecha) return;

        selectorFecha.addEventListener('change', function () {
            const fechaValor = this.value;
            const fechaLegible = formatearFechaHoraLegible(fechaValor);

            const postulacion = typeof window.obtenerPostulacionActual === 'function'
                ? window.obtenerPostulacionActual()
                : null;

            if (!postulacion) return;

            const textarea = obtenerElemento('campoCuerpoCorreo');
            if (!textarea) return;

            // Si el texto está en modo plantilla o no ha sido editado a mano, regeneramos con la fecha actualizada
            if (!edicionManualActivada && cachePlantillas && cachePlantillas[tipoActual]) {
                textarea.value = construirMensaje(cachePlantillas[tipoActual].cuerpo, postulacion, fechaLegible);
            } else {
                // Si ya fue editado manualmente, intentamos reemplazar la línea de cita o avisar
                const regexCita = /(📅\s*Cita de entrevista:\s*)([^\n\r]+)/i;
                if (regexCita.test(textarea.value)) {
                    textarea.value = textarea.value.replace(regexCita, '$1' + fechaLegible);
                }
            }
        });
    }

    /**
     * Envía el correo mediante la API backend.
     */
    async function enviarCorreoCandidato() {
        const postulacion = typeof window.obtenerPostulacionActual === 'function'
            ? window.obtenerPostulacionActual()
            : null;

        if (!postulacion || !postulacion.id) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('No se encontró la postulación activa.', true);
            }
            return;
        }

        const campoAsunto = obtenerElemento('campoAsuntoCorreo');
        const campoCuerpo = obtenerElemento('campoCuerpoCorreo');
        const selectorFecha = obtenerElemento('selectorFechaEntrevista');
        const checkActualizar = obtenerElemento('checkActualizarEstado');
        const btnEnviar = obtenerElemento('btnEnviarCorreoConfirmar');

        const asunto = campoAsunto ? campoAsunto.value.trim() : '';
        const cuerpo = campoCuerpo ? campoCuerpo.value.trim() : '';
        const fechaEntrevista = (tipoActual === 'positivo' && selectorFecha) ? selectorFecha.value : '';
        const actualizarEstado = checkActualizar && checkActualizar.checked ? '1' : '0';

        if (!asunto) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('El asunto del correo no puede estar vacío.', true);
            }
            if (campoAsunto) campoAsunto.focus();
            return;
        }

        if (!cuerpo) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('El contenido del mensaje no puede estar vacío.', true);
            }
            if (campoCuerpo) campoCuerpo.focus();
            return;
        }

        if (tipoActual === 'positivo' && !fechaEntrevista) {
            const continuar = confirm('No has seleccionado fecha y hora en el selector de la cita. ¿Deseas enviar el correo de todas formas?');
            if (!continuar) {
                if (selectorFecha) selectorFecha.focus();
                return;
            }
        }

        // Estado de carga en el botón
        if (btnEnviar) {
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span>Enviando correo...</span>';
        }

        try {
            const formData = new FormData();
            formData.append('id_postulacion', postulacion.id);
            formData.append('tipo_correo', tipoActual);
            formData.append('asunto', asunto);
            formData.append('cuerpo', cuerpo);
            formData.append('fecha_entrevista', fechaEntrevista);
            formData.append('actualizar_estado', actualizarEstado);

            const res = await fetch('api/enviar_correo_postulacion.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (data && data.ok) {
                cerrarModalEnviarCorreo();

                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast(data.mensaje || 'Correo enviado exitosamente.', false);
                }

                // Actualizar la postulación actual en memoria
                if (data.datos) {
                    if (data.datos.estado) {
                        postulacion.estado = data.datos.estado;
                        if (typeof window.actualizarEstadoEnListado === 'function') {
                            window.actualizarEstadoEnListado(postulacion.id, data.datos.estado);
                        }
                        if (typeof window.actualizarSelectorEstado === 'function') {
                            // Sincronizar el selector de botones de estado en el modal
                            document.querySelectorAll('.patch-status-option').forEach(function (btn) {
                                btn.classList.toggle('active', btn.dataset.estado === data.datos.estado);
                            });
                        }
                    }

                    postulacion.tipo_ultimo_correo = tipoActual;
                    postulacion.fecha_ultimo_correo = data.datos.fecha_ultimo_correo || new Date().toISOString();

                    if (data.datos.comentario_seguimiento) {
                        postulacion.comentario_seguimiento = data.datos.comentario_seguimiento;
                        const campoComentario = obtenerElemento('comentarioSeguimiento');
                        if (campoComentario) {
                            campoComentario.value = data.datos.comentario_seguimiento;
                        }
                    }

                    // Actualizar banner de último correo en el modal
                    const bannerUltimo = obtenerElemento('bannerUltimoCorreo');
                    const textoUltimo = obtenerElemento('textoUltimoCorreo');
                    if (bannerUltimo && textoUltimo) {
                        const tipoTexto = tipoActual === 'positivo' ? 'Positivo / Cita' : 'Negativo / Descarte';
                        textoUltimo.textContent = 'Último correo enviado: ' + tipoTexto + ' (Ahora mismo)';
                        bannerUltimo.className = 'patch-mail-status-banner has-email';
                        bannerUltimo.style.display = 'flex';
                    }
                }
            } else {
                throw new Error((data && data.mensaje) ? data.mensaje : 'No se pudo enviar el correo.');
            }

        } catch (error) {
            console.error('Error al enviar correo:', error);
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Error: ' + error.message, true);
            }
        } finally {
            if (btnEnviar) {
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="fa fa-paper-plane"></i> <span>Enviar correo</span>';
            }
        }
    }

    // Inicialización de eventos al cargar el DOM
    document.addEventListener('DOMContentLoaded', function () {
        initSelectorFecha();

        // Cerrar con clic en el backdrop del modal de correo
        const modal = obtenerElemento('modalEnviarCorreo');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    cerrarModalEnviarCorreo();
                }
            });
        }
    });

    // Exponer funciones necesarias globalmente
    window.abrirModalEnviarCorreo = abrirModalEnviarCorreo;
    window.cerrarModalEnviarCorreo = cerrarModalEnviarCorreo;
    window.toggleEdicionCorreo = toggleEdicionCorreo;
    window.enviarCorreoCandidato = enviarCorreoCandidato;

})(window, document);
