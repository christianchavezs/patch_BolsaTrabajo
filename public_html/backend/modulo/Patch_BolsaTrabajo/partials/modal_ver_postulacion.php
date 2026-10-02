<div
    class="patch-modal"
    id="modalPostulacion"
    aria-hidden="true"
>

<div
    class="patch-modal-container"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTituloPostulacion"
>

    <div class="patch-modal-header">

        <div class="patch-modal-header-info">

            <h2 id="modalTituloPostulacion">
                Detalle de postulación
            </h2>

            <p id="modalSubtitulo">
                Información del candidato
            </p>

        </div>

        <button
            type="button"
            class="patch-modal-close"
            onclick="cerrarPostulacion()"
            aria-label="Cerrar"
        >
            <i class="fa fa-xmark"></i>
        </button>

    </div>


    <div class="patch-modal-body">

        <div class="patch-profile">

            <div
                class="patch-profile-avatar"
                id="modalAvatar"
            >
                ?
            </div>

            <div>

                <h3 id="modalNombre">
                    Candidato
                </h3>

                <p id="modalCorreo">
                    Sin correo
                </p>

            </div>

        </div>


        <div class="patch-detail-grid">

            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-envelope"></i>
                    Correo electrónico
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleCorreo"
                >
                    Sin correo
                </span>

            </div>


            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-phone"></i>
                    Teléfono
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleTelefono"
                >
                    Sin teléfono
                </span>

            </div>


            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-briefcase"></i>
                    Vacante
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleVacante"
                >
                    Sin vacante
                </span>

            </div>


            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-calendar"></i>
                    Fecha de postulación
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleFecha"
                >
                    Sin fecha
                </span>

            </div>


            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-location-dot"></i>
                    Ubicación
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleUbicacion"
                >
                    No especificada
                </span>

            </div>


            <div class="patch-detail-item">

                <span class="patch-detail-label">
                    <i class="fa fa-building"></i>
                    Modalidad
                </span>

                <span
                    class="patch-detail-value"
                    id="detalleModalidad"
                >
                    No especificada
                </span>

            </div>

        </div>


        <div class="patch-modal-section">

            <h3>
                Comentarios del candidato
            </h3>

            <div
                id="detalleComentarios"
                class="patch-comments"
            >
                Sin comentarios.
            </div>

        </div>


        <div class="patch-modal-section">

            <h3>
                Currículum vitae
            </h3>

            <div class="patch-cv-card">

                <div class="patch-cv-info">

                    <div class="patch-cv-icon">
                        <i class="fa fa-file-pdf"></i>
                    </div>

                    <div>

                        <div
                            class="patch-cv-name"
                            id="detalleCVNombre"
                        >
                            CV.pdf
                        </div>

                        <div class="patch-cv-label">
                            Documento PDF
                        </div>

                    </div>

                </div>


                <a
                    href="#"
                    id="btnVerCV"
                    class="patch-cv-button"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa fa-eye"></i>
                    Ver CV
                </a>

            </div>

        </div>


        <div class="patch-modal-section">

            <h3>
                Estado de la postulación
            </h3>

            <div
                class="patch-status-selector"
                id="selectorEstado"
            >

                <button
                    type="button"
                    class="patch-status-option"
                    data-estado="nueva"
                    onclick="seleccionarEstado(this)"
                >
                    <span class="patch-status-option-icon">
                        <i class="fa fa-clock"></i>
                    </span>

                    <span>
                        Por revisar
                    </span>
                </button>


                <button
                    type="button"
                    class="patch-status-option"
                    data-estado="en_proceso"
                    onclick="seleccionarEstado(this)"
                >
                    <span class="patch-status-option-icon">
                        <i class="fa fa-spinner"></i>
                    </span>

                    <span>
                        En proceso
                    </span>
                </button>


                <button
                    type="button"
                    class="patch-status-option"
                    data-estado="entrevista"
                    onclick="seleccionarEstado(this)"
                >
                    <span class="patch-status-option-icon">
                        <i class="fa fa-comments"></i>
                    </span>

                    <span>
                        Entrevista
                    </span>
                </button>


                <button
                    type="button"
                    class="patch-status-option"
                    data-estado="aceptado"
                    onclick="seleccionarEstado(this)"
                >
                    <span class="patch-status-option-icon">
                        <i class="fa fa-circle-check"></i>
                    </span>

                    <span>
                        Aceptado
                    </span>
                </button>


                <button
                    type="button"
                    class="patch-status-option"
                    data-estado="descartado"
                    onclick="seleccionarEstado(this)"
                >
                    <span class="patch-status-option-icon">
                        <i class="fa fa-circle-xmark"></i>
                    </span>

                    <span>
                        Descartado
                    </span>
                </button>

            </div>

        </div>

    </div>


    <div class="patch-modal-footer">

        <button
            type="button"
            class="patch-modal-footer-button secondary"
            onclick="cerrarPostulacion()"
        >
            <i class="fa fa-xmark"></i>
            Cerrar
        </button>


        <button
            type="button"
            class="patch-modal-footer-button primary"
            onclick="guardarEstado()"
        >
            <i class="fa fa-floppy-disk"></i>
            Guardar estado
        </button>

    </div>

</div>


</div>
