<template>
    <div class="modal fade" id="incidentModal" data-backdrop="false" style="z-index:1000">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editing ? 'Editar incidente' : 'Reportar incidente' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Titulo *</label>
                            <input v-model="form.title" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo *</label>
                            <select v-model="form.incident_type_id" class="form-control">
                                <option value="">Seleccione...</option>
                                <option v-for="t in types" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripcion *</label>
                        <textarea v-model="form.description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Severidad *</label>
                            <select v-model="form.severity" class="form-control">
                                <option v-for="s in severities" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Estado *</label>
                            <select v-model="form.status" class="form-control">
                                <option v-for="s in statuses" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Asignar a</label>
                            <select v-model="form.assigned_to" class="form-control">
                                <option :value="null">Sin asignar</option>
                                <option v-for="a in admins" :value="a.id">{{ a.name }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Detectado</label>
                            <input type="datetime-local" v-model="form.detected_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Reportado por</label>
                            <input v-model="form.reported_by" class="form-control">
                        </div>
                        <div class="form-group col-md-8">
                            <label>Activos afectados</label>
                            <input v-model="form.affected_assets" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Impacto</label>
                        <textarea v-model="form.impact" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Remediacion</label>
                        <textarea v-model="form.remediation" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding-top:0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button v-if="editing" @click="saveEdit()" class="btn btn-primary">Actualizar</button>
                    <button v-else @click="saveNew()" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import ErrorBag from '../error_bag'
    function blank(){ return { title:'', description:'', incident_type_id:'', severity:'media', status:'abierto', reported_by:'', assigned_to:null, detected_at:'', affected_assets:'', impact:'', remediation:'' }; }
    export default {
        name: "StoreIncident",
        props: ['types', 'admins', 'severities', 'statuses'],
        data(){ return { editing:false, editId:null, form: blank(), errors: new ErrorBag }; },
        mounted(){
            this.$parent.$on('create_incident', () => {
                this.editing = false; this.editId = null; this.form = blank();
                this.errors.clearAll(); $('#incidentModal').modal('show');
            });
            this.$parent.$on('edit_incident', (inc) => {
                this.editing = true; this.editId = inc.id; this.errors.clearAll();
                this.form = { title:inc.title, description:inc.description, incident_type_id:inc.incident_type_id, severity:inc.severity, status:inc.status, reported_by:inc.reported_by||'', assigned_to:inc.assigned_to, detected_at:inc.detected_at?inc.detected_at.substring(0,16):'', affected_assets:inc.affected_assets||'', impact:inc.impact||'', remediation:inc.remediation||'' };
                $('#incidentModal').modal('show');
            });
        },
        methods: {
            saveNew(){
                this.$http.post('/admin/incidents', this.form).then(res => {
                    this.$parent.$emit('add_incident', res.data);
                    $('#incidentModal').modal('hide');
                }).catch(err => this.onError(err));
            },
            saveEdit(){
                this.$http.put('/admin/incidents/' + this.editId, this.form).then(res => {
                    this.$parent.$emit('update_incident', res.data);
                    $('#incidentModal').modal('hide');
                }).catch(err => this.onError(err));
            },
            onError(err){
                if (err.response && err.response.status == 422) this.errors.setErrors(err.response.data.errors);
                this.notifError(err.message || 'Ocurrio un error');
            }
        }
    }
</script>
<style scoped></style>
