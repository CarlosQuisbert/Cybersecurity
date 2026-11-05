<template>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4>Ficha del incidente</h4></div>
                <div class="card-body">
                    <p><strong>Descripcion:</strong><br>{{ item.description }}</p>
                    <p><strong>Tipo:</strong> {{ item.type ? item.type.name : '-' }} | <strong>Severidad:</strong> {{ item.severity }} | <strong>Estado:</strong> {{ item.status }}</p>
                    <p><strong>Activos:</strong> {{ item.affected_assets || '-' }}</p>
                    <p><strong>Impacto:</strong><br>{{ item.impact || '-' }}</p>
                    <p><strong>Remediacion:</strong><br>{{ item.remediation || '-' }}</p>
                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Cambiar estado</label>
                            <select v-model="newStatus" class="form-control">
                                <option v-for="s in statuses" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nota de cambio</label>
                            <input v-model="statusNote" class="form-control" placeholder="Motivo...">
                        </div>
                    </div>
                    <button @click="saveStatus()" class="btn btn-warning">Actualizar estado</button>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h4>Historial de estados</h4></div>
                <div class="card-body">
                    <ul class="list-group">
                        <li v-for="h in item.histories" :key="h.id" class="list-group-item">
                            <strong>{{ h.from_status || 'nuevo' }} - {{ h.to_status }}</strong>
                            <span class="text-muted"> - {{ h.created_at }}</span>
                            <br><small>{{ h.notes || '' }}</small>
                        </li>
                    </ul>
                    <p v-if="!item.histories || !item.histories.length" class="text-muted">Sin historial.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4>Seguimiento</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea v-model="newComment" rows="3" class="form-control" placeholder="Agregar nota..."></textarea>
                    </div>
                    <button @click="addComment()" class="btn btn-primary btn-block">Agregar comentario</button>
                    <hr>
                    <div v-for="c in comments" :key="c.id" class="alert alert-light">
                        <strong>{{ c.author ? c.author.name : 'Sistema' }}</strong>
                        <small class="text-muted"> - {{ c.created_at }}</small>
                        <p class="mb-1">{{ c.comment }}</p>
                        <a @click="delComment(c.id)" class="text-danger" style="cursor:pointer"><small>eliminar</small></a>
                    </div>
                    <p v-if="!comments.length" class="text-muted">Sin comentarios.</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: "IncidentDetail",
        props: ['raw_incident', 'raw_types', 'raw_admins'],
        data(){
            const inc = JSON.parse(this.raw_incident);
            return {
                item: inc,
                comments: inc.comments || [],
                statuses: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
                newStatus: inc.status,
                statusNote: '',
                newComment: '',
            };
        },
        methods: {
            saveStatus(){
                this.$http.put('/admin/incidents/' + this.item.id, {
                    title: this.item.title,
                    description: this.item.description,
                    incident_type_id: this.item.incident_type_id,
                    severity: this.item.severity,
                    status: this.newStatus,
                    reported_by: this.item.reported_by,
                    assigned_to: this.item.assigned_to,
                    affected_assets: this.item.affected_assets,
                    impact: this.item.impact,
                    remediation: this.item.remediation,
                    status_notes: this.statusNote,
                }).then(res => {
                    this.item.status = res.data.status;
                    this.statusNote = '';
                    this.notifSuceess('Estado actualizado');
                    location.reload();
                }).catch(e => this.notifError(e.message || 'Error'));
            },
            addComment(){
                if (!this.newComment) return;
                this.$http.post('/admin/incidents/' + this.item.id + '/comments', { comment: this.newComment }).then(res => {
                    this.comments.unshift(res.data);
                    this.newComment = '';
                    this.notifSuceess('Comentario agregado');
                }).catch(e => this.notifError(e.message || 'Error'));
            },
            delComment(id){
                this.$http.delete('/admin/incidents/' + this.item.id + '/comments/' + id).then(() => {
                    this.comments = this.comments.filter(c => c.id !== id);
                });
            }
        }
    }
</script>
<style scoped></style>