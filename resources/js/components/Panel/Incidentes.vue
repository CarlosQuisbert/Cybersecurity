<template>
    <div>
        <div class="d-flex justify-content-end mb-3">
            <button @click="crearIncidente()" class="btn btn-primary btn-agregar">
                <i class="fas fa-shield-alt mr-2"></i>Reportar incidente
            </button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
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
                <tr v-for="inc in listado" :key="inc.id">
                    <td><strong>{{ inc.codigo }}</strong></td>
                    <td>{{ inc.titulo }}</td>
                    <td>{{ inc.tipo ? inc.tipo.nombre : '-' }}</td>
                    <td><span class="badge" :class="claseSeveridad(inc.severidad)">{{ inc.severidad }}</span></td>
                    <td><span class="badge badge-info">{{ inc.estado }}</span></td>
                    <td>{{ inc.usuario_asignado ? inc.usuario_asignado.nombre : 'Sin asignar' }}</td>
                    <td class="text-center"><a :href="'/panel/incidentes/' + inc.id"><i class="fas fa-eye"></i></a></td>
                    <td class="text-center"><a @click="editarIncidente(inc)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a @click="eliminarIncidente(inc.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <guardar-incidente :tipos="tipos" :usuarios="usuarios" :severidades="severidades" :estados="estados"></guardar-incidente>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    export default {
        name: "Incidentes",
        mixins: [TablaDatos],
        props: ['datos_incidentes', 'datos_tipos', 'datos_usuarios'],
        data(){
            return {
                listado: JSON.parse(this.datos_incidentes),
                tipos: JSON.parse(this.datos_tipos),
                usuarios: JSON.parse(this.datos_usuarios),
                severidades: ['baja','media','alta','critica'],
                estados: ['abierto','en_proceso','contenido','erradicado','recuperado','cerrado'],
            }
        },
        computed: {
            registrosTabla(){ return this.listado; }
        },
        mounted(){
            if (this.listado.data) this.listado = this.listado.data;
            this.$on('incidente_agregado', (inc) => {
                this.listado.unshift(inc);
                this.notificarExito('Incidente registrado correctamente');
            });
            this.$on('incidente_actualizado', (inc) => {
                const i = this.listado.findIndex(x => x.id === inc.id);
                if (i > -1) this.listado.splice(i, 1, inc);
                this.notificarExito('Incidente actualizado correctamente');
            });
        },
        methods: {
            claseSeveridad(s){
                return { baja:'badge-success', media:'badge-warning', alta:'badge-danger', critica:'badge-dark' }[s] || 'badge-secondary';
            },
            crearIncidente(){ this.$emit('crear_incidente'); },
            editarIncidente(inc){ this.$emit('editar_incidente', inc); },
            eliminarIncidente(id){
                Swal('¿Eliminar este incidente?', { buttons: ['Cancelar', true] }).then(v => {
                    if (v) this.$http.delete(`/panel/incidentes/${id}`).then(() => {
                        this.listado = this.listado.filter(x => x.id !== id);
                        this.notificarExito('Incidente eliminado');
                    }).catch(e => this.notificarError(this.mensajeDeError(e, 'eliminar el incidente')));
                });
            }
        }
    }
</script>
<style scoped></style>
