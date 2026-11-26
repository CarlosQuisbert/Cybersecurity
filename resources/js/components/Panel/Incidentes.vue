<template>
    <div>
        <div class="d-flex justify-content-end mb-3" v-if="puede('incidentes.crear')">
            <button @click="crearIncidente()" class="btn btn-primary btn-agregar">
                <i class="fas fa-shield-alt mr-2"></i>Reportar incidente
            </button>
        </div>
        <div class="box-body">
            <table ref="tabla" :key="claveTabla" class="table table-bordered table-striped w-100">
                <thead>
                <tr>
                    <th>Incidente</th>
                    <th>Categoría y origen</th>
                    <th>Índice</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Asignado</th>
                    <th>Ver</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="inc in listado" :key="inc.id">
                    <td>
                        <strong>{{ inc.codigo }}</strong>
                        <div class="texto-pequeno">{{ inc.titulo }}</div>
                        <div v-if="inc.calificacion !== 'incidente'"><span class="badge badge-light texto-pequeno">{{ etiquetaCalificacion(inc.calificacion) }}</span></div>
                    </td>
                    <td>
                        <span class="texto-pequeno">{{ inc.tipo ? inc.tipo.nombre : '-' }}</span>
                        <div class="texto-pequeno text-muted">{{ etiquetaOrigen(inc.origen) }}</div>
                    </td>
                    <td class="indice-riesgo">{{ formatoIndice(inc.indice_riesgo) }}</td>
                    <td><span class="badge" :class="clasePrioridad(inc.nivel_prioridad)">{{ etiquetaPrioridad(inc.nivel_prioridad) }}</span></td>
                    <td><span class="badge badge-info">{{ etiquetaEstado(inc.estado) }}</span></td>
                    <td>{{ inc.usuario_asignado ? inc.usuario_asignado.nombre : 'Sin asignar' }}</td>
                    <td class="text-center"><a :href="'/panel/incidentes/' + inc.id" title="Ver, evaluar y dar seguimiento"><i class="fas fa-eye"></i></a></td>
                    <td class="text-center"><a v-if="puede('incidentes.editar')" @click="editarIncidente(inc)"><i class="fas fa-edit"></i></a></td>
                    <td class="text-center"><a v-if="puede('incidentes.eliminar')" @click="eliminarIncidente(inc.id)"><span class="fas fa-trash"></span></a></td>
                </tr>
                </tbody>
            </table>
        </div>
        <guardar-incidente :tipos="tipos" :usuarios="usuarios" :tacticas="tacticas" :activos="activos"></guardar-incidente>
    </div>
</template>
<script>
    import Swal from 'sweetalert'
    import TablaDatos from '../../mixins/tabla_datos'
    export default {
        name: "Incidentes",
        mixins: [TablaDatos],
        props: ['datos_incidentes', 'datos_tipos', 'datos_usuarios', 'datos_tacticas', 'datos_activos'],
        data(){
            return {
                listado: JSON.parse(this.datos_incidentes),
                tipos: JSON.parse(this.datos_tipos),
                usuarios: JSON.parse(this.datos_usuarios),
                tacticas: JSON.parse(this.datos_tacticas),
                activos: JSON.parse(this.datos_activos),
            }
        },
        computed: {
            registrosTabla(){ return this.listado; }
        },
        mounted(){
            this.$on('incidente_agregado', (inc) => {
                this.listado.unshift(inc);
                this.notificarExito('Incidente registrado correctamente. Ábralo para evaluarlo.');
            });
            this.$on('incidente_actualizado', (inc) => {
                const i = this.listado.findIndex(x => x.id === inc.id);
                if (i > -1) this.listado.splice(i, 1, inc);
                this.notificarExito('Incidente actualizado correctamente');
            });
        },
        methods: {
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
