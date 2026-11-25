<template>
    <div class="modal fade" style="z-index:1000" id="modalRol" data-backdrop="false">
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
                        <label>Nombre del rol</label>
                        <input type="text" :class="{'is-invalid': errores.tieneError('nombre')}"
                               v-model="rol.nombre" class="form-control" placeholder="">
                        <div class="invalid-feedback" v-if="errores.tieneError('nombre')">{{ errores.primero('nombre') }}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Asignar permisos</label>
                        <p style="color: #dc3545; font-size: 80%" v-if="errores.tieneError('permisos')">Seleccione uno o más permisos</p>

                        <div class="selectgroup selectgroup-pills" v-if="permisos.length > 0">
                            <label class="selectgroup-item" v-for="permiso in permisos" :key="permiso.id">
                                <input type="checkbox" :class="{'is-invalid': errores.tieneError('permisos')}" v-model="permisosSeleccionados[permiso.id]" class="selectgroup-input">
                                <span class="selectgroup-button">{{ permiso.nombre }}</span>
                            </label>
                        </div>

                        <p v-else><em>No hay permisos... Agregue uno <a href="/panel/permisos">aquí <i class="fas fa-plus-circle"></i></a></em></p>
                    </div>
                </div>

                <div class="modal-footer" style="padding-top: 0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="actualizarRol(rol.id)" v-if="editando">Actualizar rol</button>
                    <button type="button" @click="guardarRol()" class="btn btn-primary" v-else>Crear rol</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BolsaErrores from '../bolsa_errores'

    class Rol {
        constructor(rol){
            this.nombre = rol.nombre || '';
            this.permisos = rol.permisos || [];
        }
    }

    export default {
        name: "GuardarRol",
        data(){
            return {
                editando: false,
                rol: new Rol({}),
                errores: new BolsaErrores,
                permisos: [],
                permisosSeleccionados: []
            }
        },
        mounted(){
            this.$parent.$on('crear_rol', (permisos) => {
                this.editando = false;
                this.rol = new Rol({});
                this.permisosSeleccionados = [];
                this.errores.limpiarTodo();
                this.permisos = permisos;
                $('#modalRol').modal('show');
            });

            this.$parent.$on('editar_rol', (rol, permisos) => {
                this.editando = true;
                this.rol = new Rol(rol);
                this.rol.id = rol.id;
                this.errores.limpiarTodo();
                this.permisos = permisos;

                this.permisosSeleccionados = [];
                this.rol.permisos.forEach((permiso) => {
                    this.permisosSeleccionados[permiso.id] = true;
                });
                $('#modalRol').modal('show');
            });
        },
        computed: {
            titulo(){
                return this.editando ? 'Editar rol' : 'Crear rol';
            }
        },
        methods: {
            idsSeleccionados(){
                const ids = [];
                for (const [clave, valor] of Object.entries(this.permisosSeleccionados)) {
                    if (valor) ids.push(clave);
                }
                return ids;
            },
            async guardarRol(){
                if (!(await this.confirmar('¿Desea guardar el rol?'))) return;
                this.$http.post('/panel/roles', {
                    nombre: this.rol.nombre,
                    permisos: this.idsSeleccionados()
                }).then(res => {
                    this.$parent.$emit('rol_agregado', res.data);
                    $('#modalRol').modal('hide');
                }).catch(err => this.alFallar(err, 'guardar el rol'));
            },
            async actualizarRol(id){
                if (!(await this.confirmar('¿Desea guardar los cambios del rol?'))) return;
                this.$http.put(`/panel/roles/${id}`, {
                    nombre: this.rol.nombre,
                    permisos: this.idsSeleccionados()
                }).then(res => {
                    this.$parent.$emit('rol_actualizado', res.data);
                    $('#modalRol').modal('hide');
                }).catch(err => this.alFallar(err, 'actualizar el rol'));
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
