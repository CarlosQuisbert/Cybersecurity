<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="crearTipo()" class="btn btn-primary btn-agregar">
                <i class="fas fa-plus mr-2"></i>Nuevo tipo
            </button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead><tr><th>No</th><th>Nombre</th><th>Descripción</th><th>Incidentes</th><th>Activo</th><th>Editar</th><th>Eliminar</th></tr></thead>
                <tbody>
                <tr v-for="t, indice in listado" :key="t.id">
                    <td>{{ indice + 1 }}</td>
                    <td><strong>{{ t.nombre }}</strong></td>
                    <td>{{ t.descripcion || '-' }}</td>
                    <td><span class="badge badge-info">{{ t.incidentes_count || 0 }}</span></td>
                    <td><span class="badge" :class="t.activo ? 'badge-success' : 'badge-secondary'">{{ t.activo ? 'Sí' : 'No' }}</span></td>
                    <td class="text-center"><a @click="editarTipo(t)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminarTipo(t.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <guardar-tipo-incidente></guardar-tipo-incidente>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    export default {
        name: "TiposIncidente",
        mixins: [TablaDatos],
        props: ['datos_tipos'],
        data(){ return { listado: JSON.parse(this.datos_tipos) }; },
        computed: {
            registrosTabla(){ return this.listado; }
        },
        mounted(){
            this.$on('tipo_agregado', (t) => { this.listado.unshift(t); this.notificarExito('Tipo de incidente guardado correctamente'); });
            this.$on('tipo_actualizado', (t) => {
                const i = this.listado.findIndex(x => x.id === t.id);
                if (i > -1) this.listado.splice(i, 1, t);
                this.notificarExito('Tipo de incidente actualizado correctamente');
            });
        },
        methods: {
            crearTipo(){ this.$emit('crear_tipo'); },
            editarTipo(t){ this.$emit('editar_tipo', t); },
            eliminarTipo(id){
                Swal('¿Eliminar este tipo?', { buttons: ['Cancelar', true] }).then(v => {
                    if (!v) return;
                    this.$http.delete('/panel/tipos-incidente/' + id).then(() => {
                        this.listado = this.listado.filter(x => x.id !== id);
                        this.notificarExito('Tipo eliminado');
                    }).catch(err => this.notificarError(this.mensajeDeError(err, 'eliminar el tipo')));
                });
            }
        }
    }
</script>
<style scoped></style>
