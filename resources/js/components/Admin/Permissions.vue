<template>
    <div>

        <div class="float-right mb-4">
            <button style="padding: 10px" @click="createPermission()" class=" pull-right btn btn-primary">
                <i class="fas fa-user-plus" style="padding-right: 10px"></i>Agregar permiso
            </button>
        </div>
        <br>
        <div class="box-body">

            <table id="example1"  class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nombre del permiso</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="permission, key in permissions">
                    <td>{{key + 1}}</td>
                    <td>{{permission.name}}</td>
                    <td><a @click="editPermission(permission)"><i class="text-center fas fa-edit"></i></a></td>
                    <td><a @click="deletePermission(permission.id, key)"><span class="text-center fas fa-trash" ></span></a></td>
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

        <store-permission></store-permission>

    </div>

</template>

<script>
    import Swal from 'sweetalert'
    export default {
        name: "Permissions",

        props: ['raw_permissions'],

        data(){

            return{

                permissions: JSON.parse(this.raw_permissions)

            }
        },

        mounted(){

            this.$on('add_permission', (permission) => {

                this.permissions.push(permission);
                this.notifSuceess('Permiso creado correctamente')

            });


            this.$on('update_permission', (permission) => {

                const index = this.permissions.findIndex(item => item.id === permission.id);
                this.permissions.splice(index, 1, permission);

                this.notifSuceess('Permiso actualizado correctamente')
            })

        },

        computed: {


        },

        methods: {

            createPermission(){

                this.$emit('create_permission');

            },

            editPermission(permission){

                this.$emit('edit_permission', permission)

            },

            deletePermission(id, key){

                Swal('¿Está seguro de eliminar este permiso?', {
                    buttons: ['Cancelar', true]
                }).then(value => {
                    if (value) {
                        this.$http.delete(`/admin/permission/${id}`).then(res => {

                            this.permissions.splice(key, 1);
                            this.notifSuceess('Permiso eliminado correctamente');

                        }).catch(err => {

                            this.notifError( err.message || 'Ocurrió un error')
                        })

                    }
                })

            }
        }
    }
</script>

<style scoped>

</style>
