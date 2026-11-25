<template>
    <div class="modal fade" id="modalPermiso" data-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ titulo }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del permiso</label>
                        <input type="text" :class="{'is-invalid': errores.tieneError('nombre')}" v-model="permiso.nombre"
                               class="form-control" placeholder="">
                        <div class="invalid-feedback" v-if="errores.tieneError('nombre')">{{ errores.primero('nombre') }}</div>
                    </div>
                </div>

                <div class="modal-footer" style="padding-top: 0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="actualizarPermiso(permiso.id)" v-if="editando">Actualizar permiso</button>
                    <button type="button" @click="guardarPermiso()" class="btn btn-primary" v-else>Crear permiso</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BolsaErrores from '../bolsa_errores'

    class Permiso {
        constructor(permiso){
            this.nombre = permiso.nombre || '';
        }
    }

    export default {
        name: "GuardarPermiso",
        data(){
            return {
                editando: false,
                permiso: new Permiso({}),
                errores: new BolsaErrores
            }
        },
        mounted(){
            this.$parent.$on('crear_permiso', () => {
                this.editando = false;
                this.permiso = new Permiso({});
                this.errores.limpiarTodo();
                $('#modalPermiso').modal('show');
            });

            this.$parent.$on('editar_permiso', (permiso) => {
                this.editando = true;
                this.permiso = new Permiso(permiso);
                this.permiso.id = permiso.id;
                this.errores.limpiarTodo();
                $('#modalPermiso').modal('show');
            });
        },
        computed: {
            titulo(){
                return this.editando ? 'Editar permiso' : 'Crear permiso';
            }
        },
        methods: {
            async guardarPermiso(){
                if (!(await this.confirmar('¿Desea guardar el permiso?'))) return;
                this.$http.post('/panel/permisos', { nombre: this.permiso.nombre }).then(res => {
                    this.$parent.$emit('permiso_agregado', res.data);
                    $('#modalPermiso').modal('hide');
                }).catch(err => this.alFallar(err, 'guardar el permiso'));
            },
            async actualizarPermiso(id){
                if (!(await this.confirmar('¿Desea guardar los cambios del permiso?'))) return;
                this.$http.put(`/panel/permisos/${id}`, { nombre: this.permiso.nombre }).then(res => {
                    this.$parent.$emit('permiso_actualizado', res.data);
                    $('#modalPermiso').modal('hide');
                }).catch(err => this.alFallar(err, 'actualizar el permiso'));
            },
            alFallar(err, accion){
                if (err.response && err.response.status == 422) {
                    this.errores.establecerErrores(err.response.data.errors);
                }
                this.notificarError(this.mensajeDeError(err, accion));
            }
        }
    }
</script>

<style scoped></style>
