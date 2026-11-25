<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="crearPermiso()" class="btn btn-primary btn-agregar">
                <i class="fas fa-user-plus mr-2"></i>Agregar permiso
            </button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nombre del permiso</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="permiso, indice in permisos" :key="permiso.id">
                    <td>{{ indice + 1 }}</td>
                    <td>{{ permiso.nombre }}</td>
                    <td class="text-center"><a @click="editarPermiso(permiso)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminarPermiso(permiso.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th>No</th>
                    <th>Nombre del permiso</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </tfoot>
            </table>
        </div>

        <guardar-permiso></guardar-permiso>
    </div>
</template>

<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    export default {
        name: "Permisos",
        mixins: [TablaDatos],
        props: ['datos_permisos'],
        data(){
            return {
                permisos: JSON.parse(this.datos_permisos)
            }
        },
        computed: {
            registrosTabla(){ return this.permisos; }
        },
        mounted(){
            this.$on('permiso_agregado', (permiso) => {
                this.permisos.unshift(permiso);
                this.notificarExito('Permiso creado correctamente');
            });
            this.$on('permiso_actualizado', (permiso) => {
                const indice = this.permisos.findIndex(p => p.id === permiso.id);
                this.permisos.splice(indice, 1, permiso);
                this.notificarExito('Permiso actualizado correctamente');
            });
        },
        methods: {
            crearPermiso(){
                this.$emit('crear_permiso');
            },
            editarPermiso(permiso){
                this.$emit('editar_permiso', permiso);
            },
            eliminarPermiso(id){
                Swal('¿Está seguro de eliminar este permiso?', {
                    buttons: ['Cancelar', true]
                }).then(valor => {
                    if (valor) {
                        this.$http.delete(`/panel/permisos/${id}`).then(() => {
                            this.permisos = this.permisos.filter(p => p.id !== id);
                            this.notificarExito('Permiso eliminado correctamente');
                        }).catch(err => {
                            this.notificarError(err.message || 'Ocurrió un error');
                        });
                    }
                });
            }
        }
    }
</script>

<style scoped></style>
