// ==========================
// API de Bolsa de Trabajo
// ==========================

// URL del controlador PHP
const URL_CONTROLADOR = 'Admin_BolsaTrabajo.php';

// ==========================
// Función genérica para hacer peticiones AJAX
// ==========================
async function peticion(accion, datos = null) {
    let url;

    // Acciones que van por listar.php con parámetro
    if (accion === 'listar' || accion === 'listarArchivadas') {
        url = 'api/listar.php?accion=' + accion;
    } else {
        // Acciones que tienen su propio archivo (crear.php, editar.php, etc.)
        url = 'api/' + accion + '.php';
    }

    const opciones = {
        method: datos ? 'POST' : 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (datos) opciones.body = datos;

    const respuesta = await fetch(url, opciones);
    const raw = await respuesta.text(); // obtenemos texto crudo
    console.log("Respuesta cruda del backend:", raw); // lo mostramos en consola

    try {
        return JSON.parse(raw); // intentamos parsear
    } catch (e) {
        console.error("Error al parsear JSON:", e);
        throw e;
    }
}

// ==========================
// Cargar vacantes activas/inactivas
// ==========================
async function cargarVacantes() {
    listaVacantes.innerHTML = `
    <div class="patch-loading">
        <i class="fa fa-spinner fa-spin"></i>
        Cargando vacantes...
    </div>`;

    try {
        const respuesta = await peticion('listar');
        if (!respuesta.ok) throw new Error(respuesta.mensaje || 'No fue posible cargar las vacantes.');

        vacantes = Array.isArray(respuesta.datos) ? respuesta.datos : [];
        actualizarContadores();
        renderizarVacantes();

    } catch (error) {
        console.error(error);
        listaVacantes.innerHTML = `
        <div class="patch-loading">
            <i class="fa fa-exclamation-triangle"></i>
            No fue posible cargar las vacantes.
        </div>`;
        mostrarToast('No fue posible cargar las vacantes.', true);
    }
}

// ==========================
// Cargar vacantes archivadas
// ==========================
async function cargarVacantesArchivadas() {
    listaVacantesArchivadas.innerHTML = `
    <div class="patch-loading">
        <i class="fa fa-spinner fa-spin"></i>
        Cargando vacantes archivadas...
    </div>`;

    try {
        const respuesta = await peticion('listarArchivadas');
        if (!respuesta.ok) throw new Error(respuesta.mensaje || 'No fue posible cargar las vacantes archivadas.');

        vacantesArchivadas = Array.isArray(respuesta.datos) ? respuesta.datos : [];
        renderizarVacantesArchivadas();

    } catch (error) {
        console.error(error);
        listaVacantesArchivadas.innerHTML = `
        <div class="patch-loading">
            <i class="fa fa-exclamation-triangle"></i>
            No fue posible cargar las vacantes archivadas.
        </div>`;
        mostrarToast('No fue posible cargar las vacantes archivadas.', true);
    }
}
