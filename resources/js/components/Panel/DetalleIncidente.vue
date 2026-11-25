<template>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4>Ficha del incidente</h4></div>
                <div class="card-body">
                    <p><strong>Descripción:</strong><br>{{ incidente.descripcion }}</p>
                    <p><strong>Tipo:</strong> {{ incidente.tipo ? incidente.tipo.nombre : '-' }} | <strong>Severidad:</strong> {{ incidente.severidad }} | <strong>Estado:</strong> {{ incidente.estado }}</p>
                    <p><strong>Activos:</strong> {{ incidente.activos_afectados || '-' }}</p>
                    <p><strong>Impacto:</strong><br>{{ incidente.impacto || '-' }}</p>
                    <p><strong>Remediación:</strong><br>{{ incidente.remediacion || '-' }}</p>
                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Cambiar estado</label>
                            <select v-model="nuevoEstado" class="form-control">
                                <option v-for="e in estados" :value="e">{{ e }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nota de cambio</label>
                            <input v-model="notaEstado" class="form-control" placeholder="Motivo...">
                        </div>
                    </div>
                    <button @click="guardarEstado()" class="btn btn-warning">Actualizar estado</button>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h4>Historial de estados</h4></div>
                <div class="card-body">
                    <ul class="list-group">
                        <li v-for="h in incidente.historial" :key="h.id" class="list-group-item">
                            <strong>{{ h.estado_anterior || 'nuevo' }} - {{ h.estado_nuevo }}</strong>
                            <span class="text-muted"> - {{ h.creado_en }}</span>
                            <br><small>{{ h.notas || '' }}</small>
                        </li>
                    </ul>
                    <p v-if="!incidente.historial || !incidente.historial.length" class="text-muted">Sin historial.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4>Seguimiento</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea v-model="nuevoComentario" rows="3" class="form-control" placeholder="Agregar nota..."></textarea>
                    </div>
                    <button @click="agregarComentario()" class="btn btn-primary btn-block">Agregar comentario</button>
                    <hr>
                    <div v-for="c in comentarios" :key="c.id" class="alert alert-light">
                        <strong>{{ c.autor ? c.autor.nombre : 'Sistema' }}</strong>
                        <small class="text-muted"> - {{ c.creado_en }}</small>
                        <p class="mb-1">{{ c.comentario }}</p>
                        <a @click="eliminarComentario(c.id)" class="text-danger" style="cursor:pointer"><small>eliminar</small></a>
                    </div>
                    <p v-if="!comentarios.length" class="text-muted">Sin comentarios.</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: "DetalleIncidente",
        props: ['datos_incidente', 'datos_tipos', 'datos_usuarios'],
        data(){
            const inc = JSON.parse(this.datos_incidente);
            return {
                incidente: inc,
                comentarios: inc.comentarios || [],
                estados: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
                nuevoEstado: inc.estado,
                notaEstado: '',
                nuevoComentario: '',
            };
        },
        methods: {
            async guardarEstado(){
                if (!(await this.confirmar('¿Desea actualizar el estado del incidente?', 'Nuevo estado: ' + this.nuevoEstado))) return;
                this.$http.put('/panel/incidentes/' + this.incidente.id, {
                    titulo: this.incidente.titulo,
                    descripcion: this.incidente.descripcion,
                    tipo_incidente_id: this.incidente.tipo_incidente_id,
                    severidad: this.incidente.severidad,
                    estado: this.nuevoEstado,
                    reportado_por: this.incidente.reportado_por,
                    asignado_a: this.incidente.asignado_a,
                    activos_afectados: this.incidente.activos_afectados,
                    impacto: this.incidente.impacto,
                    remediacion: this.incidente.remediacion,
                    notas_estado: this.notaEstado,
                }).then(res => {
                    this.incidente.estado = res.data.estado;
                    this.notaEstado = '';
                    this.notificarExito('Estado actualizado correctamente');
                    // Se recarga para mostrar el historial actualizado, dejando ver el mensaje.
                    setTimeout(() => location.reload(), 1500);
                }).catch(e => this.notificarError(this.mensajeDeError(e, 'actualizar el estado')));
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
