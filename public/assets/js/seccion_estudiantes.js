// public/assets/js/seccion_estudiantes.js
// JavaScript para gestionar la inscripcion de estudiantes en una seccion
// Compatible al 100% con el esquema de base de datos en produccion (`estudiantes.seccion_id`)

(function () {
    const BASE_URL = window.BASE_URL || '/';

    const btnInscribirEstudiante = document.getElementById('btnInscribirEstudiante');
    const formInscripcion = document.getElementById('formInscripcion');
    const modalInscripcionElemento = document.getElementById('modalInscripcion');

    let modalInscripcion = null;
    if (modalInscripcionElemento) {
        modalInscripcion = new bootstrap.Modal(modalInscripcionElemento);
    }

    function init() {
        if (btnInscribirEstudiante && modalInscripcion) {
            btnInscribirEstudiante.addEventListener('click', abrirModalInscripcion);
        }

        if (formInscripcion) {
            formInscripcion.addEventListener('submit', inscribirEstudiante);
        }

        document.addEventListener('click', function (evento) {
            const botonQuitar = evento.target.closest('.js-quitar-estudiante');

            if (botonQuitar) {
                confirmarQuitar(botonQuitar);
            }
        });
    }

    function abrirModalInscripcion() {
        formInscripcion.reset();
        modalInscripcion.show();
    }

    function inscribirEstudiante(evento) {
        evento.preventDefault();

        const formData = new FormData(formInscripcion);

        fetch(BASE_URL + 'estudiante/asignarSeccion', {
            method: 'POST',
            body: formData
        })
        .then(function (respuesta) {
            return respuesta.text();
        })
        .then(function (texto) {
            let datos;
            try {
                datos = JSON.parse(texto);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error del servidor',
                    text: 'La respuesta del servidor no es JSON valido.'
                });
                return;
            }

            if (datos.status === 'success') {
                if (modalInscripcion) {
                    modalInscripcion.hide();
                }
                Swal.fire({
                    icon: 'success',
                    title: datos.title,
                    text: datos.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function () {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: datos.title,
                    text: datos.message
                });
            }
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexion',
                text: 'No se pudo comunicar con el servidor.'
            });
        });
    }

    function confirmarQuitar(boton) {
        const id = boton.dataset.id;
        const nombre = boton.dataset.nombre;

        Swal.fire({
            icon: 'warning',
            title: 'Confirmar retiro de sección',
            text: '¿Estás seguro de retirar a "' + nombre + '" de esta sección? Se eliminarán sus notas de esta asignatura.',
            showCancelButton: true,
            confirmButtonText: 'Sí, retirar',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.isConfirmed) {
                quitarEstudiante(id);
            }
        });
    }

    function quitarEstudiante(id) {
        const formData = new FormData();
        formData.append('estudiante_id', id);

        fetch(BASE_URL + 'estudiante/quitarSeccion', {
            method: 'POST',
            body: formData
        })
        .then(function (respuesta) {
            return respuesta.text();
        })
        .then(function (texto) {
            let datos;
            try {
                datos = JSON.parse(texto);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error del servidor',
                    text: 'La respuesta del servidor no es JSON valido.'
                });
                return;
            }

            if (datos.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: datos.title,
                    text: datos.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function () {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: datos.title,
                    text: datos.message
                });
            }
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexion',
                text: 'No se pudo comunicar con el servidor.'
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();