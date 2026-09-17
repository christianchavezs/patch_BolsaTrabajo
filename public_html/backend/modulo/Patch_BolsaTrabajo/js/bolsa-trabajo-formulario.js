// ==========================
// Formulario de Vacantes
// ==========================

function abrirModalNueva() {
    modoFormulario = 'crear';
    tituloModal.textContent = 'Nueva vacante';
    formVacante.reset();
    campoId.value = '';
    campoActivo.checked = true;
    modalVacante.classList.add('active');
    modalVacante.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    setTimeout(function() {
        campoTitulo.focus();
    }, 150);
}

function abrirModalEditar(vacante) {
    modoFormulario = 'editar';
    tituloModal.textContent = 'Editar vacante';
    campoId.value = vacante.id;
    campoTitulo.value = vacante.titulo || '';
    campoOferta.value = vacante.lo_que_se_ofrece || '';
    campoRequisitos.value = vacante.requisitos || '';
    campoResponsabilidades.value = vacante.responsabilidades || '';
    campoFechaCierre.value = fechaParaInput(vacante.fecha_cierre);
    campoActivo.checked = Number(vacante.activo) === 1;
    modalVacante.classList.add('active');
    modalVacante.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    setTimeout(function() {
        campoTitulo.focus();
    }, 150);
}


function cerrarModal() {
    modalVacante.classList.remove('active');
    modalVacante.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    formVacante.reset();
    campoId.value = '';
}

// ==========================
// Eventos del formulario
// ==========================

// Abrir modal nueva vacante
btnNuevaVacante.addEventListener('click', function(e) {
    e.preventDefault();
    abrirModalNueva();
});

// Cerrar modal
btnCerrarModal.addEventListener('click', cerrarModal);
btnCancelarModal.addEventListener('click', cerrarModal);

// Cerrar modal al hacer click fuera
modalVacante.addEventListener('click', function(e) {
    if (e.target === modalVacante) cerrarModal();
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modalVacante.classList.contains('active')) cerrarModal();
});

// Envío del formulario
formVacante.addEventListener('submit', async function(e) {
    e.preventDefault();

    const titulo = campoTitulo.value.trim();
    const oferta = campoOferta.value.trim();
    const requisitos = campoRequisitos.value.trim();
    const responsabilidades = campoResponsabilidades.value.trim();

    if (!titulo) {
        mostrarToast('Ingresa el título de la vacante.', true);
        campoTitulo.focus();
        return;
    }
    if (!oferta) {
        mostrarToast('Ingresa lo que se ofrece.', true);
        campoOferta.focus();
        return;
    }
    if (!requisitos) {
        mostrarToast('Ingresa los requisitos.', true);
        campoRequisitos.focus();
        return;
    }
    if (!responsabilidades) {
        mostrarToast('Ingresa las responsabilidades.', true);
        campoResponsabilidades.focus();
        return;
    }

    const datos = new FormData();
    datos.append('titulo', titulo);
    datos.append('lo_que_se_ofrece', oferta);
    datos.append('requisitos', requisitos);
    datos.append('responsabilidades', responsabilidades);
    datos.append('fecha_cierre', campoFechaCierre.value);
    datos.append('activo', campoActivo.checked ? '1' : '0');

    let accion = 'crear';
    if (modoFormulario === 'editar') {
        accion = 'editar';
        datos.append('id', campoId.value);
    }

    btnGuardarVacante.disabled = true;
    btnGuardarVacante.innerHTML = `
        <i class="fa fa-spinner fa-spin"></i>
        Guardando...`;

    try {
        const respuesta = await peticion(accion, datos);
        if (!respuesta.ok) throw new Error(respuesta.mensaje || 'No fue posible guardar la vacante.');

        cerrarModal();
        await cargarVacantes();
        mostrarToast(respuesta.mensaje);

    } catch (error) {
        console.error(error);
        mostrarToast(error.message || 'Ocurrió un error al guardar.', true);

    } finally {
        btnGuardarVacante.disabled = false;
        btnGuardarVacante.innerHTML = `
            <i class="fa fa-save"></i>
            Guardar vacante`;
    }
});
