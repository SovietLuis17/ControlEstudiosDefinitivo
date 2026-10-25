// public/assets/js/seccion.js
// JavaScript del modulo de secciones
// Usa Fetch API y SweetAlert2 para comunicarse con el servidor

(function () {
    const BASE_URL = window.BASE_URL || '/';

    const btnNuevaSeccion = document.getElementById('btnNuevaSeccion');
    const formSeccion = document.getElementById('formSeccion');
    const modalSeccionElemento = document.getElementById('modalSeccion');
    const tituloModalSeccion = document.getElementById('tituloModalSeccion');
    const seccionIdInput = document.getElementById('seccionId');

    let modalSeccion = null;
    if (modalSeccionElemento) {
        modalSeccion = new bootstrap.Modal(modalSeccionElemento);
    }

    function init() {
        if (btnNuevaSeccion) {
            btnNuevaSeccion.addEventListener('click', abrirModalNuevo);
        }

        if (formSeccion) {
            formSeccion.addEventListener('submit', guardarSeccion);
        }

        document.addEventListener('click', function (evento) {
            const botonEditar = evento.target.closest('.js-editar-seccion');
            const botonEliminar = evento.target.closest('.js-eliminar-seccion');

            if (botonEditar) {
                abrirModalEditar(botonEditar);
            }

            if (botonEliminar) {
                confirmarEliminacion(botonEliminar);
            }
        });
    }

    function abrirModalNuevo() {
        tituloModalSeccion.textContent = 'Nueva Sección';
        formSeccion.reset();
        seccionIdInput.value = '0';
        modalSeccion.show();
    }

    function abrirModalEditar(boton) {
        tituloModalSeccion.textContent = 'Editar Sección';
        formSeccion.reset();
        seccionIdInput.value = boton.dataset.id;
        document.getElementById('materia_id').value = boton.dataset.materia;
        document.getElementById('profesor_id').value = boton.dataset.profesor;
        document.getElementById('periodo_id').value = boton.dataset.periodo;
        document.getElementById('codigo_seccion').value = boton.dataset.codigo;
        document.getElementById('aula').value = boton.dataset.aula;
        document.getElementById('horario').value = boton.dataset.horario;
        modalSeccion.show();
    }

    function guardarSeccion(evento) {
        evento.preventDefault();

        const formData = new FormData(formSeccion);

        fetch(BASE_URL + 'seccion/guardar', {
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
                modalSeccion.hide();
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

    function confirmarEliminacion(boton) {
        const id = boton.dataset.id;
        const codigo = boton.dataset.codigo;

        Swal.fire({
            icon: 'warning',
            title: 'Confirmar eliminacion',
            text: 'Estas seguro de eliminar la seccion "' + codigo + '"?',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (resultado) {
            if (resultado.isConfirmed) {
                eliminarSeccion(id);
            }
        });
    }

    function eliminarSeccion(id) {
        const formData = new FormData();
        formData.append('id', id);

        fetch(BASE_URL + 'seccion/eliminar', {
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