/**
 * Carga de dependencias JavaScript del proyecto (Vue, Axios, etc.).
 */

require('./bootstrap');

window.Vue = require('vue').default;

import Axios from 'axios'
import Swal from 'sweetalert'
import izitoast from 'izitoast'
import '../../public/admin/css/izitoast.css'

Vue.prototype.$http = Axios;
Vue.prototype.$swal = Swal;

/**
 * Bootstrap "atrapa" el foco dentro del modal abierto; eso impide usar los botones
 * de SweetAlert (Enter/Escape/foco) cuando la confirmación aparece sobre un modal.
 */
if (window.$ && $.fn.modal) {
    $.fn.modal.Constructor.prototype._enforceFocus = function () {};
}

/**
 * Pregunta de confirmación Sí / No. Devuelve una promesa con true o false.
 */
function confirmarAccion(pregunta, detalle) {
    return Swal({
        title: pregunta,
        text: detalle || '',
        icon: 'warning',
        buttons: {
            cancel: { text: 'No', value: false, visible: true, closeModal: true },
            confirm: { text: 'Sí', value: true, closeModal: true },
        },
    }).then(valor => !!valor);
}

/**
 * Mensajes temporales en la esquina superior derecha.
 */
function notificarExito(mensaje) {
    izitoast.success({ title: '¡Éxito!', message: mensaje, position: 'topRight', timeout: 3500 });
}

function notificarError(mensaje) {
    izitoast.error({ title: '¡Error!', message: mensaje, position: 'topRight', timeout: 5000 });
}

/**
 * Traduce un error de Axios a un mensaje legible en español.
 */
function mensajeDeError(err, accion) {
    const prefijo = 'No se pudo ' + (accion || 'completar la operación');
    if (err && err.response) {
        if (err.response.status === 422) return prefijo + ': revise los campos marcados.';
        if (err.response.status === 403) return prefijo + ': no tiene permiso para esta acción.';
        if (err.response.data && err.response.data.message) return prefijo + ': ' + err.response.data.message;
    }
    return prefijo + '. Intente nuevamente.';
}

/**
 * Coloca en cada celda el nombre de su columna (data-label). En pantallas pequeñas
 * el CSS usa ese dato para mostrar cada fila como una tarjeta, sin barra horizontal.
 */
function etiquetarCeldas(tabla) {
    const titulos = Array.from(tabla.querySelectorAll('thead th')).map(th => th.innerText.trim());
    tabla.classList.add('tabla-apilable');
    tabla.querySelectorAll('tbody tr').forEach(fila => {
        Array.from(fila.children).forEach((celda, i) => celda.setAttribute('data-label', titulos[i] || ''));
    });
}

/**
 * Opciones comunes de DataTables: en español, sin reordenar (se respeta el orden
 * del servidor: el más reciente arriba) y ajustadas al ancho de la pantalla.
 */
function opcionesTablaDatos(extra) {
    return Object.assign({
        order: [],
        autoWidth: false,
        pageLength: 10,
        dom: "<'row'<'col-12 col-md-6'l><'col-12 col-md-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row'<'col-12 col-md-5'i><'col-12 col-md-7'p>>",
        initComplete: function () {
            const api = this.api();
            const tabla = api.table().node();
            const titulos = api.columns().header().toArray().map(th => th.innerText.trim());
            tabla.classList.add('tabla-apilable');
            api.rows().nodes().toArray().forEach(fila => {
                Array.from(fila.children).forEach((celda, i) => celda.setAttribute('data-label', titulos[i] || ''));
            });
        },
        language: {
            processing: 'Procesando...',
            lengthMenu: 'Mostrar _MENU_ registros',
            zeroRecords: 'No se encontraron resultados',
            emptyTable: 'No hay registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros en total)',
            search: 'Buscar:',
            loadingRecords: 'Cargando...',
            paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
            aria: { sortAscending: ': orden ascendente', sortDescending: ': orden descendente' },
        },
    }, extra || {});
}

/**
 * Permisos del usuario autenticado (los publica la plantilla base). Solo sirven
 * para mostrar u ocultar botones: el servidor vuelve a verificar cada acción.
 */
function puede(permiso) {
    return (window.permisosUsuario || []).indexOf(permiso) > -1;
}

