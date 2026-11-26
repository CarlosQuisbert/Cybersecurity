<template>
    <div class="modal fade" id="modalIncidente" data-backdrop="false" style="z-index:1000">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editando ? 'Editar incidente' : 'Reportar incidente' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Registro</h6>
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Título *</label>
                            <input v-model="formulario.titulo" class="form-control" :class="{'is-invalid': errores.tieneError('titulo')}">
                            <div class="invalid-feedback" v-if="errores.tieneError('titulo')">{{ errores.primero('titulo') }}</div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Detectado</label>
                            <input type="datetime-local" v-model="formulario.detectado_en" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción *</label>
                        <textarea v-model="formulario.descripcion" rows="3" class="form-control" :class="{'is-invalid': errores.tieneError('descripcion')}"></textarea>
                        <div class="invalid-feedback" v-if="errores.tieneError('descripcion')">{{ errores.primero('descripcion') }}</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Reportado por</label>
                            <input v-model="formulario.reportado_por" class="form-control" placeholder="Persona, unidad o herramienta de detección">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Activo afectado (inventario)</label>
                            <select v-model="formulario.activo_id" class="form-control" :class="{'is-invalid': errores.tieneError('activo_id')}">
                                <option :value="null">No figura en el inventario</option>
                                <option v-for="a in activos" :value="a.id">{{ a.codigo }} · {{ a.nombre }} (criticidad {{ a.criticidad }})</option>
                            </select>
                            <small class="form-text text-muted" v-if="!formulario.activo_id">Se valorará con criticidad 1 y se registrará la omisión del inventario.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Otros activos o detalle del activo afectado</label>
                        <input v-model="formulario.activos_afectados" class="form-control">
                    </div>

                    <h6 class="text-primary mt-2">Clasificación</h6>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Calificación *</label>
                            <select v-model="formulario.calificacion" class="form-control">
                                <option value="por_calificar">Evento por calificar</option>
                                <option value="incidente">Incidente confirmado</option>
                                <option value="descartado">Evento descartado</option>
                            </select>
                            <small class="form-text text-muted">Solo los incidentes confirmados entran a la cola de prioridad.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Categoría *</label>
                            <select v-model="formulario.tipo_incidente_id" class="form-control" :class="{'is-invalid': errores.tieneError('tipo_incidente_id')}">
                                <option value="">Seleccione...</option>
                                <option v-for="t in tipos" :value="t.id">{{ t.nombre }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="errores.tieneError('tipo_incidente_id')">{{ errores.primero('tipo_incidente_id') }}</div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Origen *</label>
                            <select v-model="formulario.origen" class="form-control" :class="{'is-invalid': errores.tieneError('origen')}">
                                <option :value="null">Seleccione...</option>
                                <option value="adversario">Adversario (ataque)</option>
                                <option value="no_adversario">No adversario (error, falla, vulnerabilidad)</option>
                            </select>
                            <div class="invalid-feedback" v-if="errores.tieneError('origen')">{{ errores.primero('origen') }}</div>
                        </div>
                    </div>
                    <div class="form-group" v-if="formulario.origen === 'adversario'">
                        <label>Tácticas ATT&amp;CK evidenciadas</label>
                        <small class="form-text text-muted mb-2">Marque las tácticas con evidencia; el modelo usa la de mayor progresión.</small>
                        <div class="row">
                            <div class="col-md-6" v-for="t in tacticas" :key="t.id">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" :id="'tactica' + t.id" :value="t.id" v-model="formulario.tacticas">
                                    <label class="custom-control-label" :for="'tactica' + t.id">
                                        {{ t.nombre }} <small class="text-muted">({{ t.identificador }} · nivel {{ t.nivel_progresion }})</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-if="puede('incidentes.asignar')">
                        <h6 class="text-primary mt-2">Seguimiento</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Estado</label>
                                <select v-model="formulario.estado" class="form-control">
                                    <option v-for="e in estados" :value="e">{{ etiquetaEstado(e) }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Asignar a</label>
                                <select v-model="formulario.asignado_a" class="form-control">
                                    <option :value="null">Sin asignar</option>
                                    <option v-for="u in usuarios" :value="u.id">{{ u.nombre }}</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <h6 class="text-primary mt-2">Descripción del daño</h6>
                    <div class="form-group">
                        <label>Impacto observado</label>
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
    function formularioVacio(){
        return {
            titulo:'', descripcion:'', tipo_incidente_id:'', calificacion:'incidente', origen:null, tacticas:[],
            activo_id:null, estado:'abierto', reportado_por:'', asignado_a:null, detectado_en:'',
            activos_afectados:'', impacto:'', remediacion:''
        };
    }
    export default {
        name: "GuardarIncidente",
        props: ['tipos', 'usuarios', 'tacticas', 'activos'],
        data(){
            return {
                editando:false, idEdicion:null, formulario: formularioVacio(), errores: new BolsaErrores,
                estados: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
            };
        },
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
                    calificacion: inc.calificacion || 'incidente',
                    origen: inc.origen,
                    tacticas: (inc.tacticas || []).map(t => t.id),
                    activo_id: inc.activo_id,
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
            datos(){
                const d = Object.assign({}, this.formulario);
                if (d.origen !== 'adversario') d.tacticas = [];
                return d;
            },
            async guardarNuevo(){
                if (!(await this.confirmar('¿Desea guardar el incidente?'))) return;
                this.$http.post('/panel/incidentes', this.datos()).then(res => {
                    this.$parent.$emit('incidente_agregado', res.data);
                    $('#modalIncidente').modal('hide');
                }).catch(err => this.alFallar(err, 'guardar el incidente'));
            },
            async guardarEdicion(){
                if (!(await this.confirmar('¿Desea guardar los cambios del incidente?', 'Si cambia el origen, las tácticas o el activo, la prioridad se recalcula.'))) return;
                this.$http.put('/panel/incidentes/' + this.idEdicion, this.datos()).then(res => {
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
