// public/assets/js/nota.js
// JavaScript del modulo de notas
// Las notas se cargan y editan estudiante por estudiante (no de forma masiva)
// Regla de coherencia: la nota final solo se muestra con el 100% de notas
// ACTUALIZACION FUNCIONAL:
// - Si `data.periodo_estado === 'cerrado'`, deshabilita los inputs numericos y oculta el boton de guardar
//   para garantizar modo de solo lectura en periodos academicos cerrados.

(function () {
    const BASE_URL = window.BASE_URL || '/';

    const formNotas = document.getElementById('formNotas');
    const modalNotasElemento = document.getElementById('modalNotas');
    const tituloModalNotas = document.getElementById('tituloModalNotas');
    const notaEstudianteId = document.getElementById('notaEstudianteId');
    const notaSeccionId = document.getElementById('notaSeccionId');
    const infoEstudiante = document.getElementById('infoEstudiante');
    const cuerpoActividades = document.getElementById('cuerpoActividades');
    const notaFinalCalculada = document.getElementById('notaFinalCalculada');
    const estadoEstudiante = document.getElementById('estadoEstudiante');
    const btnGuardarNotasModal = document.getElementById('btnGuardarNotasModal');

    const modalNotas = new bootstrap.Modal(modalNotasElemento);

    function init() {
        if (formNotas) {
            formNotas.addEventListener('submit', guardarNotas);
        }

        document.addEventListener('click', function (evento) {
            const botonVer = evento.target.closest('.js-ver-notas');

            if (botonVer) {
                cargarNotasEstudiante(botonVer);
            }
        });

        if (cuerpoActividades) {
            cuerpoActividades.addEventListener('input', calcularNotaFinal);
        }
    }

    function cargarNotasEstudiante(boton) {
        const estudianteId = boton.dataset.estudianteId;
        const seccionId = boton.dataset.seccionId;
        const nombre = boton.dataset.nombre;

        notaEstudianteId.value = estudianteId;
        notaSeccionId.value = seccionId;
        tituloModalNotas.textContent = 'Calificaciones de ' + nombre;
        infoEstudiante.textContent = 'Cargando datos...';
        cuerpoActividades.innerHTML = '';
        notaFinalCalculada.textContent = '---';
        estadoEstudiante.textContent = '---';

        const formData = new FormData();
        formData.append('estudiante_id', estudianteId);
        formData.append('seccion_id', seccionId);

        fetch(BASE_URL + 'nota/ver', {
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
                renderizarModal(datos.data);
                modalNotas.show();
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

    // Construye el contenido del modal con las actividades y notas de forma segura (sin XSS)
    function renderizarModal(data) {
        const estudiante = data.estudiante;
        const actividades = data.actividades;
        const esPeriodoCerrado = data.periodo_estado === 'cerrado';

        if (btnGuardarNotasModal) {
            btnGuardarNotasModal.style.display = esPeriodoCerrado ? 'none' : '';
        }

        // Construimos la ficha del estudiante usando nodos DOM seguros
        infoEstudiante.textContent = '';
        const contenedorIzq = document.createElement('div');
        const labelEst = document.createElement('span');
        labelEst.className = 'text-muted small d-block';
        labelEst.textContent = esPeriodoCerrado ? 'Estudiante inscrito (Acta Cerrada · Solo Lectura)' : 'Estudiante inscrito';
        const nombreStrong = document.createElement('strong');
        nombreStrong.className = 'text-dark fs-6';
        nombreStrong.textContent = estudiante.nombres + ' ' + estudiante.apellidos;
        contenedorIzq.appendChild(labelEst);
        contenedorIzq.appendChild(nombreStrong);

        const badgeCedula = document.createElement('span');
        badgeCedula.className = 'ce-code-pill tabular-nums';
        badgeCedula.textContent = 'C.I. ' + estudiante.cedula;

        infoEstudiante.appendChild(contenedorIzq);
        infoEstudiante.appendChild(badgeCedula);

        cuerpoActividades.innerHTML = '';

        actividades.forEach(function (actividad) {
            const fila = document.createElement('tr');

            const celdaNombre = document.createElement('td');
            celdaNombre.className = 'fw-semibold text-dark';
            celdaNombre.textContent = actividad.nombre_actividad;
            fila.appendChild(celdaNombre);

            const celdaPorcentaje = document.createElement('td');
            celdaPorcentaje.className = 'text-center tabular-nums';
            celdaPorcentaje.textContent = parseFloat(actividad.porcentaje).toFixed(2) + '%';
            fila.appendChild(celdaPorcentaje);

            const celdaNota = document.createElement('td');
            celdaNota.className = 'text-center';

            const inputNota = document.createElement('input');
            inputNota.type = 'number';
            inputNota.className = 'form-control text-center fw-bold tabular-nums js-input-nota';
            inputNota.name = 'notas[' + actividad.actividad_id + ']';
            inputNota.min = '0';
            inputNota.max = '20';
            inputNota.step = '0.01';
            inputNota.placeholder = 'Sin nota (0-20)';
            if (actividad.nota !== null) {
                inputNota.value = actividad.nota;
            }
            if (esPeriodoCerrado) {
                inputNota.disabled = true;
                inputNota.readOnly = true;
            }
            celdaNota.appendChild(inputNota);
            fila.appendChild(celdaNota);

            cuerpoActividades.appendChild(fila);
        });

        calcularNotaFinal();
    }

    function calcularNotaFinal() {
        const inputs = document.querySelectorAll('.js-input-nota');
        const totalInputs = inputs.length;
        let notaFinal = 0;
        let totalConNota = 0;

        inputs.forEach(function (input) {
            const valor = parseFloat(input.value);
            if (!isNaN(valor) && input.value !== '') {
                const fila = input.closest('tr');
                const celdaPorcentaje = fila.children[1];
                const porcentajeTexto = celdaPorcentaje.textContent.replace('%', '');
                const porcentaje = parseFloat(porcentajeTexto);

                if (!isNaN(porcentaje)) {
                    notaFinal += valor * porcentaje / 100;
                    totalConNota++;
                }
            }
        });

        notaFinal = Math.round(notaFinal * 100) / 100;
        estadoEstudiante.textContent = '';

        if (totalInputs === 0 || totalConNota === 0) {
            notaFinalCalculada.textContent = '---';
            notaFinalCalculada.className = 'text-center fs-5 fw-bold tabular-nums text-muted';
            const badge = document.createElement('span');
            badge.className = 'ce-badge ce-badge-neutral';
            badge.textContent = '● Sin notas';
            estadoEstudiante.appendChild(badge);
            return;
        }

        if (totalConNota < totalInputs) {
            notaFinalCalculada.textContent = '---';
            notaFinalCalculada.className = 'text-center fs-5 fw-bold tabular-nums text-muted';
            const badge = document.createElement('span');
            badge.className = 'ce-badge ce-badge-warning';
            badge.textContent = '● En curso (' + totalConNota + '/' + totalInputs + ' evaluadas)';
            estadoEstudiante.appendChild(badge);
            return;
        }

        notaFinalCalculada.textContent = notaFinal.toFixed(2);

        const badge = document.createElement('span');
        if (notaFinal >= 13) {
            notaFinalCalculada.className = 'text-center fs-5 fw-bold tabular-nums text-success';
            badge.className = 'ce-badge ce-badge-success';
            badge.textContent = '● Aprobado';
        } else {
            notaFinalCalculada.className = 'text-center fs-5 fw-bold tabular-nums text-danger';
            badge.className = 'ce-badge ce-badge-danger';
            badge.textContent = '● Reprobado';
        }
        estadoEstudiante.appendChild(badge);
    }

    function guardarNotas(evento) {
        evento.preventDefault();

        const formData = new FormData(formNotas);

        fetch(BASE_URL + 'nota/guardar', {
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
                modalNotas.hide();
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