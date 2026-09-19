// ==========================
// Formulario de Vacantes
// ==========================


// ==========================
// Estado de la imagen
// ==========================

let imagenSeleccionada = null;

let imagenExistente = '';

let eliminarImagenExistente = false;


// ==========================
// Elementos de imagen
// ==========================

const campoSoloImagen =
    document.getElementById('campoSoloImagen');

const campoImagen =
    document.getElementById('campoImagen');

const imagenDropzone =
    document.getElementById('imagenDropzone');

const btnSeleccionarImagen =
    document.getElementById('btnSeleccionarImagen');

const btnQuitarImagen =
    document.getElementById('btnQuitarImagen');

const imagenPreviewContainer =
    document.getElementById('imagenPreviewContainer');

const campoImagenPreview =
    document.getElementById('campoImagenPreview');

const campoImagenNombre =
    document.getElementById('campoImagenNombre');

const requiredImagen =
    document.getElementById('requiredImagen');


// ==========================
// Abrir modal nueva vacante
// ==========================

function abrirModalNueva() {

    modoFormulario = 'crear';

    tituloModal.textContent = 'Nueva vacante';

    formVacante.reset();

    campoId.value = '';


    // Los campos contenteditable no se limpian con form.reset()

    campoOferta.innerHTML = '';

    campoRequisitos.innerHTML = '';

    campoResponsabilidades.innerHTML = '';


    campoActivo.checked = true;


    // Reiniciar estado de imagen

    imagenSeleccionada = null;

    imagenExistente = '';

    eliminarImagenExistente = false;


    limpiarVistaImagen();


    // Estado inicial del toggle

    campoSoloImagen.checked = false;

    actualizarEstadoSoloImagen();


    modalVacante.classList.add('active');

    modalVacante.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';


    setTimeout(function() {

        campoTitulo.focus();

    }, 150);

}


// ==========================
// Abrir modal editar
// ==========================

function abrirModalEditar(vacante) {

    modoFormulario = 'editar';

    tituloModal.textContent = 'Editar vacante';


    campoId.value = vacante.id;

    campoTitulo.value = vacante.titulo || '';


    // Los editores son contenteditable,
    // por lo que utilizamos innerHTML
    // para conservar el formato guardado.

    campoOferta.innerHTML =
        vacante.lo_que_se_ofrece || '';

    campoRequisitos.innerHTML =
        vacante.requisitos || '';

    campoResponsabilidades.innerHTML =
        vacante.responsabilidades || '';


    campoFechaCierre.value =
        fechaParaInput(vacante.fecha_cierre);


    campoActivo.checked =
        Number(vacante.activo) === 1;


    // ==========================
    // Estado de imagen
    // ==========================

    imagenSeleccionada = null;

    imagenExistente =
        vacante.url_imagen || '';

    eliminarImagenExistente = false;


    // Mostrar imagen existente si existe

    if (imagenExistente) {

        mostrarVistaImagenExistente(
            imagenExistente
        );

    } else {

        limpiarVistaImagen();

    }


    // Estado del toggle Solo Imagen

    campoSoloImagen.checked =
        Number(vacante.solo_imagen) === 1;


    actualizarEstadoSoloImagen();


    modalVacante.classList.add('active');

    modalVacante.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';


    setTimeout(function() {

        campoTitulo.focus();

    }, 150);

}


// ==========================
// Cerrar modal
// ==========================

function cerrarModal() {

    modalVacante.classList.remove('active');

    modalVacante.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';


    formVacante.reset();

    campoId.value = '';


    // Limpiar manualmente los campos contenteditable

    campoOferta.innerHTML = '';

    campoRequisitos.innerHTML = '';

    campoResponsabilidades.innerHTML = '';


    // Reiniciar imagen

    imagenSeleccionada = null;

    imagenExistente = '';

    eliminarImagenExistente = false;


    limpiarVistaImagen();

    campoSoloImagen.checked = false;

    actualizarEstadoSoloImagen();

}


// ==========================
// Estado del modo Solo Imagen
// ==========================

function actualizarEstadoSoloImagen() {

    const soloImagen =
        campoSoloImagen.checked;


    const editores = [

        campoOferta,

        campoRequisitos,

        campoResponsabilidades

    ];


    // ==========================
    // Habilitar / deshabilitar editores
    // ==========================

    editores.forEach(function(editor) {

        if (!editor) {
            return;
        }


        editor.contentEditable =
            soloImagen ? 'false' : 'true';


        const contenedor =
            editor.closest('.patch-editor');


        if (contenedor) {

            contenedor.classList.toggle(
                'disabled',
                soloImagen
            );


            const botones =
                contenedor.querySelectorAll(
                    '.patch-editor-btn'
                );


            botones.forEach(function(boton) {

                boton.disabled = soloImagen;

            });

        }

    });


    // ==========================
    // Indicadores de obligatoriedad
    // ==========================

    const requiredOferta =
        document.getElementById(
            'requiredOferta'
        );

    const requiredRequisitos =
        document.getElementById(
            'requiredRequisitos'
        );

    const requiredResponsabilidades =
        document.getElementById(
            'requiredResponsabilidades'
        );


    if (requiredOferta) {

        requiredOferta.style.display =
            soloImagen ? 'none' : 'inline';

    }


    if (requiredRequisitos) {

        requiredRequisitos.style.display =
            soloImagen ? 'none' : 'inline';

    }


    if (requiredResponsabilidades) {

        requiredResponsabilidades.style.display =
            soloImagen ? 'none' : 'inline';

    }


    // ==========================
    // Imagen obligatoria
    // ==========================

    if (requiredImagen) {

        requiredImagen.style.display =
            soloImagen ? 'inline' : 'none';

    }

}


