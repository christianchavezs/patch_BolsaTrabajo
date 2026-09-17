// ==========================
// Referencias de elementos HTML
// ==========================

const patchToast = document.getElementById('patchToast');
const patchToastMensaje = document.getElementById('patchToastMensaje');

const modalConfirmarArchivo = document.getElementById('modalConfirmarArchivo');
const btnCerrarConfirmarArchivo = document.getElementById('btnCerrarConfirmarArchivo');
const btnCancelarConfirmarArchivo = document.getElementById('btnCancelarConfirmarArchivo');
const btnAceptarConfirmarArchivo = document.getElementById('btnAceptarConfirmarArchivo');


// ==========================
// Utilidades de Interfaz
// ==========================

// Mostrar notificación tipo toast
function mostrarToast(mensaje, error = false) {

    if (!patchToast || !patchToastMensaje) {
        console.warn('No se encontró el elemento del toast.');
        return;
    }

    clearTimeout(toastTimeout);

    patchToastMensaje.textContent = mensaje;

    patchToast.classList.toggle('error', error);

    const icono = patchToast.querySelector('i');

    if (icono) {
        icono.className = error
            ? 'fa fa-exclamation-circle'
            : 'fa fa-check-circle';
    }

    patchToast.classList.add('show');

    toastTimeout = setTimeout(function() {
        patchToast.classList.remove('show');
    }, 3500);
}


// ==========================
// Formatear fecha para tarjetas
// ==========================

function formatearFecha(fecha) {

    if (!fecha) {
        return 'Sin fecha';
    }

    const fechaObj = new Date(String(fecha).replace(' ', 'T'));

    if (isNaN(fechaObj.getTime())) {
        return fecha;
    }

    return fechaObj.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}


// ==========================
// Formato para input[type=date]
// ==========================

function fechaParaInput(fecha) {

    if (!fecha) {
        return '';
    }

    return String(fecha).substring(0, 10);
}


// ==========================
// Escapar HTML para evitar XSS
// ==========================

function escaparHTML(texto) {

    const div = document.createElement('div');

    div.textContent = texto ?? '';

    return div.innerHTML;
}


// ==========================
// Obtener estado de la vacante
// ==========================

function obtenerEstado(vacante) {

    if (Number(vacante.archivada) === 1) {
        return 'archived';
    }

    if (Number(vacante.activo) === 1) {
        return 'active';
    }

    return 'inactive';
}


// ==========================
// Resumen de texto
// ==========================

function obtenerResumen(texto) {

    if (!texto) {
        return 'Sin descripción disponible.';
    }

    const limpio = String(texto)
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (limpio.length <= 180) {
        return limpio;
    }

    return limpio.substring(0, 180) + '...';
}


// ==========================
// Confirmación de archivado
// ==========================

let vacantePendienteArchivar = null;


// ==========================
// Abrir modal de confirmación
// ==========================

function abrirModalConfirmarArchivo(id) {

    if (!modalConfirmarArchivo) {
        console.error('No se encontró el modal de confirmación de archivado.');
        mostrarToast('No se encontró el modal de confirmación.', true);
        return;
    }

    vacantePendienteArchivar = id;

    modalConfirmarArchivo.classList.add('active');

    modalConfirmarArchivo.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}


// ==========================
// Cerrar modal de confirmación
// ==========================

function cerrarModalConfirmarArchivo() {

    if (!modalConfirmarArchivo) {
        return;
    }

    modalConfirmarArchivo.classList.remove('active');

    modalConfirmarArchivo.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';

    vacantePendienteArchivar = null;
}


// ==========================
// Solicitar confirmación de archivado
// ==========================

function archivarVacante(id) {

    abrirModalConfirmarArchivo(id);
}


// ==========================
// Botón cerrar del modal
// ==========================

if (btnCerrarConfirmarArchivo) {

    btnCerrarConfirmarArchivo.addEventListener('click', function() {
        cerrarModalConfirmarArchivo();
    });

}


// ==========================
// Botón cancelar
// ==========================

if (btnCancelarConfirmarArchivo) {

    btnCancelarConfirmarArchivo.addEventListener('click', function() {
        cerrarModalConfirmarArchivo();
    });

}


// ==========================
// Cerrar al hacer clic fuera
// ==========================

if (modalConfirmarArchivo) {

    modalConfirmarArchivo.addEventListener('click', function(e) {

        if (e.target === modalConfirmarArchivo) {
            cerrarModalConfirmarArchivo();
        }

    });

}


// ==========================
// Cerrar con la tecla Escape
// ==========================

document.addEventListener('keydown', function(e) {

    if (
        e.key === 'Escape' &&
        modalConfirmarArchivo &&
        modalConfirmarArchivo.classList.contains('active')
    ) {
        cerrarModalConfirmarArchivo();
    }

});


// ==========================
// Confirmar archivado
// ==========================

if (btnAceptarConfirmarArchivo) {

    btnAceptarConfirmarArchivo.addEventListener('click', async function() {

        if (vacantePendienteArchivar === null) {

            cerrarModalConfirmarArchivo();

            return;
        }

        const id = vacantePendienteArchivar;

        const datos = new FormData();

        datos.append('id', id);
        datos.append('accion', 'archivar');

        btnAceptarConfirmarArchivo.disabled = true;

        btnAceptarConfirmarArchivo.innerHTML = `
            <i class="fa fa-spinner fa-spin"></i>
            Archivando...
        `;

        try {

            const respuesta = await peticion('archivo', datos);

            if (!respuesta.ok) {

                throw new Error(
                    respuesta.mensaje ||
                    'No fue posible archivar la vacante.'
                );

            }

            cerrarModalConfirmarArchivo();

            // Recargar el listado principal.
            // El backend ya excluye las vacantes archivadas.
            await cargarVacantes();

            mostrarToast(
                respuesta.mensaje || 'La vacante fue archivada correctamente.'
            );

        } catch (error) {

            console.error('Error al archivar la vacante:', error);

            mostrarToast(
                error.message || 'No fue posible archivar la vacante.',
                true
            );

        } finally {

            btnAceptarConfirmarArchivo.disabled = false;

            btnAceptarConfirmarArchivo.innerHTML = `
                <i class="fa fa-check"></i>
                Aceptar
            `;

        }

    });

}