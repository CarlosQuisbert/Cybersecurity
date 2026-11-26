<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="nueva()" class="btn btn-primary btn-agregar"><i class="fas fa-plus mr-2"></i>Nueva táctica</button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead><tr><th>#</th><th>Táctica</th><th>Objetivo</th><th>Progresión</th><th>Incidentes</th><th>Activa</th><th>Editar</th><th>Eliminar</th></tr></thead>
                <tbody>
                <tr v-for="t in listado" :key="t.id">
                    <td>{{ t.orden }}</td>
                    <td><strong>{{ t.nombre }}</strong><div><code>{{ t.identificador }}</code></div></td>
                    <td class="texto-pequeno">{{ t.objetivo || '-' }}</td>
                    <td><span class="badge badge-primary">{{ t.nivel_progresion }}</span> {{ etapas[t.nivel_progresion] }}</td>
                    <td><span class="badge badge-info">{{ t.incidentes_count || 0 }}</span></td>
                    <td><span class="badge" :class="t.activo ? 'badge-success' : 'badge-secondary'">{{ t.activo ? 'Sí' : 'No' }}</span></td>
                    <td class="text-center"><a @click="editar(t)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminar(t.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="modal fade" ref="modal" id="modalTactica" data-backdrop="false" style="z-index:1000">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ idEdicion ? 'Editar táctica' : 'Nueva táctica' }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label>Identificador *</label>
                                <input v-model="formulario.identificador" class="form-control" placeholder="TA0005" :class="{'is-invalid': errores.tieneError('identificador')}">
                                <div class="invalid-feedback" v-if="errores.tieneError('identificador')">{{ errores.primero('identificador') }}</div>
                            </div>
                            <div class="form-group col-md-7">
                                <label>Nombre *</label>
                                <input v-model="formulario.nombre" class="form-control" :class="{'is-invalid': errores.tieneError('nombre')}">
                                <div class="invalid-feedback" v-if="errores.tieneError('nombre')">{{ errores.primero('nombre') }}</div>
                            </div>
                        </div>
                        <div class="form-group"><label>Objetivo que persigue el adversario</label><textarea v-model="formulario.objetivo" rows="2" class="form-control"></textarea></div>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label>Nivel de progresión *</label>
                                <select v-model="formulario.nivel_progresion" class="form-control" :class="{'is-invalid': errores.tieneError('nivel_progresion')}">
                                    <option v-for="(etapa, n) in etapas" :value="Number(n)">{{ n }} · {{ etapa }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4"><label>Orden</label><input type="number" v-model.number="formulario.orden" class="form-control"></div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" v-model="formulario.activo" class="form-check-input" id="casillaTacticaActiva">
                            <label class="form-check-label" for="casillaTacticaActiva">Activa en el catálogo</label>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding-top:0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button @click="guardar()" class="btn btn-primary">{{ idEdicion ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    import ModalEnCuerpo from '../../mixins/modal_en_cuerpo'
    import BolsaErrores from '../bolsa_errores'
    const vacia = () => ({ identificador:'', nombre:'', objetivo:'', nivel_progresion:3, orden:null, activo:true });
    export default {
        name: "Tacticas",
        mixins: [TablaDatos, ModalEnCuerpo],
        props: ['datos_tacticas'],
        data(){
            return {
                listado: JSON.parse(this.datos_tacticas), idEdicion:null, formulario: vacia(), errores: new BolsaErrores,
                etapas: { 1:'Preparación previa', 2:'Acceso inicial', 3:'Consolidación', 4:'Expansión', 5:'Consumación' },
            };
        },
        computed: { registrosTabla(){ return this.listado; } },
        methods: {
            nueva(){ this.idEdicion = null; this.formulario = vacia(); this.errores.limpiarTodo(); $('#modalTactica').modal('show'); },
            editar(t){
                this.idEdicion = t.id; this.errores.limpiarTodo();
                this.formulario = { identificador:t.identificador, nombre:t.nombre, objetivo:t.objetivo||'', nivel_progresion:t.nivel_progresion, orden:t.orden, activo:!!t.activo };
                $('#modalTactica').modal('show');
            },
            async guardar(){
                if (!(await this.confirmar(this.idEdicion ? '¿Desea guardar los cambios de la táctica?' : '¿Desea guardar la táctica?'))) return;
                const peticion = this.idEdicion ? this.$http.put('/panel/tacticas/' + this.idEdicion, this.formulario) : this.$http.post('/panel/tacticas', this.formulario);
                peticion.then(res => {
                    if (this.idEdicion) {
                        const i = this.listado.findIndex(x => x.id === res.data.id);
                        this.listado.splice(i, 1, res.data);
                    } else this.listado.push(res.data);
                    $('#modalTactica').modal('hide');
                    this.notificarExito('Táctica guardada correctamente');
                }).catch(err => {
                    if (err.response && err.response.status == 422) this.errores.establecerErrores(err.response.data.errors);
                    this.notificarError(this.mensajeDeError(err, 'guardar la táctica'));
                });
            },
            eliminar(id){
                Swal('¿Eliminar esta táctica?', { buttons: ['Cancelar', true] }).then(v => {
                    if (!v) return;
                    this.$http.delete('/panel/tacticas/' + id).then(() => {
                        this.listado = this.listado.filter(x => x.id !== id);
                        this.notificarExito('Táctica eliminada');
                    }).catch(e => this.notificarError(this.mensajeDeError(e, 'eliminar la táctica')));
                });
            }
        }
    }
</script>