// ==========================
// Mostrar vista previa de imagen nueva
// ==========================

function mostrarVistaImagenNueva(file) {

    if (!file) {
        return;
    }


    imagenSeleccionada = file;

    eliminarImagenExistente = false;


    const url =
        URL.createObjectURL(file);


    campoImagenPreview.src = url;

    campoImagenPreview.alt =
        'Vista previa de la imagen seleccionada';


    campoImagenNombre.textContent =
        file.name;


    imagenPreviewContainer.hidden = false;


    imagenDropzone.style.display = 'none';

}


// ==========================
// Mostrar imagen existente
// ==========================

function mostrarVistaImagenExistente(urlImagen) {

    if (!urlImagen) {

        limpiarVistaImagen();

        return;

    }


    let urlFinal = urlImagen;


    try {

        urlFinal =
            new URL(
                urlImagen,
                window.location.href
            ).href;

    } catch (error) {

        console.warn(
            'No fue posible construir la URL de la imagen:',
            error
        );

    }


    campoImagenPreview.src =
        urlFinal;

    campoImagenPreview.alt =
        'Imagen actual de la vacante';


    campoImagenNombre.textContent =
        'Imagen actual';


    imagenPreviewContainer.hidden =
        false;


    imagenDropzone.style.display =
        'none';

}


// ==========================
// Limpiar imagen
// ==========================

function limpiarVistaImagen() {

    campoImagenPreview.src = '';

    campoImagenPreview.alt = '';


    campoImagenNombre.textContent = '';


    imagenPreviewContainer.hidden =
        true;


    imagenDropzone.style.display =
        '';


    if (campoImagen) {

        campoImagen.value = '';

    }

}


// ==========================
// Seleccionar imagen
// ==========================

function seleccionarImagen(file) {

    if (!file) {
        return;
    }


    // ==========================
    // Validar tipo
    // ==========================

    const tiposPermitidos = [

        'image/jpeg',

        'image/png',

        'image/webp'

    ];


    if (!tiposPermitidos.includes(file.type)) {

        mostrarToast(
            'La imagen debe estar en formato JPG, PNG o WEBP.',
            true
        );

        return;

    }


    // ==========================
    // Validar que sea una sola imagen
    // ==========================

    imagenSeleccionada = file;


    mostrarVistaImagenNueva(file);

}


// ==========================
// Botón seleccionar imagen
// ==========================

btnSeleccionarImagen.addEventListener(
    'click',
    function(e) {

        e.preventDefault();

        campoImagen.click();

    }
);


// ==========================
// Input de imagen
// ==========================

campoImagen.addEventListener(
    'change',
    function() {

        if (
            this.files &&
            this.files.length > 0
        ) {

            seleccionarImagen(
                this.files[0]
            );

        }

    }
);


// ==========================
// Arrastrar imagen
// ==========================

imagenDropzone.addEventListener(
    'dragover',
    function(e) {

        e.preventDefault();

        e.stopPropagation();


        imagenDropzone.classList.add(
            'dragover'
        );

    }
);


imagenDropzone.addEventListener(
    'dragleave',
    function(e) {

        e.preventDefault();

        e.stopPropagation();


        imagenDropzone.classList.remove(
            'dragover'
        );

    }
);


imagenDropzone.addEventListener(
    'drop',
    function(e) {

        e.preventDefault();

        e.stopPropagation();


        imagenDropzone.classList.remove(
            'dragover'
        );


        const archivos =
            e.dataTransfer.files;


        if (
            !archivos ||
            archivos.length === 0
        ) {

            return;

        }


        // Solo permitimos una imagen

        seleccionarImagen(
            archivos[0]
        );

    }
);


// ==========================
// Quitar imagen
// ==========================

btnQuitarImagen.addEventListener(
    'click',
    function(e) {

        e.preventDefault();


        if (imagenExistente) {

            eliminarImagenExistente = true;

        }


        imagenSeleccionada = null;


        limpiarVistaImagen();

    }
);


// ==========================
// Eventos del formulario
// ==========================

// Abrir modal nueva vacante

