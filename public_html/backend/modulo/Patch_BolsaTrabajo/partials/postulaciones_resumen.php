<div class="patch-summary-grid">

<div class="patch-summary-card">

    <div class="patch-summary-card-header">

        <div>
            <p class="patch-summary-card-label">
                Postulaciones totales
            </p>

            <p class="patch-summary-card-value">
                <?php echo (int)$totalPostulaciones; ?>
            </p>
        </div>

        <div class="patch-summary-card-icon">
            <i class="fa fa-users"></i>
        </div>

    </div>

    <p class="patch-summary-card-description">
        Todas las postulaciones recibidas.
    </p>

</div>


<div class="patch-summary-card orange">

    <div class="patch-summary-card-header">

        <div>
            <p class="patch-summary-card-label">
                Por revisar
            </p>

            <p class="patch-summary-card-value">
                <?php echo (int)$totalPendientes; ?>
            </p>
        </div>

        <div class="patch-summary-card-icon">
            <i class="fa fa-clock"></i>
        </div>

    </div>

    <p class="patch-summary-card-description">
        Postulaciones con estado nueva.
    </p>

</div>


<div class="patch-summary-card blue">

    <div class="patch-summary-card-header">

        <div>
            <p class="patch-summary-card-label">
                En proceso
            </p>

            <p class="patch-summary-card-value">
                <?php echo (int)$totalEnProceso; ?>
            </p>
        </div>

        <div class="patch-summary-card-icon">
            <i class="fa fa-spinner"></i>
        </div>

    </div>

    <p class="patch-summary-card-description">
        Candidatos actualmente en proceso.
    </p>

</div>


<div class="patch-summary-card purple">

    <div class="patch-summary-card-header">

        <div>
            <p class="patch-summary-card-label">
                CV generales
            </p>

            <p class="patch-summary-card-value">
                <?php echo (int)$totalCVGenerales; ?>
            </p>
        </div>

        <div class="patch-summary-card-icon">
            <i class="fa fa-file-pdf"></i>
        </div>

    </div>

    <p class="patch-summary-card-description">
        CV enviados sin una vacante específica.
    </p>

</div>

</div>
