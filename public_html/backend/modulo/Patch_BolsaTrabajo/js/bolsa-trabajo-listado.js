// ==========================
// Listado de Vacantes
// ==========================


// ==========================
// Crear tarjeta de vacante
// ==========================

function crearTarjeta(vacante) {

    const tarjeta = document.createElement('div');

    tarjeta.className = 'patch-vacancy-card';

    tarjeta.dataset.id = vacante.id;
    tarjeta.dataset.token = vacante.token;
    tarjeta.dataset.status = obtenerEstado(vacante);
    tarjeta.dataset.title = vacante.titulo || '';
    tarjeta.dataset.date = vacante.fecha_publicacion || '';


    const activo = Number(vacante.activo) === 1;
    const archivada = Number(vacante.archivada) === 1;


    let estadoClase = 'inactive';
    let estadoTexto = 'Inactiva';
    let metaTexto = 'No recibe postulaciones';
    let metaIcono = 'fa-pause-circle';


    if (archivada) {

        estadoClase = 'archived';
        estadoTexto = 'Archivada';
        metaTexto = 'Vacante archivada';
        metaIcono = 'fa-archive';

    } else if (activo) {

        estadoClase = 'active';
        estadoTexto = 'Activa';
        metaTexto = 'Recibiendo postulaciones';
        metaIcono = 'fa-circle';

    }


    const descripcion =
        obtenerResumen(vacante.lo_que_se_ofrece);


    tarjeta.innerHTML = `

        <div class="patch-vacancy-top">

            <h3 class="patch-vacancy-title">

                ${escaparHTML(vacante.titulo)}

            </h3>

            <span class="patch-status ${estadoClase}">

                ${estadoTexto}

            </span>

        </div>


        <div class="patch-vacancy-meta">

            <span>

                <i class="fa fa-calendar"></i>

                Publicada:
                ${formatearFecha(vacante.fecha_publicacion)}

            </span>


            <span>

                <i class="fa ${metaIcono}"></i>

                ${metaTexto}

            </span>

        </div>


        <div class="patch-vacancy-description">

            ${escaparHTML(descripcion)}

        </div>


        <div class="patch-vacancy-actions">

            <button
                type="button"
                class="patch-action-btn edit"
                data-action="edit">

                <i class="fa fa-pencil"></i>

                Editar

            </button>


            ${archivada ? '' : `

            <button
                type="button"
                class="patch-action-btn archive"
                data-action="archive">

                <i class="fa fa-archive"></i>

                Archivar

            </button>

            `}

        </div>`;

    return tarjeta;
}


// ==========================
// Renderizar listado de
// vacantes activas/inactivas
// ==========================

function renderizarVacantes() {

    const texto =
        buscarVacante.value.toLowerCase().trim();


    const estado =
        filtroEstado.value;


    let resultado =
        vacantes.filter(function(vacante) {

            if (Number(vacante.archivada) === 1) {

                return false;

            }


            const titulo =
                (vacante.titulo || '').toLowerCase();


            const coincideTexto =
                !texto || titulo.includes(texto);


            let coincideEstado = true;


            const estadoVacante =
                obtenerEstado(vacante);


            if (estado === 'activas') {

                coincideEstado =
                    estadoVacante === 'active';

            }


            if (estado === 'inactivas') {

                coincideEstado =
                    estadoVacante === 'inactive';

            }


            return coincideTexto && coincideEstado;

        });


    // ==========================
    // Ordenar resultados
    // ==========================

    resultado.sort(function(a, b) {

        if (ordenVacantes.value === 'az') {

            return (a.titulo || '')
                .localeCompare(
                    b.titulo || '',
                    'es'
                );

        }


        const fechaA =
            new Date(
                (a.fecha_publicacion || '')
                    .replace(' ', 'T')
            );


        const fechaB =
            new Date(
                (b.fecha_publicacion || '')
                    .replace(' ', 'T')
            );


        if (ordenVacantes.value === 'antiguas') {

            return fechaA - fechaB;

        }


        return fechaB - fechaA;

    });


    // ==========================
    // Renderizar
    // ==========================

    listaVacantes.innerHTML = '';


    if (resultado.length === 0) {

        estadoVacio.style.display = 'block';

        return;

    }


    estadoVacio.style.display = 'none';


    resultado.forEach(function(vacante) {

        listaVacantes.appendChild(
            crearTarjeta(vacante)
        );

    });


    // ==========================
    // Acción: Editar
    // ==========================

    document
        .querySelectorAll('.patch-action-btn.edit')
        .forEach(function(btn) {

            btn.addEventListener(
                'click',
                function(e) {

                    const id =
                        e.currentTarget
                            .closest('.patch-vacancy-card')
                            .dataset.id;


                    const vacante =
                        vacantes.find(function(v) {

                            return v.id == id;

                        });


                    if (vacante) {

                        abrirModalEditar(vacante);

                    }

                }
            );

        });


    // ==========================
    // Acción: Archivar
    // ==========================

    document
        .querySelectorAll('.patch-action-btn.archive')
        .forEach(function(btn) {

            btn.addEventListener(
                'click',
                function(e) {

                    const id =
                        e.currentTarget
                            .closest('.patch-vacancy-card')
                            .dataset.id;


                    archivarVacante(id);

                }
            );

        });

}


