/**
 * ============================================================
 * API - POSTULACIONES
 * Patch_BolsaTrabajo
 * ============================================================
 *
 * Este archivo centraliza las peticiones relacionadas con
 * postulaciones.
 *
 * No contiene lógica visual del modal ni de los filtros.
 * ============================================================
 */

(function (window) {

    'use strict';

    const PostulacionesAPI = {

        /**
         * URL base de las APIs del módulo.
         */
        baseURL: 'api/',

        /**
         * Realiza una petición GET a una API.
         *
         * @param {string} endpoint
         * @param {Object} params
         * @returns {Promise<Object>}
         */
        async get(endpoint, params = {}) {

            const url = new URL(
                this.baseURL + endpoint,
                window.location.href
            );

            Object.keys(params).forEach(function (key) {

                const valor = params[key];

                if (
                    valor !== undefined &&
                    valor !== null &&
                    valor !== ''
                ) {
                    url.searchParams.set(key, valor);
                }

            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            return this.procesarRespuesta(response);
        },

        /**
         * Realiza una petición POST.
         *
         * @param {string} endpoint
         * @param {Object|FormData} datos
         * @returns {Promise<Object>}
         */
        async post(endpoint, datos = {}) {

            let opciones = {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            };

            if (datos instanceof FormData) {

                opciones.body = datos;

            } else {

                opciones.headers['Content-Type'] =
                    'application/x-www-form-urlencoded; charset=UTF-8';

                opciones.body =
                    new URLSearchParams(datos).toString();
            }

            const response = await fetch(
                this.baseURL + endpoint,
                opciones
            );

            return this.procesarRespuesta(response);
        },

        /**
         * Procesa una respuesta de las APIs.
         *
         * @param {Response} response
         * @returns {Promise<Object>}
         */
        async procesarRespuesta(response) {

            let datos = null;

            try {

                datos = await response.json();

            } catch (error) {

                throw new Error(
                    'La respuesta del servidor no tiene un formato JSON válido.'
                );
            }

            if (!response.ok) {

                throw new Error(
                    datos && datos.mensaje
                        ? datos.mensaje
                        : 'Ocurrió un error al comunicarse con el servidor.'
                );
            }

            if (
                datos &&
                typeof datos === 'object' &&
                Object.prototype.hasOwnProperty.call(datos, 'ok')
            ) {

                if (!datos.ok) {

                    throw new Error(
                        datos.mensaje ||
                        'La operación no pudo completarse.'
                    );
                }
            }

            return datos;
        },

        /**
         * Obtiene el detalle de una postulación.
         *
         * @param {number} id
         * @returns {Promise<Object>}
         */
        obtenerDetalle(id) {

            return this.get(
                'detalle_postulacion.php',
                {
                    id: id
                }
            );
        },

        /**
         * Actualiza el estado y el comentario de seguimiento
         * de una postulación.
         *
         * @param {number} id
         * @param {string} estado
         * @param {string} comentarioSeguimiento
         * @returns {Promise<Object>}
         */
        actualizarEstado(
            id,
            estado,
            comentarioSeguimiento = ''
        ) {

            return this.post(
                'actualizar_postulacion.php',
                {
                    id: id,
                    estado: estado,
                    comentario_seguimiento: comentarioSeguimiento
                }
            );
        },

        /**
         * Obtiene la lista de postulaciones.
         *
         * @param {Object} filtros
         * @returns {Promise<Object>}
         */
        listar(filtros = {}) {

            return this.get(
                'listar_postulaciones.php',
                filtros
            );
        },

        /**
         * Obtiene el listado de CV generales.
         *
         * @returns {Promise<Object>}
         */
        listarCVGenerales() {

            return this.get(
                'listar_postulaciones.php',
                {
                    tipo: 'general'
                }
            );
        },

        /**
         * Obtiene la ruta para visualizar un CV.
         *
         * @param {string} ruta
         * @returns {string}
         */
        obtenerRutaCV(ruta) {

            if (!ruta) {
                return '#';
            }

            ruta = String(ruta).replace(/\\/g, '/');

            if (
                ruta.indexOf('http://') === 0 ||
                ruta.indexOf('https://') === 0 ||
                ruta.indexOf('/') === 0
            ) {
                return ruta;
            }

            if (ruta.indexOf('backend/') === 0) {
                return '../' + ruta;
            }

            return '../../../../../' + ruta;
        },

        /**
         * Obtiene únicamente el nombre del archivo CV.
         *
         * @param {string} ruta
         * @returns {string}
         */
        obtenerNombreArchivo(ruta) {

            if (!ruta) {
                return '';
            }

            const partes = String(ruta)
                .replace(/\\/g, '/')
                .split('/');

            return partes[partes.length - 1] || '';
        }

    };

    /**
     * Exponemos la API globalmente para que los demás
     * archivos JavaScript del módulo puedan utilizarla.
     */
    window.PostulacionesAPI = PostulacionesAPI;

})(window);