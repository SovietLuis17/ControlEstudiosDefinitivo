// public/assets/js/perfil.js
// JavaScript del modulo "Mi Perfil y Seguridad"
// Gestiona la actualizacion de datos personales y el cambio seguro de contrasena via Fetch API

(function () {
    const BASE_URL = window.BASE_URL || '/';

    const formPerfilDatos = document.getElementById('formPerfilDatos');
    const formPerfilPassword = document.getElementById('formPerfilPassword');
    const inputNuevaPassword = document.getElementById('password_nueva');
    const contenedorRequisitos = document.getElementById('requisitosPasswordPerfil');

    function init() {
        if (formPerfilDatos) {
            formPerfilDatos.addEventListener('submit', guardarDatosPerfil);
        }

        if (formPerfilPassword) {
            formPerfilPassword.addEventListener('submit', cambiarPasswordPerfil);
        }

        if (inputNuevaPassword && contenedorRequisitos) {
            inputNuevaPassword.addEventListener('input', evaluarFortalezaPassword);
        }
    }

    function evaluarFortalezaPassword() {
        const val = inputNuevaPassword.value || '';
        const reglas = {
            len: val.length >= 8,
            upper: /[A-Z]/.test(val),
            lower: /[a-z]/.test(val),
            num: /\d/.test(val),
            sym: /[\-\*\+#\$\&\.\,\!\?\@\%\(\)_\=\[\]\{\}\|:;]/.test(val)
        };

        Object.keys(reglas).forEach(function (clave) {
            const item = contenedorRequisitos.querySelector('[data-req="' + clave + '"]');
            if (!item) return;
            const textoBase = item.textContent.replace(/^[●○]\s*/, '');
            if (reglas[clave]) {
                item.className = clave === 'sym' ? 'col-12 text-success fw-semibold' : 'col-6 text-success fw-semibold';
                item.textContent = '● ' + textoBase;
            } else {
                item.className = clave === 'sym' ? 'col-12 text-muted' : 'col-6 text-muted';
                item.textContent = '○ ' + textoBase;
            }
        });
    }

    function guardarDatosPerfil(evento) {
        evento.preventDefault();
        const formData = new FormData(formPerfilDatos);

        fetch(BASE_URL + 'perfil/guardarDatos', {
            method: 'POST',
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (datos) {
            if (datos.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: datos.title,
                    text: datos.message,
                    timer: 1800,
                    showConfirmButton: false
                }).then(function () {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: datos.title || 'Error',
                    text: datos.message || 'No se pudieron actualizar los datos.'
                });
            }
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor.'
            });
        });
    }

    function cambiarPasswordPerfil(evento) {
        evento.preventDefault();
        const formData = new FormData(formPerfilPassword);

        fetch(BASE_URL + 'perfil/cambiarPassword', {
            method: 'POST',
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (datos) {
            if (datos.status === 'success') {
                formPerfilPassword.reset();
                evaluarFortalezaPassword();
                Swal.fire({
                    icon: 'success',
                    title: datos.title,
                    text: datos.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: datos.title || 'Error',
                    text: datos.message || 'No se pudo actualizar la contraseña.'
                });
            }
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
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
