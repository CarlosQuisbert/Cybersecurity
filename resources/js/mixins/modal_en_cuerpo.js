/**
 * Mueve a <body> el modal con ref="modal" de un componente que no es él mismo un
 * modal (dentro de <section> la barra superior lo tapa).
 */
export default {
    mounted() {
        if (this.$refs.modal) document.body.appendChild(this.$refs.modal);
    },
    beforeDestroy() {
        if (this.$refs.modal && this.$refs.modal.parentNode === document.body) document.body.removeChild(this.$refs.modal);
    },
};
