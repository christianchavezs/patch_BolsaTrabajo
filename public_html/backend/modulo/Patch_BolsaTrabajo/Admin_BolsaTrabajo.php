<?php
// Includes del servidor (se mantienen para cuando subas la app)
require_once __DIR__ . "/../../../includes/config.php";
//require_once __DIR__ . "/../../../includes/_header.php";

$title = "Bolsa de Trabajo";
$description = "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo $description; ?>">

    <!-- Fuente moderna desde Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">

   <!-- Favicon del módulo -->
<link rel="icon" href="C:\AppServ\www\Patch_BolsaTrabajo\public_html\backend\modulo\Patch_BolsaTrabajo\
" type="image/x-icon">



    <!-- CSS del módulo -->
    <link rel="stylesheet" href="css/Admin_BolsaTrabajo.css">

    <!-- FontAwesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="patch-bolsa-wrapper">
    <div class="container">

        <!-- HEADER -->
        <div class="patch-bolsa-header">
            <div>
                <h1><i class="fa fa-briefcase"></i> Bolsa de Trabajo</h1>
                <p>Administra las vacantes disponibles y controla su publicación.</p>
            </div>

            <div class="patch-bolsa-header-actions">
                <a href="javascript:;" class="patch-btn-primary" id="btnMostrarArchivadas">
                    <i class="fa fa-archive"></i> Vacantes archivadas
                </a>
                <a href="javascript:;" class="patch-btn-primary" id="btnNuevaVacante">
                    <i class="fa fa-plus"></i> Nueva vacante
                </a>
            </div>
        </div>

        <!-- RESUMEN -->
        <div class="patch-summary-grid">
            <div class="patch-summary-card">
                <div class="patch-summary-icon"><i class="fa fa-check-circle"></i></div>
                <div class="patch-summary-content">
                    <span class="patch-summary-number" id="contadorActivas">0</span>
                    <span class="patch-summary-label">Vacantes activas</span>
                </div>
            </div>

            <div class="patch-summary-card">
                <div class="patch-summary-icon"><i class="fa fa-pause-circle"></i></div>
                <div class="patch-summary-content">
                    <span class="patch-summary-number" id="contadorInactivas">0</span>
                    <span class="patch-summary-label">Vacantes inactivas</span>
                </div>
            </div>

            <div class="patch-summary-card">
                <div class="patch-summary-icon"><i class="fa fa-briefcase"></i></div>
                <div class="patch-summary-content">
                    <span class="patch-summary-number" id="contadorTotal">0</span>
                    <span class="patch-summary-label">Total de vacantes</span>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="patch-filters">
            <div class="patch-filter-group patch-filter-search">
                <label class="patch-filter-label">Buscar vacante</label>
                <i class="fa fa-search"></i>
                <input type="search" id="buscarVacante" class="patch-filter-input" placeholder="Buscar por nombre..." autocomplete="off">
            </div>

            <div class="patch-filter-group">
                <label class="patch-filter-label">Estado</label>
                <select id="filtroEstado" class="patch-filter-select">
                    <option value="todas">Todas</option>
                    <option value="activas">Activas</option>
                    <option value="inactivas">Inactivas</option>
                </select>
            </div>

            <div class="patch-filter-group">
                <label class="patch-filter-label">Ordenar por</label>
                <select id="ordenVacantes" class="patch-filter-select">
                    <option value="recientes">Más recientes</option>
                    <option value="antiguas">Más antiguas</option>
                    <option value="az">Título A-Z</option>
                </select>
            </div>

            <div>
                <button type="button" class="patch-btn-clear" id="btnLimpiarFiltros">
                    <i class="fa fa-refresh"></i> Limpiar filtros
                </button>
            </div>
        </div>

        <!-- LISTADO -->
        <h2 class="patch-section-title">Vacantes registradas</h2>
        <div class="patch-vacancies-list" id="listaVacantes">
            <div class="patch-loading">
                <i class="fa fa-spinner fa-spin"></i> Cargando vacantes...
            </div>
        </div>

        <!-- ESTADO VACÍO -->
        <div class="patch-empty-state" id="estadoVacio">
            <i class="fa fa-search"></i>
            <h3>No se encontraron vacantes</h3>
            <p>Intenta cambiar los filtros o registra una nueva vacante.</p>
        </div>

    </div>
</div>

<!-- MODAL NUEVA/EDITAR VACANTE -->
<?php include 'partials/modal_vacante.php'; ?>

<!-- MODAL ARCHIVADAS -->
<?php include 'partials/modal_archivadas.php'; ?>

<!-- MODAL CONFIRMAR ARCHIVADAS -->
<?php include 'partials/modal_confirmar.php'; ?>

<!-- MODAL CONFIRMAR DESARCHIVAR -->
<?php include 'partials/modal_confirmar_desarchivar.php'; ?>

<!-- TOAST -->
<div class="patch-toast" id="patchToast">
    <i class="fa fa-check-circle"></i>
    <span id="patchToastMensaje"></span>
</div>

<!-- SCRIPTS MODULARES -->
<!-- <script src="js/bolsa-trabajo-ui.js"></script>
<script src="js/bolsa-trabajo-api.js"></script>
<script src="js/bolsa-trabajo-formulario.js"></script>
<script src="js/bolsa-trabajo-listado.js"></script>
<script src="js/bolsa-trabajo-archivadas.js"></script> -->

<script src="js/bolsa-trabajo-ui.js?v=20260917-1"></script>
<script src="js/bolsa-trabajo-api.js?v=20260917-1"></script>
<script src="js/bolsa-trabajo-formulario.js?v=20260917-1"></script>
<script src="js/bolsa-trabajo-listado.js?v=20260917-1"></script>
<script src="js/bolsa-trabajo-archivadas.js?v=20260917-1"></script>


<script>
    // Variables globales necesarias
    let vacantes = [];
    let vacantesArchivadas = [];
    let modoFormulario = 'crear';
    let toastTimeout = null;

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        cargarVacantes(); // primera carga de vacantes
    });
</script>

<!--?php include '../../../includes/_footer.php'; ?-->
</body>
</html>
