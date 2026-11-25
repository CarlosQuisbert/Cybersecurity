/**
 * Contenedor de errores de validación devueltos por Laravel (respuesta 422).
 */
class BolsaErrores {
    constructor(errores = {}) {
        this.establecerErrores(errores);
    }

    tieneErrores() {
        return !!this.claves.length;
    }

    get claves() {
        return Object.keys(this.errores);
    }

    tieneError(clave) {
        return this.claves.indexOf(clave) > -1;
    }

    primeraClave() {
        return this.claves[0];
    }

    primero(clave) {
        return this.errores[clave] ? this.errores[clave][0] : undefined;
    }

    establecerErrores(errores) {
        this.errores = errores || {};
    }

    limpiarTodo() {
        this.establecerErrores({});
    }

    limpiar(clave) {
        return delete this.errores[clave];
    }
}

export default BolsaErrores;