const ETIQUETAS_PRIORIDAD = { critico: 'Crítico', alto: 'Alto', medio: 'Medio', bajo: 'Bajo' };
const ETIQUETAS_ESTADO = {
    abierto: 'Abierto', en_proceso: 'En proceso', contenido: 'Contenido',
    erradicado: 'Erradicado', recuperado: 'Recuperado', cerrado: 'Cerrado',
};
const ETIQUETAS_CALIFICACION = { por_calificar: 'Evento por calificar', incidente: 'Incidente', descartado: 'Evento descartado' };
const ETIQUETAS_ORIGEN = { adversario: 'Adversario', no_adversario: 'No adversario' };

function formatoFecha(valor) {
    if (!valor) return '-';
    const f = new Date(valor);
    if (isNaN(f)) return valor;
    const d = n => String(n).padStart(2, '0');
    return `${d(f.getDate())}/${d(f.getMonth() + 1)}/${f.getFullYear()} ${d(f.getHours())}:${d(f.getMinutes())}`;
}

function formatoIndice(valor) {
    return valor === null || valor === undefined ? '-' : Number(valor).toFixed(2).replace('.', ',');
}

// Disponibles también para las vistas Blade (formularios y listado de usuarios).
window.opcionesTablaDatos = opcionesTablaDatos;
window.etiquetarCeldas = etiquetarCeldas;
window.confirmarAccion = confirmarAccion;
window.notificarExito = notificarExito;
window.notificarError = notificarError;

Vue.mixin({
    /**
     * Los modales de Bootstrap deben vivir directamente en <body>.
     * Dentro de <section class="section"> (z-index: 1 en Stisla) quedan por debajo
     * de la barra superior, que tapa la "X" y bloquea el cierre del modal.
     */
    mounted() {
        if (this.$el && this.$el.classList && this.$el.classList.contains('modal')
            && this.$el.parentNode !== document.body) {
            document.body.appendChild(this.$el);
        }
    },
    beforeDestroy() {
        if (this.$el && this.$el.classList && this.$el.classList.contains('modal')
            && this.$el.parentNode === document.body) {
            document.body.removeChild(this.$el);
        }
    },
    methods: {
        confirmar: confirmarAccion,
        notificarExito: notificarExito,
        notificarError: notificarError,
        mensajeDeError: mensajeDeError,
        puede: puede,
        formatoFecha: formatoFecha,
        formatoIndice: formatoIndice,
        etiquetaPrioridad(n) { return n ? (ETIQUETAS_PRIORIDAD[n] || n) : 'Sin evaluar'; },
        clasePrioridad(n) { return 'badge-prioridad-' + (n || 'ninguna'); },
        etiquetaEstado(e) { return ETIQUETAS_ESTADO[e] || e; },
        etiquetaCalificacion(c) { return ETIQUETAS_CALIFICACION[c] || c; },
        etiquetaOrigen(o) { return o ? (ETIQUETAS_ORIGEN[o] || o) : 'Sin indicar'; },
    }
});

/**
 * Registro de los componentes Vue del panel.
 */
Vue.component('incidentes', require('./components/Panel/Incidentes.vue').default);
Vue.component('guardar-incidente', require('./components/Panel/GuardarIncidente.vue').default);
Vue.component('detalle-incidente', require('./components/Panel/DetalleIncidente.vue').default);
Vue.component('tipos-incidente', require('./components/Panel/TiposIncidente.vue').default);
Vue.component('guardar-tipo-incidente', require('./components/Panel/GuardarTipoIncidente.vue').default);
Vue.component('roles', require('./components/Panel/Roles.vue').default);
Vue.component('guardar-rol', require('./components/Panel/GuardarRol.vue').default);
Vue.component('permisos', require('./components/Panel/Permisos.vue').default);
Vue.component('guardar-permiso', require('./components/Panel/GuardarPermiso.vue').default);
Vue.component('activos', require('./components/Panel/Activos.vue').default);
Vue.component('tacticas', require('./components/Panel/Tacticas.vue').default);
Vue.component('calculo-ahp', require('./components/Panel/CalculoAhp.vue').default);

/**
 * Instancia principal de Vue montada sobre #app.
 */
const app = new Vue({
    el: '#app',
});
