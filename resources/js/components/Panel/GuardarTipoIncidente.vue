<template>
    <div class="modal fade" id="modalTipoIncidente" data-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editando ? 'Editar tipo' : 'Nuevo tipo' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input v-model="formulario.nombre" class="form-control" :class="{'is-invalid': errores.tieneError('nombre')}">
                        <div class="invalid-feedback" v-if="errores.tieneError('nombre')">{{ errores.primero('nombre') }}</div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea v-model="formulario.descripcion" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" v-model="formulario.activo" class="form-check-input" id="casillaActivo">
                        <label class="form-check-label" for="casillaActivo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer" style="padding-top:0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button v-if="editando" @click="guardarEdicion()" class="btn btn-primary">Actualizar</button>
                    <button v-else @click="guardarNuevo()" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import BolsaErrores from '../bolsa_errores'
    export default {
        name: "GuardarTipoIncidente",
        data(){ return { editando:false, idEdicion:null, formulario:{nombre:'',descripcion:'',activo:true}, errores:new BolsaErrores }; },
        mounted(){
            this.$parent.$on('crear_tipo', () => {
                this.editando=false; this.idEdicion=null;
                this.formulario={nombre:'',descripcion:'',activo:true};
                this.errores.limpiarTodo(); $('#modalTipoIncidente').modal('show');
            });
            this.$parent.$on('editar_tipo', (t) => {
                this.editando=true; this.idEdicion=t.id;
                this.formulario={nombre:t.nombre,descripcion:t.descripcion||'',activo:!!t.activo};
                this.errores.limpiarTodo(); $('#modalTipoIncidente').modal('show');
            });
        },
        methods: {
            async guardarNuevo(){
                if (!(await this.confirmar('¿Desea guardar el tipo de incidente?'))) return;
                this.$http.post('/panel/tipos-incidente', this.formulario).then(res => {
                    this.$parent.$emit('tipo_agregado', res.data);
                    $('#modalTipoIncidente').modal('hide');
                }).catch(e => this.alFallar(e, 'guardar el tipo de incidente'));
            },
            async guardarEdicion(){
                if (!(await this.confirmar('¿Desea guardar los cambios del tipo de incidente?'))) return;
                this.$http.put('/panel/tipos-incidente/' + this.idEdicion, this.formulario).then(res => {
                    this.$parent.$emit('tipo_actualizado', res.data);
                    $('#modalTipoIncidente').modal('hide');
                }).catch(e => this.alFallar(e, 'actualizar el tipo de incidente'));
            },
            alFallar(err, accion){
                if (err.response && err.response.status == 422) this.errores.establecerErrores(err.response.data.errors);
                this.notificarError(this.mensajeDeError(err, accion));
            }
        }
    }
</script>
<style scoped></style>
