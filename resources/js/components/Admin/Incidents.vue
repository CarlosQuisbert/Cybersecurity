<template>
    <div>
        <div class="float-right mb-4">
            <button style="padding: 10px" @click="createIncident()" class="pull-right btn btn-primary">
                <i class="fas fa-shield-alt" style="padding-right: 10px"></i>Reportar incidente
            </button>
        </div>
        <div class="form-row mb-3">
            <div class="col-md-3">
                <input v-model="filters.q" @input="fetchList" class="form-control" placeholder="Buscar código, título...">
            </div>
            <div class="col-md-3">
                <select v-model="filters.status" @change="fetchList" class="form-control">
                    <option value="">Todos los estados</option>
                    <option v-for="s in statuses" :value="s">{{ s }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select v-model="filters.severity" @change="fetchList" class="form-control">
                    <option value="">Todas las severidades</option>
                    <option v-for="s in severities" :value="s">{{ s }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select v-model="filters.incident_type_id" @change="fetchList" class="form-control">
                    <option value="">Todos los tipos</option>
                    <option v-for="t in types" :value="t.id">{{ t.name }}</option>
                </select>
            </div>
        </div>
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Severidad</th>
                    <th>Estado</th>
                    <th>Asignado</th>
                    <th>Ver</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="inc in list" :key="inc.id">
                    <td><strong>{{ inc.code }}</strong></td>
                    <td>{{ inc.title }}</td>
                    <td>{{ inc.type ? inc.type.name : '-' }}</td>
                    <td><span class="badge" :class="sevClass(inc.severity)">{{ inc.severity }}</span></td>
                    <td><span class="badge badge-info">{{ inc.status }}</span></td>
                    <td>{{ inc.assigned_admin ? inc.assigned_admin.name : 'Sin asignar' }}</td>
                    <td><a :href="'/admin/incidents/' + inc.id"><i class="text-center fas fa-eye"></i></a></td>
                    <td><a @click="editIncident(inc)"><i class="text-center fas fa-edit"></i></a></td>
                    <td><a @click="deleteIncident(inc.id)"><span class="text-center fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <store-incident :types="types" :admins="admins" :severities="severities" :statuses="statuses"></store-incident>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    export default {
        name: "Incidents",
        props: ['raw_incidents', 'raw_types', 'raw_admins'],
        data(){
            return{
                list: JSON.parse(this.raw_incidents),
                types: JSON.parse(this.raw_types),
                admins: JSON.parse(this.raw_admins),
                severities: ['baja','media','alta','critica'],
                statuses: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
                filters: { q:'', status:'', severity:'', incident_type_id:'' },
            }
        },
        mounted(){
            if (this.list.data) this.list = this.list.data;
            this.$on('add_incident', (inc) => {
                this.list.unshift(inc);
                this.notifSuceess('Incidente registrado correctamente');
            });
            this.$on('update_incident', (inc) => {
                const i = this.list.findIndex(x => x.id === inc.id);
                if (i > -1) this.list.splice(i, 1, inc);
                this.notifSuceess('Incidente actualizado');
            });
        },
        methods: {
            sevClass(s){
                return { baja:'badge-success', media:'badge-warning', alta:'badge-danger', critica:'badge-dark' }[s] || 'badge-secondary';
            },
            createIncident(){ this.$emit('create_incident'); },
            editIncident(inc){ this.$emit('edit_incident', inc); },
            fetchList(){
                const p = { ...this.filters };
                this.$http.get('/admin/incidents', { params: { ...p, ajax: 1 }, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => {
                    this.list = res.data.data || res.data;
                }).catch(() => {});
            },
            deleteIncident(id){
                Swal('¿Eliminar este incidente?', { buttons: ['Cancelar', true] }).then(v => {
                    if (v) this.$http.delete(`/admin/incidents/${id}`).then(() => {
                        this.list = this.list.filter(x => x.id !== id);
                        this.notifSuceess('Incidente eliminado');
                    }).catch(e => this.notifError(e.message || 'Error'));
                });
            }
        }
    }
</script>
<style scoped></style>
