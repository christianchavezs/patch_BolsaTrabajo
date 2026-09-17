<div class="patch-modal" id="modalArchivadas" aria-hidden="true">
    <div class="patch-modal-container" role="dialog" aria-modal="true">

        <!-- CABECERA DEL MODAL -->
        <div class="patch-modal-header">
            <h2 class="patch-modal-title">
                <i class="fa fa-archive"></i>
                <span>Vacantes archivadas</span>
            </h2>
            <button type="button" class="patch-modal-close" id="btnCerrarArchivadas" title="Cerrar">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="patch-modal-body">
            <div class="patch-vacancies-list" id="listaVacantesArchivadas">
                <div class="patch-loading">
                    <i class="fa fa-spinner fa-spin"></i> Cargando vacantes archivadas...
                </div>
            </div>

            <div class="patch-empty-state" id="estadoVacioArchivadas">
                <i class="fa fa-archive"></i>
                <h3>No hay vacantes archivadas</h3>
                <p>Las vacantes que archives aparecerán aquí.</p>
            </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="patch-modal-footer">
            <button type="button" class="patch-btn-secondary" id="btnCerrarArchivadasFooter">
                <i class="fa fa-times"></i> Cerrar
            </button>
        </div>

    </div>
</div>
