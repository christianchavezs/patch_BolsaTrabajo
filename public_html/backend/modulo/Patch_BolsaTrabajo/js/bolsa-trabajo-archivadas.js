
// ==========================
// Vacantes Archivadas
// ==========================


// ==========================
// Referencias del modal
// ==========================

const modalConfirmarDesarchivar =
    document.getElementById('modalConfirmarDesarchivar');

const btnCerrarConfirmarDesarchivar =
    document.getElementById('btnCerrarConfirmarDesarchivar');

const btnCancelarConfirmarDesarchivar =
    document.getElementById('btnCancelarConfirmarDesarchivar');

const btnAceptarConfirmarDesarchivar =
    document.getElementById('btnAceptarConfirmarDesarchivar');

let vacantePendienteDesarchivar = null;


// ==========================
// Abrir modal de vacantes archivadas
// ==========================

function abrirModalArchivadas() {

    modalArchivadas.classList.add('active');

    modalArchivadas.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';

    cargarVacantesArchivadas();

}


// ==========================
// Cerrar modal de vacantes archivadas
// ==========================

function cerrarModalArchivadas() {

    modalArchivadas.classList.remove('active');

    modalArchivadas.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';

}


// ==========================
// Abrir modal de confirmación
// de desarchivado
// ==========================

function abrirModalConfirmarDesarchivar(id) {

    if (!modalConfirmarDesarchivar) {

        console.error(
            'No se encontró el modal de confirmación de desarchivado.'
        );

        return;
    }

    vacantePendienteDesarchivar = id;

    modalConfirmarDesarchivar.classList.add('active');

    modalConfirmarDesarchivar.setAttribute(
        'aria-hidden',
        'false'
    );

    document.body.style.overflow = 'hidden';

}


// ==========================
// Cerrar modal de confirmación
// de desarchivado
// ==========================

function cerrarModalConfirmarDesarchivar() {

    if (!modalConfirmarDesarchivar) {
        return;
    }

    modalConfirmarDesarchivar.classList.remove('active');

    modalConfirmarDesarchivar.setAttribute(
        'aria-hidden',
        'true'
    );

    document.body.style.overflow = '';

    vacantePendienteDesarchivar = null;

}


// ==========================
// Solicitar desarchivado
// ==========================

function desarchivarVacante(id) {

    abrirModalConfirmarDesarchivar(id);

}


// ==========================
// Eventos del modal archivadas
// ==========================


// Botón para mostrar archivadas

btnMostrarArchivadas.addEventListener(
    'click',
    function(e) {

        e.preventDefault();

        abrirModalArchivadas();

    }
);


// Botones de cierre

btnCerrarArchivadas.addEventListener(
    'click',
    cerrarModalArchivadas
);

btnCerrarArchivadasFooter.addEventListener(
    'click',
    cerrarModalArchivadas
);


// Cerrar modal al hacer click fuera

modalArchivadas.addEventListener(
    'click',
    function(e) {

        if (e.target === modalArchivadas) {

            cerrarModalArchivadas();

        }

    }
);


// ==========================
// Eventos del modal de
// confirmación de desarchivado
// ==========================


// Botón cerrar

if (btnCerrarConfirmarDesarchivar) {

    btnCerrarConfirmarDesarchivar.addEventListener(
        'click',
        cerrarModalConfirmarDesarchivar
    );

}


// Botón cancelar

if (btnCancelarConfirmarDesarchivar) {

    btnCancelarConfirmarDesarchivar.addEventListener(
        'click',
        cerrarModalConfirmarDesarchivar
    );

}


// Cerrar haciendo click fuera

if (modalConfirmarDesarchivar) {

    modalConfirmarDesarchivar.addEventListener(
        'click',
        function(e) {

            if (e.target === modalConfirmarDesarchivar) {

                cerrarModalConfirmarDesarchivar();

            }

        }
    );

}


// ==========================
// Cerrar con tecla Escape
// ==========================

document.addEventListener(
    'keydown',
    function(e) {

        if (
            e.key === 'Escape' &&
            modalConfirmarDesarchivar &&
            modalConfirmarDesarchivar.classList.contains('active')
        ) {

            cerrarModalConfirmarDesarchivar();

        }

    }
);


// ==========================
// Confirmar desarchivado
// ==========================

if (btnAceptarConfirmarDesarchivar) {

    btnAceptarConfirmarDesarchivar.addEventListener(
        'click',
        async function() {

            if (!vacantePendienteDesarchivar) {

                cerrarModalConfirmarDesarchivar();

                return;
            }

            const id = vacantePendienteDesarchivar;

            const datos = new FormData();

            datos.append('id', id);

            datos.append('accion', 'desarchivar');


            // Deshabilitar botón mientras se procesa

            btnAceptarConfirmarDesarchivar.disabled = true;

            btnAceptarConfirmarDesarchivar.innerHTML = `
                <i class="fa fa-spinner fa-spin"></i>
                Desarchivando...
            `;


            try {

                /*
                 * archivo.php maneja tanto:
                 *
                 * accion = archivar
                 * accion = desarchivar
                 *
                 * Por eso aquí utilizamos:
                 *
                 * peticion('archivo', datos)
                 */

                const respuesta = await peticion(
                    'archivo',
                    datos
                );


                if (!respuesta.ok) {

                    throw new Error(
                        respuesta.mensaje ||
                        'No fue posible desarchivar la vacante.'
                    );

                }


                // Cerrar modal de confirmación

                cerrarModalConfirmarDesarchivar();


                // Cerrar modal de vacantes archivadas

                cerrarModalArchivadas();


                // Recargar listado principal

                await cargarVacantes();


                // Recargar listado de archivadas

                await cargarVacantesArchivadas();


                // Mostrar mensaje

                mostrarToast(
                    respuesta.mensaje ||
                    'La vacante fue desarchivada correctamente.'
                );


            } catch (error) {

                console.error(
                    'Error al desarchivar la vacante:',
                    error
                );

                mostrarToast(
                    error.message ||
                    'No fue posible desarchivar la vacante.',
                    true
                );


            } finally {

                // Restaurar botón

                btnAceptarConfirmarDesarchivar.disabled = false;

                btnAceptarConfirmarDesarchivar.innerHTML = `
                    <i class="fa fa-check"></i>
                    Aceptar
                `;

            }

        }
    );

}


// ==========================
// Acciones sobre vacantes archivadas
// ==========================

listaVacantesArchivadas.addEventListener(
    'click',
    function(e) {

        const boton =
            e.target.closest('.patch-action-btn');


        if (!boton) {
            return;
        }


        const tarjeta =
            boton.closest('.patch-vacancy-card');


        if (!tarjeta) {
            return;
        }


        const id =
            Number(tarjeta.dataset.id);


        const vacante =
            vacantesArchivadas.find(function(item) {

                return Number(item.id) === id;

            });


        if (!vacante) {

            mostrarToast(
                'No se encontró la vacante archivada.',
                true
            );

            return;
        }


        const accion =
            boton.dataset.action;


        // ==========================
        // Editar vacante archivada
        // ==========================

        if (accion === 'edit') {

            cerrarModalArchivadas();

            abrirModalEditar(vacante);

            return;
        }


        // ==========================
        // Desarchivar vacante
        // ==========================

        if (accion === 'unarchive') {

            desarchivarVacante(id);

            return;
        }

    }
);

