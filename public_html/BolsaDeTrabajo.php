<?php

require_once __DIR__ . "/includes/config.php";

header('Content-Type: text/html; charset=utf-8');


/* =========================================================
   FUNCIONES AUXILIARES
   ========================================================= */

function e($valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


function resumenTexto($texto, int $limite = 145): string
{
    $texto = trim(
        preg_replace(
            '/\s+/',
            ' ',
            strip_tags((string)$texto)
        )
    );

    if (mb_strlen($texto) > $limite) {
        return mb_substr($texto, 0, $limite) . '…';
    }

    return $texto;
}


function formatearFecha($fecha): string
{
    if (empty($fecha)) {
        return 'Fecha por confirmar';
    }

    $timestamp = strtotime((string)$fecha);

    if (!$timestamp) {
        return 'Fecha por confirmar';
    }

    return date(
        'd/m/Y',
        $timestamp
    );
}


/**
 * Obtiene una clase de color estable para cada vacante.
 *
 * El color depende del ID para que cada vacante conserve
 * su apariencia mientras exista.
 */
function colorVacante(int $id): string
{
    $colores = [
        'verde',
        'azul',
        'morado',
        'naranja',
        'turquesa',
        'coral',
        'indigo',
        'esmeralda'
    ];

    return $colores[$id % count($colores)];
}


/**
 * Devuelve un valor limpio para campos opcionales.
 */
function campoVacante($valor): string
{
    return trim((string)($valor ?? ''));
}


/* =========================================================
   BUSCADOR
   ========================================================= */

$buscar = trim(
    $_GET['buscar'] ?? ''
);


/* =========================================================
   OBTENER VACANTES DISPONIBLES
   ========================================================= */

$vacantes = [];
$error = null;

$sql = "
    SELECT
        id,
        token,
        titulo,

        /* Información principal de la vacante */
        descripcion,
        ubicacion,
        tipo_jornada,
        modalidad,

        /* Información adicional */
        lo_que_se_ofrece,
        requisitos,
        responsabilidades,

        /* Estado */
        activo,
        archivada,

        /* Imagen */
        solo_imagen,
        url_imagen,

        /* Fechas */
        fecha_publicacion,
        fecha_cierre

    FROM patch_BolsaTrabajo

    WHERE activo = 1
      AND archivada = 0
";


/* =========================================================
   FILTRO DE BÚSQUEDA
   ========================================================= */

if ($buscar !== '') {

    $sql .= "
        AND (
            titulo LIKE ?
            OR descripcion LIKE ?
            OR ubicacion LIKE ?
            OR tipo_jornada LIKE ?
            OR modalidad LIKE ?
            OR lo_que_se_ofrece LIKE ?
            OR requisitos LIKE ?
            OR responsabilidades LIKE ?
        )
    ";
}


$sql .= "
    ORDER BY
        fecha_publicacion DESC,
        id DESC
";


/* =========================================================
   PREPARAR CONSULTA
   ========================================================= */

$stmt = $conn->prepare($sql);


if (!$stmt) {

    $error =
        'No fue posible cargar las vacantes disponibles.';

} else {

    /* =====================================================
       PARÁMETROS DE BÚSQUEDA
       ===================================================== */

    if ($buscar !== '') {

        $busqueda =
            '%' . $buscar . '%';

        $stmt->bind_param(
            "ssssssss",
            $busqueda,
            $busqueda,
            $busqueda,
            $busqueda,
            $busqueda,
            $busqueda,
            $busqueda,
            $busqueda
        );
    }


    /* =====================================================
       EJECUTAR CONSULTA
       ===================================================== */

    if (!$stmt->execute()) {

        $error =
            'No fue posible cargar las vacantes disponibles.';

    } else {

        $resultado =
            $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {

            $vacantes[] = $fila;
        }
    }


    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Consulta las vacantes disponibles de Kombitec."
    >

    <title>
        Bolsa de Trabajo | Kombitec
    </title>

    <link
        rel="stylesheet"
        href="css/bolsa-trabajo.css"
    >

</head>


<body>


<!-- =====================================================
     ENCABEZADO
     ===================================================== -->

<header class="hero">


    <!-- =================================================
         NAVBAR
         ================================================= -->

    <nav class="navbar">

        <a
            class="logo"
            href="BolsaDeTrabajo.php"
            aria-label="Bolsa de Trabajo"
        >

            <span
                class="maletin"
                aria-hidden="true"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                >

                    <path
                        d="M8 8V6.8A1.8 1.8 0 0 1 9.8 5h4.4A1.8 1.8 0 0 1 16 6.8V8"
                    />

                    <path
                        d="M4.5 9.5h15A1.5 1.5 0 0 1 21 11v7.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5V11a1.5 1.5 0 0 1 1.5-1.5Z"
                    />

                    <path
                        d="M3.3 13.2c2.4 1.5 5.1 2.2 8.7 2.2s6.3-.7 8.7-2.2"
                    />

                </svg>

            </span>


            <span>
                Bolsa de Trabajo
            </span>

        </a>


        <div class="menu">

            <a href="#vacantes">
                Vacantes
            </a>

            <a href="#footer">
                Contacto
            </a>

        </div>

    </nav>


    <!-- =================================================
         HERO CONTENIDO
         ================================================= -->

    <div class="hero-contenido">

        <span class="etiqueta">
            Nuevas oportunidades para ti
        </span>


        <h1>

            Encuentra el trabajo que impulsa tu

            <em>
                talento.
            </em>

        </h1>


        <p>

            Explora nuestras vacantes disponibles
            y descubre nuevas oportunidades para
            crecer profesionalmente.

        </p>


        <a
            class="boton-hero"
            href="#vacantes"
        >

            Ver vacantes

            <span>
                ↓
            </span>

        </a>

    </div>


    <!-- =================================================
         DECORACIÓN
         ================================================= -->

    <div
        class="circulo circulo-1"
        aria-hidden="true"
    ></div>


    <div
        class="circulo circulo-2"
        aria-hidden="true"
    ></div>


</header>


<!-- =====================================================
     CONTENIDO PRINCIPAL
     ===================================================== -->

<main
    class="contenido"
    id="vacantes"
>


    <!-- =================================================
         ENCABEZADO DE SECCIÓN
         ================================================= -->

    <section class="encabezado-seccion">

        <div>

            <span class="kicker">
                Oportunidades abiertas
            </span>


            <h2>
                Vacantes disponibles
            </h2>

        </div>


        <!-- =================================================
             CONTADOR DE OPORTUNIDADES
             ================================================= -->

        <div class="contador-oportunidades">

            <strong
                class="contador-numero"
                id="contadorNumero"
            >
                <?= count($vacantes) ?>
            </strong>


            <div class="contador-texto">

                <span
                    class="contador-principal"
                    id="contadorPrincipal"
                >

                    <?=

                        count($vacantes) === 1

                            ? ' oportunidad disponible'

                            : ' oportunidades disponibles'

                    ?>

                </span>


                <span class="contador-secundario">

                    para dar el siguiente paso.

                </span>

            </div>

        </div>

    </section>


    <!-- =================================================
         BUSCADOR
         ================================================= -->

    <form
        class="buscador"
        id="formBuscador"
        method="get"
        action="BolsaDeTrabajo.php"
        autocomplete="off"
    >

        <label class="campo-busqueda">

            <span aria-hidden="true">
                ⌕
            </span>


            <input
                type="search"
                name="buscar"
                id="campoBusqueda"
                value="<?= e($buscar) ?>"
                placeholder="Busca por puesto o palabra clave..."
                autocomplete="off"
                spellcheck="false"
                aria-label="Buscar vacantes"
            >

        </label>


        <button
            type="button"
            id="botonLimpiarBusqueda"
        >

            Limpiar búsqueda

            <span aria-hidden="true">
                ×
            </span>

        </button>

    </form>


    <!-- =================================================
         RESULTADOS
         ================================================= -->

    <div id="resultadosVacantes">


        <?php if ($error): ?>


            <div class="mensaje error">

                <strong>
                    No fue posible cargar las vacantes.
                </strong>

                <span>
                    <?= e($error) ?>
                </span>

            </div>


        <?php elseif (!$vacantes): ?>


            <div class="mensaje">

                <strong>

                    <?php if ($buscar !== ''): ?>

                        No encontramos vacantes para
                        "<?= e($buscar) ?>".

                    <?php else: ?>

                        No hay vacantes disponibles.

                    <?php endif; ?>

                </strong>


                <span>

                    <?php if ($buscar !== ''): ?>

                        Intenta realizar una búsqueda
                        diferente.

                    <?php else: ?>

                        Vuelve a intentarlo más tarde.

                    <?php endif; ?>

                </span>

            </div>


        <?php else: ?>


            <!-- =================================================
                 GRID DE VACANTES
                 ================================================= -->

            <section
                class="grid-vacantes"
                aria-label="Listado de vacantes disponibles"
            >


                <?php foreach ($vacantes as $vacante): ?>


                    <?php

                    /* =================================================
                       DATOS DE LA VACANTE
                       ================================================= */

                    $color =
                        colorVacante(
                            (int)$vacante['id']
                        );


                    $titulo =
                        campoVacante(
                            $vacante['titulo']
                        );


                    $descripcion =
                        campoVacante(
                            $vacante['descripcion']
                        );


                    $ubicacion =
                        campoVacante(
                            $vacante['ubicacion']
                        );


                    $tipoJornada =
                        campoVacante(
                            $vacante['tipo_jornada']
                        );


                    $modalidad =
                        campoVacante(
                            $vacante['modalidad']
                        );


                    $soloImagen =
                        (int)$vacante['solo_imagen'] === 1;


                    /*
                     * =================================================
                     * URL DEL DETALLE DE LA VACANTE
                     * =================================================
                     *
                     * La página pública de detalle utiliza el TOKEN
                     * de la vacante, no el ID.
                     *
                     * Ejemplo:
                     *
                     * Detalle_Vacante.php?token=5bc1e752047a2dfa
                     *
                     * urlencode() protege correctamente el token
                     * antes de colocarlo dentro de la URL.
                     * =================================================
                     */

                    $urlDetalle =
                        'Detalle_Vacante.php?token=' .
                        urlencode(
                            (string)$vacante['token']
                        );

                    ?>


                    <!-- =================================================
                         TARJETA DE VACANTE
                         ================================================= -->

                    <a
                        class="tarjeta tarjeta-<?= e($color) ?>"
                        href="<?= e($urlDetalle) ?>"
                        aria-label="Ver detalles de <?= e($titulo ?: 'vacante disponible') ?>"
                    >


                        <!-- =============================================
                             ENCABEZADO VISUAL
                             ============================================= -->

                        <div class="encabezado-tarjeta">


                            <!-- =========================================
                                 PATRÓN DECORATIVO
                                 ========================================= -->

                            <div
                                class="patron-tarjeta"
                                aria-hidden="true"
                            >

                                <span
                                    class="patron-circulo patron-circulo-1"
                                ></span>

                                <span
                                    class="patron-circulo patron-circulo-2"
                                ></span>

                                <span
                                    class="patron-linea patron-linea-1"
                                ></span>

                                <span
                                    class="patron-linea patron-linea-2"
                                ></span>

                                <span
                                    class="patron-punto patron-punto-1"
                                ></span>

                                <span
                                    class="patron-punto patron-punto-2"
                                ></span>

                            </div>


                            <!-- =========================================
                                 ICONO DE VACANTE
                                 ========================================= -->

                            <div
                                class="icono-vacante"
                                aria-hidden="true"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >

                                    <path
                                        d="M8 8V6.8A1.8 1.8 0 0 1 9.8 5h4.4A1.8 1.8 0 0 1 16 6.8V8"
                                    />

                                    <path
                                        d="M4.5 9.5h15A1.5 1.5 0 0 1 21 11v7.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5V11a1.5 1.5 0 0 1 1.5-1.5Z"
                                    />

                                    <path
                                        d="M3.3 13.2c2.4 1.5 5.1 2.2 8.7 2.2s6.3-.7 8.7-2.2"
                                    />

                                </svg>

                            </div>


                            <!-- =========================================
                                 ESTADO
                                 ========================================= -->

                            <span class="activa">

                                <i></i>

                                Activa

                            </span>


                            <!-- =========================================
                                 ETIQUETA
                                 ========================================= -->

                            <span class="etiqueta-oportunidad">

                                Oportunidad laboral

                            </span>


                        </div>


                        <!-- =================================================
                             CUERPO DE LA TARJETA
                             ================================================= -->

                        <div class="cuerpo-tarjeta">


                            <!-- =============================================
                                 FECHA
                                 ============================================= -->

                            <span class="fecha">

                                Publicada

                                <?= e(
                                    formatearFecha(
                                        $vacante['fecha_publicacion']
                                    )
                                ) ?>

                            </span>


                            <!-- =============================================
                                 TÍTULO
                                 ============================================= -->

                            <h3>

                                <?= e(
                                    $titulo
                                        ?: 'Vacante disponible'
                                ) ?>

                            </h3>


                            <!-- =============================================
                                 DESCRIPCIÓN
                                 =============================================

                                 REGLA:

                                 1. Si existe descripcion:
                                    mostrar descripcion.

                                 2. Si descripcion está vacía:
                                    mostrar el texto indicado.

                                 NO se utiliza lo_que_se_ofrece
                                 como sustituto de descripcion.
                                 ============================================= -->

                            <?php if ($descripcion !== ''): ?>


                                <p class="descripcion-vacante">

                                    <?= e(
                                        resumenTexto(
                                            $descripcion,
                                            160
                                        )
                                    ) ?>

                                </p>


                            <?php else: ?>


                                <p class="texto-solo-imagen">

                                    Consulta la información completa
                                    de esta vacante.

                                </p>


                            <?php endif; ?>


                            <!-- =================================================
                                 INFORMACIÓN DE LA VACANTE
                                 ================================================= -->

                            <?php if (
                                $ubicacion !== ''
                                || $tipoJornada !== ''
                                || $modalidad !== ''
                            ): ?>


                                <div class="datos-vacante">


                                    <!-- =========================================
                                         UBICACIÓN
                                         ========================================= -->

                                    <?php if ($ubicacion !== ''): ?>


                                        <div class="dato-vacante">

                                            <span
                                                class="dato-icono"
                                                aria-hidden="true"
                                            >

                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >

                                                    <path
                                                        d="M20 10c0 5-8 10-8 10S4 15 4 10a8 8 0 1 1 16 0Z"
                                                    />

                                                    <circle
                                                        cx="12"
                                                        cy="10"
                                                        r="2.5"
                                                    />

                                                </svg>

                                            </span>


                                            <span>

                                                <?= e(
                                                    $ubicacion
                                                ) ?>

                                            </span>

                                        </div>


                                    <?php endif; ?>


                                    <!-- =========================================
                                         TIPO DE JORNADA
                                         ========================================= -->

                                    <?php if ($tipoJornada !== ''): ?>


                                        <div class="dato-vacante">

                                            <span
                                                class="dato-icono"
                                                aria-hidden="true"
                                            >

                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >

                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="8.5"
                                                    />

                                                    <path
                                                        d="M12 7v5l3 2"
                                                    />

                                                </svg>

                                            </span>


                                            <span>

                                                <?= e(
                                                    $tipoJornada
                                                ) ?>

                                            </span>

                                        </div>


                                    <?php endif; ?>


                                    <!-- =========================================
                                         MODALIDAD
                                         ========================================= -->

                                    <?php if ($modalidad !== ''): ?>


                                        <div class="dato-vacante">

                                            <span
                                                class="dato-icono"
                                                aria-hidden="true"
                                            >

                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                >

                                                    <rect
                                                        x="4"
                                                        y="5"
                                                        width="16"
                                                        height="11"
                                                        rx="1.5"
                                                    />

                                                    <path
                                                        d="M8 20h8M12 16v4"
                                                    />

                                                </svg>

                                            </span>


                                            <span>

                                                <?= e(
                                                    $modalidad
                                                ) ?>

                                            </span>

                                        </div>


                                    <?php endif; ?>


                                </div>


                            <?php endif; ?>


                            <!-- =================================================
                                 PIE DE TARJETA
                                 ================================================= -->

                            <div class="pie-tarjeta">

                                <span>
                                    Ver detalles
                                </span>


                                <b>
                                    ↗
                                </b>

                            </div>


                        </div>


                    </a>


                <?php endforeach; ?>


            </section>


        <?php endif; ?>


    </div>


    <!-- =================================================
         CARD FINAL - ENVÍANOS TU CV
         ================================================= -->

    <section
        class="card-talento"
        aria-label="Envía tu CV"
    >

        <div
            class="card-talento-decoracion"
            aria-hidden="true"
        ></div>


        <div class="card-talento-contenido">


            <span class="card-talento-kicker">

                TALENTO KOMBITEC

            </span>


            <h2>

                ¿No encontraste lo que buscas?

            </h2>


            <p>

                Tu talento también puede

                <strong>

                    crear su propia oportunidad.

                </strong>

            </p>


            <a
                class="boton-cv"
                href="mailto:christianchavez394@gmail.com"
            >

                <span
                    class="icono-correo"
                    aria-hidden="true"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="14"
                            rx="2"
                        />

                        <path
                            d="m4 7 8 6 8-6"
                        />

                    </svg>

                </span>


                Envíanos tu CV

            </a>


        </div>

    </section>


</main>


<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer
    class="footer"
    id="footer"
>

    <strong>
        Bolsa de Trabajo
    </strong>


    <span>
        Encuentra tu próxima oportunidad.
    </span>

</footer>


<!-- =====================================================
     BÚSQUEDA EN TIEMPO REAL
     ===================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const campoBusqueda =
        document.getElementById('campoBusqueda');

    const botonLimpiar =
        document.getElementById('botonLimpiarBusqueda');

    const resultadosVacantes =
        document.getElementById('resultadosVacantes');

    const contadorNumero =
        document.getElementById('contadorNumero');

    const contadorPrincipal =
        document.getElementById('contadorPrincipal');

    const formBuscador =
        document.getElementById('formBuscador');


    if (
        !campoBusqueda
        || !botonLimpiar
        || !resultadosVacantes
        || !contadorNumero
        || !contadorPrincipal
    ) {
        return;
    }


    let temporizadorBusqueda = null;

    let controladorActual = null;


    /* =====================================================
       REALIZAR BÚSQUEDA
       ===================================================== */

    function realizarBusqueda(texto) {

        const valor =
            texto.trim();


        /*
         * Cancelar una petición anterior si todavía
         * está en proceso.
         */

        if (controladorActual) {

            controladorActual.abort();

        }


        controladorActual =
            new AbortController();


        /*
         * Construir la URL de búsqueda.
         */

        const url =
            new URL(
                window.location.href
            );


        url.searchParams.delete('buscar');


        if (valor !== '') {

            url.searchParams.set(
                'buscar',
                valor
            );

        }


        /*
         * Actualizar la URL sin recargar la página.
         */

        window.history.replaceState(
            {},
            '',
            url.toString()
        );


        /*
         * Obtener nuevamente el contenido de la
         * página utilizando la búsqueda actual.
         */

        fetch(
            url.toString(),
            {
                method: 'GET',

                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },

                signal: controladorActual.signal
            }
        )

        .then(function (respuesta) {

            if (!respuesta.ok) {

                throw new Error(
                    'No fue posible realizar la búsqueda.'
                );

            }

            return respuesta.text();

        })

        .then(function (html) {

            /*
             * Convertir la respuesta HTML en un documento
             * temporal para obtener únicamente los resultados.
             */

            const parser =
                new DOMParser();


            const documento =
                parser.parseFromString(
                    html,
                    'text/html'
                );


            const nuevosResultados =
                documento.getElementById(
                    'resultadosVacantes'
                );


            const nuevoContadorNumero =
                documento.getElementById(
                    'contadorNumero'
                );


            const nuevoContadorPrincipal =
                documento.getElementById(
                    'contadorPrincipal'
                );


            /*
             * Actualizar resultados.
             */

            if (nuevosResultados) {

                resultadosVacantes.innerHTML =
                    nuevosResultados.innerHTML;

            }


            /*
             * Actualizar contador.
             */

            if (nuevoContadorNumero) {

                contadorNumero.textContent =
                    nuevoContadorNumero.textContent.trim();

            }


            if (nuevoContadorPrincipal) {

                contadorPrincipal.textContent =
                    nuevoContadorPrincipal.textContent;

            }


            /*
             * Volver a colocar el foco en el campo de búsqueda.
             */

            campoBusqueda.focus();


            /*
             * Mantener el cursor al final del texto.
             */

            const longitud =
                campoBusqueda.value.length;


            campoBusqueda.setSelectionRange(
                longitud,
                longitud
            );

        })

        .catch(function (error) {

            /*
             * Si la petición fue cancelada porque el usuario
             * continuó escribiendo, no mostramos ningún error.
             */

            if (
                error.name === 'AbortError'
            ) {

                return;

            }


            console.error(
                'Error en búsqueda:',
                error
            );

        });

    }


    /* =====================================================
       BÚSQUEDA AL ESCRIBIR
       ===================================================== */

    campoBusqueda.addEventListener(
        'input',
        function () {

            clearTimeout(
                temporizadorBusqueda
            );


            temporizadorBusqueda =
                setTimeout(
                    function () {

                        realizarBusqueda(
                            campoBusqueda.value
                        );

                    },
                    300
                );

        }
    );


    /* =====================================================
       BOTÓN LIMPIAR BÚSQUEDA
       ===================================================== */

    botonLimpiar.addEventListener(
        'click',
        function () {

            clearTimeout(
                temporizadorBusqueda
            );


            campoBusqueda.value = '';


            realizarBusqueda('');

        }
    );


    /* =====================================================
       EVITAR ENVÍO TRADICIONAL DEL FORMULARIO
       =====================================================

       Aunque el botón ya no es submit, también evitamos
       que Enter provoque una recarga completa de la página.
       */

    formBuscador.addEventListener(
        'submit',
        function (evento) {

            evento.preventDefault();


            clearTimeout(
                temporizadorBusqueda
            );


            realizarBusqueda(
                campoBusqueda.value
            );

        }
    );

});

</script>


</body>

</html>
