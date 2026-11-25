<template>
    <div class="modal fade" id="modalIncidente" data-backdrop="false" style="z-index:1000">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editando ? 'Editar incidente' : 'Reportar incidente' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Título *</label>
                            <input v-model="formulario.titulo" class="form-control" :class="{'is-invalid': errores.tieneError('titulo')}">
                            <div class="invalid-feedback" v-if="errores.tieneError('titulo')">{{ errores.primero('titulo') }}</div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo *</label>
                            <select v-model="formulario.tipo_incidente_id" class="form-control" :class="{'is-invalid': errores.tieneError('tipo_incidente_id')}">
                                <option value="">Seleccione...</option>
                                <option v-for="t in tipos" :value="t.id">{{ t.nombre }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="errores.tieneError('tipo_incidente_id')">{{ errores.primero('tipo_incidente_id') }}</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción *</label>
                        <textarea v-model="formulario.descripcion" rows="3" class="form-control" :class="{'is-invalid': errores.tieneError('descripcion')}"></textarea>
                        <div class="invalid-feedback" v-if="errores.tieneError('descripcion')">{{ errores.primero('descripcion') }}</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Severidad *</label>
                            <select v-model="formulario.severidad" class="form-control">
                                <option v-for="s in severidades" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Estado *</label>
                            <select v-model="formulario.estado" class="form-control">
                                <option v-for="e in estados" :value="e">{{ e }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Asignar a</label>
                            <select v-model="formulario.asignado_a" class="form-control">
                                <option :value="null">Sin asignar</option>
                                <option v-for="u in usuarios" :value="u.id">{{ u.nombre }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Detectado</label>
                            <input type="datetime-local" v-model="formulario.detectado_en" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Reportado por</label>
                            <input v-model="formulario.reportado_por" class="form-control">
                        </div>
                        <div class="form-group col-md-8">
                            <label>Activos afectados</label>
                            <input v-model="formulario.activos_afectados" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Impacto</label>
                        <textarea v-model="formulario.impacto" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Remediación</label>
                        <textarea v-model="formulario.remediacion" rows="2" class="form-control"></textarea>
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
    function formularioVacio(){ return { titulo:'', descripcion:'', tipo_incidente_id:'', severidad:'media', estado:'abierto', reportado_por:'', asignado_a:null, detectado_en:'', activos_afectados:'', impacto:'', remediacion:'' }; }
    export default {
        name: "GuardarIncidente",
        props: ['tipos', 'usuarios', 'severidades', 'estados'],
        data(){ return { editando:false, idEdicion:null, formulario: formularioVacio(), errores: new BolsaErrores }; },
        mounted(){
            this.$parent.$on('crear_incidente', () => {
                this.editando = false; this.idEdicion = null; this.formulario = formularioVacio();
                this.errores.limpiarTodo(); $('#modalIncidente').modal('show');
            });
            this.$parent.$on('editar_incidente', (inc) => {
                this.editando = true; this.idEdicion = inc.id; this.errores.limpiarTodo();
                this.formulario = {
                    titulo: inc.titulo,
                    descripcion: inc.descripcion,
                    tipo_incidente_id: inc.tipo_incidente_id,
                    severidad: inc.severidad,
                    estado: inc.estado,
                    reportado_por: inc.reportado_por || '',
                    asignado_a: inc.asignado_a,
                    detectado_en: inc.detectado_en ? inc.detectado_en.substring(0,16) : '',
                    activos_afectados: inc.activos_afectados || '',
                    impacto: inc.impacto || '',
                    remediacion: inc.remediacion || ''
                };
                $('#modalIncidente').modal('show');
            });
        },
        methods: {
            async guardarNuevo(){
                if (!(await this.confirmar('¿Desea guardar el incidente?'))) return;
                this.$http.post('/panel/incidentes', this.formulario).then(res => {
                    this.$parent.$emit('incidente_agregado', res.data);
                    $('#modalIncidente').modal('hide');
                }).catch(err => this.alFallar(err, 'guardar el incidente'));
            },
            async guardarEdicion(){
                if (!(await this.confirmar('¿Desea guardar los cambios del incidente?'))) return;
                this.$http.put('/panel/incidentes/' + this.idEdicion, this.formulario).then(res => {
                    this.$parent.$emit('incidente_actualizado', res.data);
                    $('#modalIncidente').modal('hide');
                }).catch(err => this.alFallar(err, 'actualizar el incidente'));
            },
            alFallar(err, accion){
                if (err.response && err.response.status == 422) this.errores.establecerErrores(err.response.data.errors);
                this.notificarError(this.mensajeDeError(err, accion));
            }
        }
    }
</script>
<style scoped></style>
