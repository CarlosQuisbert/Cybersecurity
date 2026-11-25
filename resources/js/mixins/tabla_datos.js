/**
 * Integra DataTables (buscador y paginador) con las tablas dibujadas por Vue.
 *
 * El componente debe definir:
 *   - la tabla con ref="tabla" y :key="claveTabla"
 *   - un computed "registrosTabla" con el arreglo que se lista
 *
 * Cuando el arreglo cambia, se destruye DataTables, Vue vuelve a dibujar la
 * tabla y se inicializa de nuevo, de modo que los registros nuevos o editados
 * siempre quedan dentro del paginador y del buscador.
 */
export default {
    data() {
        return { claveTabla: 0 };
    },
    watch: {
        registrosTabla: {
            deep: true,
            handler() {
                this.destruirTabla();
                this.claveTabla++;
                this.$nextTick(() => this.iniciarTabla());
            },
        },
    },
    mounted() {
        // Los scripts de DataTables se cargan después de app.js: se espera al DOM listo.
        $(() => this.iniciarTabla());
    },
    beforeDestroy() {
        this.destruirTabla();
    },
    methods: {
        iniciarTabla() {
            if (!this.$refs.tabla || !$.fn.DataTable || $.fn.DataTable.isDataTable(this.$refs.tabla)) return;
            $(this.$refs.tabla).DataTable(window.opcionesTablaDatos());
        },
        destruirTabla() {
            if (this.$refs.tabla && $.fn.DataTable && $.fn.DataTable.isDataTable(this.$refs.tabla)) {
                $(this.$refs.tabla).DataTable().destroy();
            }
        },
    },
};
