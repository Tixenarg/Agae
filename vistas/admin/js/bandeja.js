const { createApp } = Vue;

createApp({
    data() {
        return {
            solicitudes: [],
            cargando: true
        }
    },
    mounted() {
        // Se ejecuta automáticamente al cargar la pantalla
        this.cargarPendientes();
    },
    methods: {
        /**
         * Obtiene la lista de solicitudes pendientes desde el controlador PHP
         */
        async cargarPendientes() {
            this.cargando = true;
            try {
                // Apuntamos al controlador estandarizado subiendo dos niveles
                const respuesta = await fetch('../../controladores/solicitud_controlador.php?accion=listar_pendientes');
                const resultado = await respuesta.json();

                if (resultado.exito) {
                    this.solicitudes = resultado.data;
                } else {
                    console.error("Error devuelto por el servidor:", resultado.error);
                }
            } catch (error) {
                console.error("Error de conexión/red al obtener datos:", error);
            } finally {
                this.cargando = false;
            }
        },

        /**
         * Formatea la fecha SQL al formato local de Argentina (DD/MM/AAAA HH:MM)
         */
        formatearFecha(fechaSql) {
            if (!fechaSql) return '';
            const fecha = new Date(fechaSql);
            return fecha.toLocaleDateString('es-AR') + ' ' + fecha.toLocaleTimeString('es-AR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}).mount('#appAdmin');