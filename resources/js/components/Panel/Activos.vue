<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="nuevo()" class="btn btn-primary btn-agregar"><i class="fas fa-plus mr-2"></i>Nuevo activo</button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead><tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Área</th><th>Criticidad</th><th>Incidentes</th><th>Activo</th><th>Editar</th><th>Eliminar</th></tr></thead>
                <tbody>
                <tr v-for="a in listado" :key="a.id">
                    <td><strong>{{ a.codigo }}</strong></td>
                    <td>{{ a.nombre }}</td>
                    <td>{{ a.tipo || '-' }}</td>
                    <td>{{ a.area || '-' }}</td>
                    <td :title="descriptor(a.criticidad)"><span class="badge badge-primary">{{ a.criticidad }}</span></td>
                    <td><span class="badge badge-info">{{ a.incidentes_count || 0 }}</span></td>
                    <td><span class="badge" :class="a.activo ? 'badge-success' : 'badge-secondary'">{{ a.activo ? 'Sí' : 'No' }}</span></td>
                    <td class="text-center"><a @click="editar(a)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminar(a.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="modal fade" ref="modal" id="modalActivo" data-backdrop="false" style="z-index:1000">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ idEdicion ? 'Editar activo' : 'Nuevo activo' }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Código *</label>
                                <input v-model="formulario.codigo" class="form-control" :class="{'is-invalid': errores.tieneError('codigo')}">
                                <div class="invalid-feedback" v-if="errores.tieneError('codigo')">{{ errores.primero('codigo') }}</div>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Nombre *</label>
                                <input v-model="formulario.nombre" class="form-control" :class="{'is-invalid': errores.tieneError('nombre')}">
                                <div class="invalid-feedback" v-if="errores.tieneError('nombre')">{{ errores.primero('nombre') }}</div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Tipo</label><input v-model="formulario.tipo" class="form-control" placeholder="Aplicación, servidor, red..."></div>
                            <div class="form-group col-md-6"><label>Área responsable</label><input v-model="formulario.area" class="form-control"></div>
                        </div>
                        <div class="form-group">
                            <label>Criticidad * <small class="text-muted">(escala d de la tesis, valorada según MAGERIT)</small></label>
                            <label v-for="(d, i) in descriptores" :key="i" class="opcion-nivel" :class="{ seleccionada: formulario.criticidad === i + 1 }">
                                <input type="radio" class="d-none" :value="i + 1" v-model="formulario.criticidad">
                                <span class="numero-nivel">{{ i + 1 }}</span><span>{{ d }}</span>
                            </label>
                            <div class="text-danger texto-pequeno" v-if="errores.tieneError('criticidad')">{{ errores.primero('criticidad') }}</div>
                        </div>
                        <div class="form-group"><label>Descripción</label><textarea v-model="formulario.descripcion" rows="2" class="form-control"></textarea></div>
                        <div class="form-check">
                            <input type="checkbox" v-model="formulario.activo" class="form-check-input" id="casillaActivoInventario">
                            <label class="form-check-label" for="casillaActivoInventario">Vigente en el inventario</label>
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
    const vacio = () => ({ codigo:'', nombre:'', tipo:'', area:'', descripcion:'', criticidad:null, activo:true });
    export default {
        name: "Activos",
        mixins: [TablaDatos, ModalEnCuerpo],
        props: ['datos_activos', 'datos_descriptores'],
        data(){
            return { listado: JSON.parse(this.datos_activos), descriptores: JSON.parse(this.datos_descriptores), idEdicion:null, formulario: vacio(), errores: new BolsaErrores };
        },
        computed: { registrosTabla(){ return this.listado; } },
        methods: {
            descriptor(n){ return this.descriptores[n - 1] || ''; },
            nuevo(){ this.idEdicion = null; this.formulario = vacio(); this.errores.limpiarTodo(); $('#modalActivo').modal('show'); },
            editar(a){
                this.idEdicion = a.id; this.errores.limpiarTodo();
                this.formulario = { codigo:a.codigo, nombre:a.nombre, tipo:a.tipo||'', area:a.area||'', descripcion:a.descripcion||'', criticidad:a.criticidad, activo:!!a.activo };
                $('#modalActivo').modal('show');
            },
            async guardar(){
                const pregunta = this.idEdicion ? '¿Desea guardar los cambios del activo?' : '¿Desea guardar el activo?';
                const detalle = this.idEdicion ? 'Si cambia la criticidad, recalcule los incidentes afectados desde su detalle.' : '';
                if (!(await this.confirmar(pregunta, detalle))) return;
                const peticion = this.idEdicion ? this.$http.put('/panel/activos/' + this.idEdicion, this.formulario) : this.$http.post('/panel/activos', this.formulario);
                peticion.then(res => {
                    if (this.idEdicion) {
                        const i = this.listado.findIndex(x => x.id === res.data.id);
                        this.listado.splice(i, 1, res.data);
                    } else this.listado.unshift(res.data);
                    $('#modalActivo').modal('hide');
                    this.notificarExito('Activo guardado correctamente');
                }).catch(err => {
                    if (err.response && err.response.status == 422) this.errores.establecerErrores(err.response.data.errors);
                    this.notificarError(this.mensajeDeError(err, 'guardar el activo'));
                });
            },
            eliminar(id){
                Swal('¿Eliminar este activo?', { buttons: ['Cancelar', true] }).then(v => {
                    if (!v) return;
                    this.$http.delete('/panel/activos/' + id).then(() => {
                        this.listado = this.listado.filter(x => x.id !== id);
                        this.notificarExito('Activo eliminado');
                    }).catch(e => this.notificarError(this.mensajeDeError(e, 'eliminar el activo')));
                });
            }
        }
    }
</script>