btnNuevaVacante.addEventListener(
    'click',
    function(e) {

        e.preventDefault();

        abrirModalNueva();

    }
);


// Cerrar modal

btnCerrarModal.addEventListener(
    'click',
    cerrarModal
);


btnCancelarModal.addEventListener(
    'click',
    cerrarModal
);


// Cerrar modal al hacer click fuera

modalVacante.addEventListener(
    'click',
    function(e) {

        if (e.target === modalVacante) {

            cerrarModal();

        }

    }
);


// Cerrar modal con tecla Escape

document.addEventListener(
    'keydown',
    function(e) {

        if (

            e.key === 'Escape' &&

            modalVacante.classList.contains(
                'active'
            )

        ) {

            cerrarModal();

        }

    }
);


// ==========================
// Cambio de Solo Imagen
// ==========================

campoSoloImagen.addEventListener(
    'change',
    function() {

        actualizarEstadoSoloImagen();

    }
);


// ==========================
// Envío del formulario
// ==========================

formVacante.addEventListener(
    'submit',
    async function(e) {

        e.preventDefault();


        const soloImagen =
            campoSoloImagen.checked;


        const titulo =
            campoTitulo.value.trim();


        // Los campos son contenteditable,
        // por lo que utilizamos innerHTML
        // para conservar negritas, cursivas y listas.

        const oferta =
            campoOferta.innerHTML.trim();

        const requisitos =
            campoRequisitos.innerHTML.trim();

        const responsabilidades =
            campoResponsabilidades.innerHTML.trim();


        // ==========================
        // Validaciones
        // ==========================

        if (!titulo) {

            mostrarToast(
                'Ingresa el título de la vacante.',
                true
            );

            campoTitulo.focus();

            return;

        }


        // ==========================
        // Validar textos solamente
        // cuando NO es Solo Imagen
        // ==========================

        if (!soloImagen) {

            if (!oferta) {

                mostrarToast(
                    'Ingresa lo que se ofrece.',
                    true
                );

                campoOferta.focus();

                return;

            }


            if (!requisitos) {

                mostrarToast(
                    'Ingresa los requisitos.',
                    true
                );

                campoRequisitos.focus();

                return;

            }


            if (!responsabilidades) {

                mostrarToast(
                    'Ingresa las responsabilidades.',
                    true
                );

                campoResponsabilidades.focus();

                return;

            }

        }


        // ==========================
        // Validar imagen en
        // modo Solo Imagen
        // ==========================

        if (
            soloImagen &&
            !imagenSeleccionada &&
            !imagenExistente
        ) {

            mostrarToast(
                'Debes seleccionar una imagen para una vacante de solo imagen.',
                true
            );

            return;

        }


        // ==========================
        // Datos para la API
        // ==========================

        const datos =
            new FormData();


        datos.append(
            'titulo',
            titulo
        );


        datos.append(
            'lo_que_se_ofrece',
            oferta
        );


        datos.append(
            'requisitos',
            requisitos
        );


        datos.append(
            'responsabilidades',
            responsabilidades
        );


        datos.append(
            'fecha_cierre',
            campoFechaCierre.value
        );


        datos.append(
            'activo',
            campoActivo.checked
                ? '1'
                : '0'
        );


        datos.append(
            'solo_imagen',
            soloImagen
                ? '1'
                : '0'
        );


        // ==========================
        // Imagen nueva
        // ==========================

        if (imagenSeleccionada) {

            datos.append(
                'imagen',
                imagenSeleccionada
            );

        }


        // ==========================
        // Eliminar imagen existente
        // ==========================

        if (
            eliminarImagenExistente &&
            !imagenSeleccionada
        ) {

            datos.append(
                'eliminar_imagen',
                '1'
            );

        }


        // ==========================
        // Acción
        // ==========================

        let accion = 'crear';


        if (modoFormulario === 'editar') {

            accion = 'editar';


            datos.append(
                'id',
                campoId.value
            );

        }


        // ==========================
        // Estado de guardado
        // ==========================

        btnGuardarVacante.disabled =
            true;


        btnGuardarVacante.innerHTML = `
            <i class="fa fa-spinner fa-spin"></i>
            Guardando...
        `;


        try {

            const respuesta =
                await peticion(
                    accion,
                    datos
                );


            if (!respuesta.ok) {

                throw new Error(

                    respuesta.mensaje ||

                    'No fue posible guardar la vacante.'

                );

            }


            cerrarModal();


            await cargarVacantes();


            mostrarToast(
                respuesta.mensaje
            );


        } catch (error) {

            console.error(error);


            mostrarToast(

                error.message ||

                'Ocurrió un error al guardar.',

                true

            );


        } finally {

            btnGuardarVacante.disabled =
                false;


            btnGuardarVacante.innerHTML = `
                <i class="fa fa-save"></i>
                Guardar vacante
            `;

        }

    }
);