<template>
    <div>
        <div class="row">
            <!-- Ficha -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Ficha del incidente</h4>
                        <button v-if="puede('incidentes.editar')" class="btn btn-sm btn-outline-primary" @click="$emit('editar_incidente', incidente)">
                            <i class="fas fa-edit mr-1"></i>Editar registro y clasificación
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{{ incidente.descripcion }}</p>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Calificación:</strong> {{ etiquetaCalificacion(incidente.calificacion) }}</p>
                                <p class="mb-2"><strong>Categoría:</strong> {{ incidente.tipo ? incidente.tipo.nombre : '-' }}</p>
                                <p class="mb-2"><strong>Origen:</strong> {{ etiquetaOrigen(incidente.origen) }}</p>
                                <p class="mb-2" v-if="incidente.origen === 'adversario'"><strong>Tácticas ATT&amp;CK:</strong>
                                    <span v-if="!incidente.tacticas.length" class="text-danger">sin táctica registrada</span>
                                    <span v-for="t in incidente.tacticas" :key="t.id" class="badge badge-light mr-1" :title="t.objetivo">
                                        {{ t.nombre }} · nivel {{ t.nivel_progresion }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Activo:</strong>
                                    <span v-if="incidente.activo">{{ incidente.activo.codigo }} · {{ incidente.activo.nombre }} (criticidad {{ incidente.activo.criticidad }})</span>
                                    <span v-else class="text-warning">No figura en el inventario</span>
                                </p>
                                <p class="mb-2"><strong>Otros activos:</strong> {{ incidente.activos_afectados || '-' }}</p>
                                <p class="mb-2"><strong>Detectado:</strong> {{ formatoFecha(incidente.detectado_en) }}</p>
                                <p class="mb-2"><strong>Reportado por:</strong> {{ incidente.reportado_por || '-' }}</p>
                            </div>
                        </div>
                        <p class="mb-2" v-if="incidente.impacto"><strong>Impacto observado:</strong> {{ incidente.impacto }}</p>
                        <p class="mb-0" v-if="incidente.remediacion"><strong>Remediación:</strong> {{ incidente.remediacion }}</p>
                    </div>
                </div>
            </div>

            <!-- Prioridad -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h4>Prioridad calculada</h4></div>
                    <div class="card-body text-center">
                        <div class="indice-riesgo" style="font-size:44px;line-height:1">{{ formatoIndice(incidente.indice_riesgo) }}</div>
                        <div class="text-muted texto-pequeno mb-2">Índice de riesgo (0 a 100)</div>
                        <span class="badge" style="font-size:15px" :class="clasePrioridad(incidente.nivel_prioridad)">{{ etiquetaPrioridad(incidente.nivel_prioridad) }}</span>
                        <div v-if="ultima" class="texto-pequeno text-muted mt-3">
                            Configuración v{{ ultima.detalle.configuracion_version }} ·
                            {{ formatoFecha(ultima.creado_en) }} ·
                            {{ ultima.analista ? ultima.analista.nombre : 'Sistema' }}
                        </div>
                        <div v-if="ultima && configuracion && ultima.configuracion_id !== configuracion.id" class="alert alert-warning texto-pequeno mt-2 mb-0">
                            Calculado con la versión {{ ultima.detalle.configuracion_version }}; la vigente es la {{ configuracion.version }}.
                        </div>
                        <div v-if="incidente.calificacion !== 'incidente'" class="alert alert-light texto-pequeno mt-3 mb-0">
                            Solo los eventos calificados como <strong>incidente</strong> se evalúan y entran a la cola de prioridad.
                        </div>
                        <button v-else-if="puede('incidentes.evaluar')" class="btn btn-primary btn-block mt-3" @click="abrirEvaluacion()">
                            <i class="fas fa-calculator mr-1"></i>{{ ultima ? 'Reevaluar' : 'Evaluar criterios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle del cálculo -->
        <div class="card" v-if="ultima">
            <div class="card-header"><h4>Detalle del cálculo</h4></div>
            <div class="card-body">
                <p class="texto-pequeno text-muted">
                    R = 100 · Σ peso × valor, con valor = (nivel − 1) / 4.
                    <span v-if="ultima.progresion_excluida"> Incidente sin origen adversario: la progresión adversaria se excluye y su peso se redistribuye de forma proporcional.</span>
                    <span v-if="ultima.omision_inventario" class="text-warning"> El activo no figura en el inventario: se valoró con criticidad 1 y se registró la omisión.</span>
                </p>
                <table class="table table-sm table-striped" data-apilable ref="tablaDetalle">
                    <thead><tr><th>Bloque</th><th>Criterio</th><th>Nivel</th><th>Peso aplicado</th><th>Aporte</th></tr></thead>
                    <tbody>
                    <tr v-for="(c, codigo) in ultima.detalle.criterios" :key="codigo">
                        <td>{{ c.bloque }}</td>
                        <td><strong>{{ c.nombre }}</strong></td>
                        <td>
                            <span v-if="c.fuente === 'excluido'" class="text-muted">Excluido</span>
                            <template v-else>
                                <strong>{{ c.nivel }}</strong>
                                <div v-for="e in c.escalas" :key="e.escala_id" class="texto-pequeno text-muted">
                                    <span v-if="c.escalas.length > 1">{{ e.nombre }}: nivel {{ e.nivel }} — </span>{{ e.descriptor }}
                                    <em v-if="c.fuente === 'inventario'"> (inventario)</em>
                                    <em v-if="c.fuente === 'tacticas'"> (táctica más avanzada)</em>
                                </div>
                            </template>
                        </td>
                        <td>{{ c.peso_aplicado !== undefined ? (c.peso_aplicado * 100).toFixed(1).replace('.', ',') + ' %' : '-' }}</td>
                        <td style="min-width:120px">
                            <template v-if="c.aporte !== undefined">
                                <div class="indice-riesgo">{{ formatoIndice(c.aporte) }}</div>
                                <div class="barra-aporte" :style="{ width: Math.max(2, c.aporte / maximoAporte * 100) + '%' }"></div>
                            </template>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Seguimiento -->
                <div class="card">
                    <div class="card-header"><h4>Asignación y seguimiento</h4></div>
                    <div class="card-body">
                        <p><strong>Estado:</strong> <span class="badge badge-info">{{ etiquetaEstado(incidente.estado) }}</span>
                            &nbsp; <strong>Responsable:</strong> {{ incidente.usuario_asignado ? incidente.usuario_asignado.nombre : 'Sin asignar' }}</p>
                        <template v-if="puede('incidentes.asignar')">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Estado</label>
                                    <select v-model="seguimiento.estado" class="form-control">
                                        <option v-for="e in estados" :value="e">{{ etiquetaEstado(e) }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Responsable</label>
                                    <select v-model="seguimiento.asignado_a" class="form-control">
                                        <option :value="null">Sin asignar</option>
                                        <option v-for="u in usuarios" :value="u.id">{{ u.nombre }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Nota</label>
                                    <input v-model="seguimiento.notas" class="form-control" placeholder="Motivo...">
                                </div>
                            </div>
                            <button @click="guardarSeguimiento()" class="btn btn-warning">Guardar seguimiento</button>
                        </template>
                        <p v-else class="text-muted texto-pequeno">La asignación y el cambio de estado los realiza el responsable de seguridad.</p>
                        <hr>
                        <h6>Historial de estados</h6>
                        <ul class="list-group">
                            <li v-for="h in incidente.historial" :key="h.id" class="list-group-item">
                                <strong>{{ h.estado_anterior ? etiquetaEstado(h.estado_anterior) : 'Registro' }} → {{ etiquetaEstado(h.estado_nuevo) }}</strong>
                                <span class="text-muted texto-pequeno"> · {{ formatoFecha(h.creado_en) }} · {{ h.responsable ? h.responsable.nombre : '-' }}</span>
                                <div v-if="h.notas" class="texto-pequeno">{{ h.notas }}</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Evidencias -->
                <div class="card">
                    <div class="card-header"><h4>Evidencias</h4></div>
                    <div class="card-body">
                        <div v-if="puede('evidencias.adjuntar')" class="form-row align-items-end">
                            <div class="form-group col-md-5">
                                <label>Archivo (máx. 20 MB)</label>
                                <input type="file" ref="archivo" class="form-control-file">
                            </div>
                            <div class="form-group col-md-5">
                                <label>Descripción</label>
                                <input v-model="descripcionEvidencia" class="form-control" placeholder="Qué contiene y de dónde se obtuvo">
                            </div>
                            <div class="form-group col-md-2">
                                <button class="btn btn-primary btn-block" @click="adjuntarEvidencia()" :disabled="subiendo">Adjuntar</button>
                            </div>
                        </div>
                        <table class="table table-sm" data-apilable ref="tablaEvidencias">
                            <thead><tr><th>Archivo</th><th>Descripción</th><th>SHA-256</th><th>Registrado</th><th></th></tr></thead>
                            <tbody>
                            <tr v-for="e in evidencias" :key="e.id">
                                <td>{{ e.nombre_original }} <span class="text-muted texto-pequeno">({{ tamano(e.tamano) }})</span></td>
                                <td>{{ e.descripcion || '-' }}</td>
                                <td><code :title="e.hash_sha256">{{ e.hash_sha256.substring(0, 12) }}…</code></td>
                                <td class="texto-pequeno">{{ formatoFecha(e.creado_en) }}<br>{{ e.autor ? e.autor.nombre : '-' }}</td>
                                <td><a class="btn btn-sm btn-light" :href="'/panel/incidentes/' + incidente.id + '/evidencias/' + e.id + '/descargar'"><i class="fas fa-download"></i></a></td>
                            </tr>
                            </tbody>
                        </table>
                        <p v-if="!evidencias.length" class="text-muted">Sin evidencias.</p>
                        <p class="texto-pequeno text-muted mb-0">Las evidencias no se eliminan; cada carga y descarga queda en la bitácora con su huella SHA-256.</p>
                    </div>
                </div>

                <!-- Historial de evaluaciones -->
                <div class="card" v-if="incidente.evaluaciones && incidente.evaluaciones.length">
                    <div class="card-header"><h4>Historial de evaluaciones</h4></div>
                    <div class="card-body">
                        <table class="table table-sm" data-apilable ref="tablaEvaluaciones">
                            <thead><tr><th>Fecha</th><th>Índice</th><th>Prioridad</th><th>Configuración</th><th>Analista</th><th>Motivo</th></tr></thead>
                            <tbody>
                            <tr v-for="ev in incidente.evaluaciones" :key="ev.id">
                                <td>{{ formatoFecha(ev.creado_en) }}</td>
                                <td class="indice-riesgo">{{ formatoIndice(ev.indice_riesgo) }}</td>
                                <td><span class="badge" :class="clasePrioridad(ev.nivel_prioridad)">{{ etiquetaPrioridad(ev.nivel_prioridad) }}</span></td>
                                <td>v{{ ev.configuracion ? ev.configuracion.version : '?' }}</td>
                                <td>{{ ev.analista ? ev.analista.nombre : '-' }}</td>
                                <td class="texto-pequeno">{{ ev.motivo || '-' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h4>Notas de seguimiento</h4></div>
                    <div class="card-body">
                        <template v-if="puede('incidentes.editar')">
                            <div class="form-group">
                                <textarea v-model="nuevoComentario" rows="3" class="form-control" placeholder="Agregar nota..."></textarea>
                            </div>
                            <button @click="agregarComentario()" class="btn btn-primary btn-block">Agregar comentario</button>
                            <hr>
                        </template>
                        <div v-for="c in comentarios" :key="c.id" class="alert alert-light">
                            <strong>{{ c.autor ? c.autor.nombre : 'Sistema' }}</strong>
                            <small class="text-muted"> - {{ formatoFecha(c.creado_en) }}</small>
                            <p class="mb-1">{{ c.comentario }}</p>
                            <a v-if="puede('incidentes.editar')" @click="eliminarComentario(c.id)" class="text-danger" style="cursor:pointer"><small>eliminar</small></a>
                        </div>
                        <p v-if="!comentarios.length" class="text-muted">Sin comentarios.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de evaluación multicriterio -->
        <div class="modal fade" ref="modalEvaluacion" id="modalEvaluacion" data-backdrop="false" style="z-index:1000">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Evaluación multicriterio · {{ incidente.codigo }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light d-flex flex-wrap justify-content-between align-items-center" style="position:sticky;top:-16px;z-index:2;border:1px solid #e4e6fc">
                            <div>Elija el nivel cuyo descriptor corresponde a la evidencia disponible. <span class="text-muted">{{ completas }} de {{ escalasAnalista.length }} escalas valoradas.</span></div>
                            <div v-if="vistaPrevia">
                                Vista previa: <span class="indice-riesgo">{{ formatoIndice(vistaPrevia.indice) }}</span>
                                <span class="badge" :class="clasePrioridad(vistaPrevia.nivel)">{{ etiquetaPrioridad(vistaPrevia.nivel) }}</span>
                            </div>
                        </div>
                        <div v-for="criterio in estructura" :key="criterio.id" class="mb-4">
                            <h6 class="mb-1">{{ criterio.nombre }} <small class="text-muted">· {{ criterio.bloque.nombre }}</small></h6>
                            <p v-if="criterio.descripcion" class="texto-pequeno text-muted mb-2">{{ criterio.descripcion }}</p>

                            <div v-if="criterio.fuente === 'inventario'" class="alert alert-secondary texto-pequeno">
                                <template v-if="incidente.activo">Nivel <strong>{{ incidente.activo.criticidad }}</strong> desde el inventario: {{ incidente.activo.codigo }} · {{ incidente.activo.nombre }}.</template>
                                <template v-else>Nivel <strong>1</strong>: el activo no figura en el inventario; se registrará la omisión.</template>
                            </div>
                            <div v-else-if="criterio.fuente === 'tacticas'" class="alert alert-secondary texto-pequeno">
                                <template v-if="incidente.origen !== 'adversario'">Excluido: incidente sin origen adversario. Su peso se redistribuye entre los demás criterios.</template>
                                <template v-else-if="nivelProgresion">Nivel <strong>{{ nivelProgresion }}</strong>: táctica más avanzada evidenciada ({{ tacticaMasAvanzada }}).</template>
                                <template v-else><span class="text-danger">Registre al menos una táctica ATT&amp;CK (Editar clasificación) antes de evaluar.</span></template>
                            </div>
                            <template v-else>
                                <div v-for="escala in criterio.escalas" :key="escala.id" class="mb-2">
                                    <div v-if="criterio.escalas.length > 1" class="font-weight-bold texto-pequeno mb-1">{{ escala.nombre }}</div>
                                    <div v-if="escala.nota" class="texto-pequeno text-muted mb-1">{{ escala.nota }}</div>
                                    <label v-for="n in escala.niveles" :key="n.id" class="opcion-nivel" :class="{ seleccionada: valores[escala.id] === n.nivel }">
                                        <input type="radio" class="d-none" :name="'escala' + escala.id" :value="n.nivel" v-model="valores[escala.id]">
                                        <span class="numero-nivel">{{ n.nivel }}</span>
                                        <span>{{ n.descriptor }}</span>
                                    </label>
                                    <div class="text-danger texto-pequeno" v-if="errores['valoraciones.' + escala.id]">{{ errores['valoraciones.' + escala.id][0] }}</div>
                                </div>
                                <p v-if="criterio.combinacion === 'maximo'" class="texto-pequeno text-muted">El nivel del criterio es el mayor de sus subescalas.</p>
                            </template>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary" @click="guardarEvaluacion()">Calcular y guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <guardar-incidente :tipos="tipos" :usuarios="usuarios" :tacticas="tacticas" :activos="activos"></guardar-incidente>
    </div>
</template>
<script>
    export default {
        name: "DetalleIncidente",
        props: ['datos_incidente', 'datos_estructura', 'datos_configuracion', 'datos_tipos', 'datos_usuarios', 'datos_tacticas', 'datos_activos'],
        data(){
            const inc = JSON.parse(this.datos_incidente);
            return {
                incidente: inc,
                estructura: JSON.parse(this.datos_estructura),
                configuracion: JSON.parse(this.datos_configuracion),
                tipos: JSON.parse(this.datos_tipos),
                usuarios: JSON.parse(this.datos_usuarios),
                tacticas: JSON.parse(this.datos_tacticas),
                activos: JSON.parse(this.datos_activos),
                comentarios: inc.comentarios || [],
                evidencias: inc.evidencias || [],
                estados: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
                seguimiento: { estado: inc.estado, asignado_a: inc.asignado_a, notas: '' },
                nuevoComentario: '',
                descripcionEvidencia: '',
                subiendo: false,
                valores: {},
                errores: {},
            };
        },
        computed: {
            ultima(){ return this.incidente.evaluaciones && this.incidente.evaluaciones.length ? this.incidente.evaluaciones[0] : null; },
            maximoAporte(){
                if (!this.ultima) return 1;
                return Math.max(1, ...Object.values(this.ultima.detalle.criterios).map(c => c.aporte || 0));
            },
            escalasAnalista(){
                return this.estructura.filter(c => c.fuente === 'analista').flatMap(c => c.escalas);
            },
            completas(){ return this.escalasAnalista.filter(e => this.valores[e.id]).length; },
            nivelProgresion(){
                const niveles = (this.incidente.tacticas || []).map(t => t.nivel_progresion);
                return niveles.length ? Math.max(...niveles) : null;
            },
            tacticaMasAvanzada(){
                return (this.incidente.tacticas || []).filter(t => t.nivel_progresion === this.nivelProgresion).map(t => t.nombre).join(', ');
            },
            /** Misma fórmula del motor, solo como vista previa; el servidor recalcula. */
            vistaPrevia(){
                if (!this.configuracion || this.completas < this.escalasAnalista.length) return null;
                const adversario = this.incidente.origen === 'adversario';
                if (adversario && !this.nivelProgresion) return null;
                const niveles = {};
                for (const c of this.estructura) {
                    if (c.fuente === 'inventario') niveles[c.codigo] = this.incidente.activo ? this.incidente.activo.criticidad : 1;
                    else if (c.fuente === 'tacticas') { if (adversario) niveles[c.codigo] = this.nivelProgresion; }
                    else niveles[c.codigo] = Math.max(...c.escalas.map(e => this.valores[e.id]));
                }
                const excluidos = this.estructura.filter(c => c.solo_adversario && !adversario).map(c => c.codigo);
                const pesos = Object.entries(this.configuracion.pesos).filter(([k]) => excluidos.indexOf(k) === -1);
                const suma = pesos.reduce((s, [, p]) => s + p, 0);
                const indice = Math.round(pesos.reduce((s, [k, p]) => s + (p / suma) * ((niveles[k] - 1) / 4) * 100, 0) * 100) / 100;
                const u = this.configuracion.umbrales;
                const nivel = indice >= u.critico ? 'critico' : indice >= u.alto ? 'alto' : indice >= u.medio ? 'medio' : 'bajo';
                return { indice, nivel };
            },
        },
        mounted(){
            // El modal debe vivir en <body> para no quedar debajo de la barra superior.
            document.body.appendChild(this.$refs.modalEvaluacion);
            this.$on('incidente_actualizado', () => {
                this.notificarExito('Incidente actualizado correctamente');
                setTimeout(() => location.reload(), 1200);
            });
            ['tablaDetalle', 'tablaEvidencias', 'tablaEvaluaciones'].forEach(r => this.$refs[r] && window.etiquetarCeldas(this.$refs[r]));
        },
        updated(){
            ['tablaDetalle', 'tablaEvidencias', 'tablaEvaluaciones'].forEach(r => this.$refs[r] && window.etiquetarCeldas(this.$refs[r]));
        },
        beforeDestroy(){
            if (this.$refs.modalEvaluacion && this.$refs.modalEvaluacion.parentNode === document.body) document.body.removeChild(this.$refs.modalEvaluacion);
        },
        methods: {
            abrirEvaluacion(){
                const valores = {};
                const previas = (this.incidente.ultima_evaluacion && this.incidente.ultima_evaluacion.valoraciones) || [];
                previas.forEach(v => { valores[v.escala_id] = v.nivel; });
                this.valores = valores;
                this.errores = {};
                $('#modalEvaluacion').modal('show');
            },
            async guardarEvaluacion(){
                if (this.completas < this.escalasAnalista.length) {
                    this.notificarError('Valore todas las escalas antes de calcular (' + this.completas + ' de ' + this.escalasAnalista.length + ').');
                    return;
                }
                const detalle = this.vistaPrevia ? 'Índice ' + this.formatoIndice(this.vistaPrevia.indice) + ' · prioridad ' + this.etiquetaPrioridad(this.vistaPrevia.nivel) : '';
                if (!(await this.confirmar('¿Desea guardar la evaluación?', detalle))) return;
                const valoraciones = {};
                this.escalasAnalista.forEach(e => { valoraciones[e.id] = this.valores[e.id]; });
                this.$http.post('/panel/incidentes/' + this.incidente.id + '/evaluaciones', { valoraciones }).then(res => {
                    $('#modalEvaluacion').modal('hide');
                    this.notificarExito('Prioridad calculada: ' + this.etiquetaPrioridad(res.data.evaluacion.nivel_prioridad) + ' (' + this.formatoIndice(res.data.evaluacion.indice_riesgo) + ')');
                    setTimeout(() => location.reload(), 1500);
                }).catch(err => {
                    if (err.response && err.response.status === 422) this.errores = err.response.data.errors || {};
                    this.notificarError(this.mensajeDeError(err, 'guardar la evaluación'));
                });
            },
            async guardarSeguimiento(){
                if (!(await this.confirmar('¿Desea guardar el seguimiento?', 'Estado: ' + this.etiquetaEstado(this.seguimiento.estado)))) return;
                this.$http.put('/panel/incidentes/' + this.incidente.id + '/seguimiento', this.seguimiento).then(() => {
                    this.notificarExito('Seguimiento guardado correctamente');
                    setTimeout(() => location.reload(), 1500);
                }).catch(e => this.notificarError(this.mensajeDeError(e, 'guardar el seguimiento')));
            },
            async adjuntarEvidencia(){
                const archivo = this.$refs.archivo.files[0];
                if (!archivo) { this.notificarError('Seleccione un archivo.'); return; }
                if (!(await this.confirmar('¿Desea adjuntar la evidencia?', archivo.name))) return;
                const datos = new FormData();
                datos.append('archivo', archivo);
                datos.append('descripcion', this.descripcionEvidencia);
                this.subiendo = true;
                this.$http.post('/panel/incidentes/' + this.incidente.id + '/evidencias', datos).then(res => {
                    this.evidencias.unshift(res.data);
                    this.descripcionEvidencia = '';
                    this.$refs.archivo.value = '';
                    this.notificarExito('Evidencia adjuntada correctamente');
                }).catch(e => this.notificarError(this.mensajeDeError(e, 'adjuntar la evidencia')))
                  .finally(() => { this.subiendo = false; });
            },
            tamano(bytes){
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            },
            async agregarComentario(){
                if (!this.nuevoComentario) return;
                if (!(await this.confirmar('¿Desea guardar el comentario?'))) return;
                this.$http.post('/panel/incidentes/' + this.incidente.id + '/comentarios', { comentario: this.nuevoComentario }).then(res => {
                    this.comentarios.unshift(res.data);
                    this.nuevoComentario = '';
                    this.notificarExito('Comentario guardado correctamente');
                }).catch(e => this.notificarError(this.mensajeDeError(e, 'guardar el comentario')));
            },
            eliminarComentario(id){
                this.$http.delete('/panel/incidentes/' + this.incidente.id + '/comentarios/' + id).then(() => {
                    this.comentarios = this.comentarios.filter(c => c.id !== id);
                });
            }
        }
    }
</script>
<style scoped></style>
