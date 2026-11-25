<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="crearRol()" class="btn btn-primary btn-agregar">
                <i class="fas fa-user-plus mr-2"></i>Agregar rol
            </button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nombre del rol</th>
                    <th>Permisos asignados</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="rol, indice in roles" :key="rol.id">
                    <td>{{ indice + 1 }}</td>
                    <td>{{ rol.nombre }}</td>
                    <td><span v-for="permiso in rol.permisos">{{ permiso.nombre }} | </span></td>
                    <td class="text-center"><a @click="editarRol(rol)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminarRol(rol.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th>No</th>
                    <th>Nombre del rol</th>
                    <th>Permisos asignados</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </tfoot>
            </table>
        </div>

        <guardar-rol></guardar-rol>
    </div>
</template>

<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    export default {
        name: "Roles",
        mixins: [TablaDatos],
        props: ['datos_roles', 'datos_permisos'],
        data(){
            return {
                roles: JSON.parse(this.datos_roles),
                permisos: JSON.parse(this.datos_permisos)
            }
        },
        computed: {
            registrosTabla(){ return this.roles; }
        },
        mounted(){
            this.$on('rol_agregado', (rol) => {
                this.roles.unshift(rol);
                this.notificarExito('Rol creado correctamente');
            });
            this.$on('rol_actualizado', (rol) => {
                const indice = this.roles.findIndex(r => r.id === rol.id);
                this.roles.splice(indice, 1, rol);
                this.notificarExito('Rol actualizado correctamente');
            });
        },
        methods: {
            crearRol(){
                this.$emit('crear_rol', this.permisos);
            },
            editarRol(rol){
                this.$emit('editar_rol', rol, this.permisos);
            },
            eliminarRol(id){
                Swal('¿Está seguro de eliminar este rol?', {
                    buttons: ['Cancelar', true]
                }).then(valor => {
                    if (valor) {
                        this.$http.delete(`/panel/roles/${id}`).then(() => {
                            this.roles = this.roles.filter(r => r.id !== id);
                            this.notificarExito('Rol eliminado correctamente');
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
