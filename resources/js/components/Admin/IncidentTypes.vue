<template>
    <div>
        <div class="float-right mb-4">
            <button style="padding: 10px" @click="createType()" class="pull-right btn btn-primary">
                <i class="fas fa-plus" style="padding-right: 10px"></i>Nuevo tipo
            </button>
        </div>
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead><tr><th>No</th><th>Nombre</th><th>Descripcion</th><th>Incidentes</th><th>Activo</th><th>Editar</th><th>Eliminar</th></tr></thead>
                <tbody>
                <tr v-for="t, key in list" :key="t.id">
                    <td>{{ key + 1 }}</td>
                    <td><strong>{{ t.name }}</strong></td>
                    <td>{{ t.description || '-' }}</td>
                    <td><span class="badge badge-info">{{ t.incidents_count || 0 }}</span></td>
                    <td><span class="badge" :class="t.active ? 'badge-success' : 'badge-secondary'">{{ t.active ? 'Si' : 'No' }}</span></td>
                    <td><a @click="editType(t)"><i class="text-center fas fa-edit"></i></a></td>
                    <td><a @click="deleteType(t.id, key)"><span class="text-center fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <store-incident-type></store-incident-type>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    export default {
        name: "IncidentTypes",
        props: ['raw_types'],
        data(){ return { list: JSON.parse(this.raw_types) }; },
        mounted(){
            this.$on('add_type', (t) => { this.list.push(t); this.notifSuceess('Tipo creado'); });
            this.$on('update_type', (t) => {
                const i = this.list.findIndex(x => x.id === t.id);
                if (i > -1) this.list.splice(i, 1, t);
                this.notifSuceess('Tipo actualizado');
            });
        },
        methods: {
            createType(){ this.$emit('create_type'); },
            editType(t){ this.$emit('edit_type', t); },
            deleteType(id, key){
                Swal('Eliminar este tipo?', { buttons: ['Cancelar', true] }).then(v => {
                    if (!v) return;
                    this.$http.delete('/admin/incident-types/' + id).then(() => {
                        this.list.splice(key, 1);
                        this.notifSuceess('Tipo eliminado');
                    }).catch(err => {
                        const msg = (err.response && err.response.data && err.response.data.message) || err.message;
                        this.notifError(msg || 'Error');
                    });
                });
            }
        }
    }
</script>
<style scoped></style>
