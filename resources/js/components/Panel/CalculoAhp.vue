<template>
    <div>
        <div class="card">
            <div class="card-body">
                <p class="mb-2">Cada experto compara los elementos de dos en dos con la escala fundamental de Saaty. Escriba un valor mayor que 1 si el
                    <strong>primer</strong> elemento es más importante (3, 5, 7, 9 o los intermedios) y su recíproco si lo es el <strong>segundo</strong> (1/3, 1/5…).
                    Con la jerarquía de la tesis, cada experto emite {{ totalJuicios }} juicios.</p>
                <p class="texto-pequeno text-muted mb-0">
                    1 igual importancia · 3 moderada · 5 fuerte · 7 muy fuerte · 9 extrema · 2, 4, 6, 8 intermedios.
                    Las matrices con razón de consistencia mayor que 0,10 se devuelven al experto, indicando las comparaciones que más inconsistencia aportan.
                    Los juicios se agregan con la media geométrica de cada celda.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Juicios de los expertos</h4>
                <div>
                    <button class="btn btn-sm btn-light" @click="agregarExperto()"><i class="fas fa-user-plus mr-1"></i>Agregar experto</button>
                </div>
            </div>
            <div class="card-body" style="overflow-x:auto">
                <table class="table table-sm table-bordered" style="min-width:600px">
                    <thead>
                    <tr>
                        <th>Comparación</th>
                        <th v-for="(e, k) in expertos" :key="k" style="min-width:110px">
                            <input v-model="e.nombre" class="form-control form-control-sm">
                            <a v-if="expertos.length > 1" class="text-danger texto-pequeno" style="cursor:pointer" @click="expertos.splice(k, 1)">quitar</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="m in estructura">
                        <tr :key="m.clave + '-titulo'" class="table-active"><td :colspan="expertos.length + 1"><strong>{{ m.titulo }}</strong></td></tr>
                        <tr v-for="p in m.pares" :key="p.clave">
                            <td>{{ p.texto }}</td>
                            <td v-for="(e, k) in expertos" :key="k">
                                <input v-model="e.juicios[p.clave]" class="form-control form-control-sm" placeholder="ej. 3 o 1/3">
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
                <button class="btn btn-primary" @click="calcular()" :disabled="calculando"><i class="fas fa-calculator mr-1"></i>Calcular pesos</button>
            </div>
        </div>

        <template v-if="resultado">
            <div class="alert" :class="resultado.consistente ? 'alert-success' : 'alert-danger'">
                <template v-if="resultado.consistente">Todas las matrices individuales y agregadas son consistentes (CR ≤ 0,10).</template>
                <template v-else>Hay matrices con CR mayor que 0,10: devuélvalas al experto para que revise sus juicios antes de aplicar los pesos.</template>
            </div>

            <div class="row">
                <div class="col-lg-6" v-for="m in resultado.matrices" :key="m.clave">
                    <div class="card">
                        <div class="card-header"><h4>{{ m.titulo }}</h4></div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead><tr><th>Elemento</th><th>Peso agregado</th></tr></thead>
                                <tbody><tr v-for="(el, i) in m.elementos" :key="el.id"><td>{{ el.nombre }}</td><td class="indice-riesgo">{{ n(m.pesos[i]) }}</td></tr></tbody>
                            </table>
                            <p class="texto-pequeno mb-2" v-if="m.elementos.length > 2">
                                Matriz agregada: λmáx = {{ n(m.lambda_max) }}, CI = {{ n(m.ci) }}, RI = {{ n(m.ri, 2) }},
                                <strong :class="m.consistente ? 'text-success' : 'text-danger'">CR = {{ n(m.cr) }}</strong>
                            </p>
                            <p class="texto-pequeno text-muted mb-2" v-else>Matriz de 2 × 2: no requiere verificación de consistencia.</p>
                            <div v-if="m.elementos.length > 2">
                                <div v-for="ind in m.individuales" :key="ind.experto" class="texto-pequeno">
                                    {{ ind.experto }}: CR = <span :class="ind.consistente ? 'text-success' : 'text-danger'">{{ n(ind.cr) }}</span>
                                    <span v-if="ind.revisar.length" class="text-danger"> · revisar: {{ ind.revisar.join('; ') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>Pesos globales resultantes</h4></div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Bloque</th><th>Criterio</th><th>Peso del bloque</th><th>Peso local</th><th>Peso global</th></tr></thead>
                        <tbody>
                        <tr v-for="f in globales" :key="f.criterio">
                            <td>{{ f.bloque }}</td><td>{{ f.criterio }}</td><td>{{ n(f.bloquePeso) }}</td><td>{{ n(f.local) }}</td><td class="indice-riesgo">{{ n(f.global) }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-success" :disabled="!resultado.consistente || !vigente" @click="publicar()">
                        <i class="fas fa-check mr-1"></i>Publicar como nueva versión de la configuración
                    </button>
                    <p class="texto-pequeno text-muted mt-2 mb-0" v-if="vigente">Se conservan los umbrales, la capacidad y la versión de ATT&amp;CK de la versión {{ vigente.version }}; los incidentes evaluados se recalculan.</p>
                </div>
            </div>
        </template>
    </div>
</template>
<script>
    export default {
        name: "CalculoAhp",
        props: ['datos_estructura', 'datos_vigente'],
        data(){
            return {
                estructura: JSON.parse(this.datos_estructura),
                vigente: JSON.parse(this.datos_vigente),
                expertos: [{ nombre: 'EXP-01', juicios: {} }],
                resultado: null,
                calculando: false,
            };
        },
        computed: {
            totalJuicios(){ return this.estructura.reduce((s, m) => s + m.pares.length, 0); },
            globales(){
                if (!this.resultado) return [];
                const bloques = this.resultado.matrices.find(m => m.clave === 'bloques');
                const filas = [];
                bloques.elementos.forEach((b, i) => {
                    const m = this.resultado.matrices.find(x => x.clave === 'bloque_' + b.id);
                    const criterios = m ? m.elementos.map((c, j) => ({ nombre: c.nombre, peso: m.pesos[j] })) : [];
                    criterios.forEach(c => filas.push({ bloque: b.nombre, criterio: c.nombre, bloquePeso: bloques.pesos[i], local: c.peso, global: bloques.pesos[i] * c.peso }));
                });
                return filas;
            },
        },
        methods: {
            n(v, d = 3){ return v === null || v === undefined ? '-' : Number(v).toFixed(d).replace('.', ','); },
            agregarExperto(){
                this.expertos.push({ nombre: 'EXP-' + String(this.expertos.length + 1).padStart(2, '0'), juicios: {} });
            },
            calcular(){
                this.calculando = true;
                this.$http.post('/panel/modelo/ahp/calcular', { expertos: this.expertos }).then(res => {
                    this.resultado = res.data;
                    this.notificarExito('Pesos calculados');
                }).catch(err => {
                    const errores = err.response && err.response.data && err.response.data.errors;
                    this.notificarError(errores ? Object.values(errores)[0][0] : this.mensajeDeError(err, 'calcular los pesos'));
                }).finally(() => { this.calculando = false; });
            },
            async publicar(){
                if (!(await this.confirmar('¿Desea publicar estos pesos como una nueva versión?', 'Los incidentes evaluados se recalcularán.'))) return;
                const crDe = clave => { const m = this.resultado.matrices.find(x => x.clave === clave); return m ? Number(m.cr.toFixed(4)) : null; };
                this.$http.post('/panel/modelo/configuraciones', {
                    descripcion: 'Pesos calculados por AHP con ' + this.resultado.expertos + ' experto(s), agregación por media geométrica.',
                    version_attack: this.vigente.version_attack,
                    umbral_critico: this.vigente.umbral_critico,
                    umbral_alto: this.vigente.umbral_alto,
                    umbral_medio: this.vigente.umbral_medio,
                    capacidad_criticos: this.vigente.capacidad_criticos,
                    pesos_bloque: this.resultado.pesos_bloque,
                    pesos_criterio: this.resultado.pesos_criterio,
                    origen_pesos: {
                        metodo: 'AHP, agregación de juicios individuales por media geométrica',
                        expertos: this.resultado.expertos,
                        cr: Object.fromEntries(this.resultado.matrices.map(m => [m.clave, Number(m.cr.toFixed(4))])),
                    },
                    recalcular: true,
                }, { headers: { Accept: 'application/json' } }).then(res => {
                    this.notificarExito(res.data.message);
                    setTimeout(() => { location.href = '/panel/modelo'; }, 1500);
                }).catch(err => {
                    const errores = err.response && err.response.data && err.response.data.errors;
                    this.notificarError(errores ? Object.values(errores)[0][0] : this.mensajeDeError(err, 'publicar la configuración'));
                });
            }
        }
    }
</script>
