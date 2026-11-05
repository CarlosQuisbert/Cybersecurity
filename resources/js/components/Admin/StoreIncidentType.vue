<template>
    <div class="modal fade" id="incidentTypeModal" data-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editing ? 'Editar tipo' : 'Nuevo tipo' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input v-model="form.name" class="form-control" :class="{'is-invalid': errors.hasError('name')}">
                        <div class="invalid-feedback" v-if="errors.hasError('name')">{{ errors.first('name') }}</div>
                    </div>
                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea v-model="form.description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" v-model="form.active" class="form-check-input" id="activeCheck">
                        <label class="form-check-label" for="activeCheck">Activo</label>
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
    export default {
        name: "StoreIncidentType",
        data(){ return { editing:false, editId:null, form:{name:'',description:'',active:true}, errors:new ErrorBag }; },
        mounted(){
            this.$parent.$on('create_type', () => {
                this.editing=false; this.editId=null;
                this.form={name:'',description:'',active:true};
                this.errors.clearAll(); $('#incidentTypeModal').modal('show');
            });
            this.$parent.$on('edit_type', (t) => {
                this.editing=true; this.editId=t.id;
                this.form={name:t.name,description:t.description||'',active:!!t.active};
                this.errors.clearAll(); $('#incidentTypeModal').modal('show');
            });
        },
        methods: {
            saveNew(){
                this.$http.post('/admin/incident-types', this.form).then(res => {
                    this.$parent.$emit('add_type', res.data);
                    $('#incidentTypeModal').modal('hide');
                }).catch(e => this.onError(e));
            },
            saveEdit(){
                this.$http.put('/admin/incident-types/' + this.editId, this.form).then(res => {
                    this.$parent.$emit('update_type', res.data);
                    $('#incidentTypeModal').modal('hide');
                }).catch(e => this.onError(e));
            },
            onError(err){
                if (err.response && err.response.status == 422) this.errors.setErrors(err.response.data.errors);
                this.notifError(err.message || 'Error');
            }
        }
    }
</script>
<style scoped></style>