// ==========================
// Crear tarjeta de vacante
// archivada
// ==========================

function crearTarjetaArchivada(vacante) {

    const tarjeta =
        document.createElement('div');


    tarjeta.className =
        'patch-vacancy-card';


    tarjeta.dataset.id =
        vacante.id;


    tarjeta.dataset.token =
        vacante.token;


    tarjeta.innerHTML = `

        <div class="patch-vacancy-top">

            <h3 class="patch-vacancy-title">

                ${escaparHTML(vacante.titulo)}

            </h3>

            <span class="patch-status archived">

                Archivada

            </span>

        </div>


        <div class="patch-vacancy-meta">

            <span>

                <i class="fa fa-calendar"></i>

                Publicada:
                ${formatearFecha(vacante.fecha_publicacion)}

            </span>


            <span>

                <i class="fa fa-archive"></i>

                Vacante archivada

            </span>

        </div>


        <div class="patch-vacancy-description">

            ${escaparHTML(
                obtenerResumen(
                    vacante.lo_que_se_ofrece
                )
            )}

        </div>


        <div class="patch-vacancy-actions">

            <button
                type="button"
                class="patch-action-btn edit"
                data-action="edit">

                <i class="fa fa-pencil"></i>

                Editar

            </button>


            <button
                type="button"
                class="patch-action-btn unarchive"
                data-action="unarchive">

                <i class="fa fa-archive"></i>

                Desarchivar

            </button>

        </div>`;

    return tarjeta;
}


// ==========================
// Renderizar listado de
// vacantes archivadas
// ==========================

function renderizarVacantesArchivadas() {

    listaVacantesArchivadas.innerHTML = '';


    if (vacantesArchivadas.length === 0) {

        estadoVacioArchivadas.style.display = 'block';

        return;

    }


    estadoVacioArchivadas.style.display = 'none';


    vacantesArchivadas.forEach(function(vacante) {

        listaVacantesArchivadas.appendChild(
            crearTarjetaArchivada(vacante)
        );

    });


    // ==========================
    // Acción: Editar
    // ==========================

    document
        .querySelectorAll('.patch-action-btn.edit')
        .forEach(function(btn) {

            btn.addEventListener(
                'click',
                function(e) {

                    const id =
                        e.currentTarget
                            .closest('.patch-vacancy-card')
                            .dataset.id;


                    const vacante =
                        vacantes.find(function(v) {

                            return v.id == id;

                        });


                    if (vacante) {

                        abrirModalEditar(vacante);

                    }

                }
            );

        });


    // ==========================
    // Acción: Archivar
    // ==========================

    document
        .querySelectorAll('.patch-action-btn.archive')
        .forEach(function(btn) {

            btn.addEventListener(
                'click',
                function(e) {

                    const id =
                        e.currentTarget
                            .closest('.patch-vacancy-card')
                            .dataset.id;


                    archivarVacante(id);

                }
            );

        });

}


// ==========================
// Actualizar contadores
// de vacantes
// ==========================

function actualizarContadores() {

    let activas = 0;

    let inactivas = 0;

    let total = 0;


    vacantes.forEach(function(vacante) {

        if (Number(vacante.archivada) === 1) {

            return;

        }


        total++;


        if (Number(vacante.activo) === 1) {

            activas++;

        } else {

            inactivas++;

        }

    });


    contadorActivas.textContent =
        activas;


    contadorInactivas.textContent =
        inactivas;


    contadorTotal.textContent =
        total;

}


// ==========================
// Eventos de filtros
// ==========================


// Buscar vacante en tiempo real

buscarVacante.addEventListener(
    'input',
    function() {

        renderizarVacantes();

    }
);


// Filtrar por estado

filtroEstado.addEventListener(
    'change',
    function() {

        renderizarVacantes();

    }
);


// Ordenar vacantes

ordenVacantes.addEventListener(
    'change',
    function() {

        renderizarVacantes();

    }
);


// Limpiar filtros

btnLimpiarFiltros.addEventListener(
    'click',
    function() {

        buscarVacante.value = '';

        filtroEstado.value = 'todas';

        ordenVacantes.value = 'recientes';

        renderizarVacantes();

    }
);