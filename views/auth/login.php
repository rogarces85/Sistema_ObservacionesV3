<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Sistema de Observaciones REM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark">
                    <h1 class="navbar-brand-text">Sistema de Observaciones REM</h1>
                </a>
                <p class="text-muted">Servicio de Salud Osorno</p>
            </div>

            <div class="card card-md">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>

                    <div id="mensajeError" class="alert alert-danger d-none" role="alert"></div>
                    <div id="mensajeBloqueo" class="alert alert-warning d-none" role="alert"></div>

                    <form id="formLogin" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label" for="inputUsuario">Nombre de usuario</label>
                            <input type="text" id="inputUsuario" name="username" class="form-control"
                                   placeholder="Ingrese su usuario" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="inputPassword">Contraseña</label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="inputPassword" name="password" class="form-control"
                                       placeholder="Ingrese su contraseña" required>
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" id="togglePassword" title="Mostrar contraseña">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>
                                        </svg>
                                    </a>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="selectAnio">Año de trabajo</label>
                            <select id="selectAnio" name="year" class="form-select">
                            </select>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100" id="btnLogin">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                                    <path d="M9 12h12l-3 -3"/>
                                    <path d="M18 15l3 -3"/>
                                </svg>
                                Ingresar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted mt-3">
                <small>&copy; Servicio de Salud Osorno - Sistema de Observaciones REM</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Poblar selector de años (2020 hasta año actual + 1)
            const selectAnio = document.getElementById('selectAnio');
            const anioActual = new Date().getFullYear();
            for (let anio = 2020; anio <= anioActual + 1; anio++) {
                const opcion = document.createElement('option');
                opcion.value = anio;
                opcion.textContent = anio;
                if (anio === anioActual) opcion.selected = true;
                selectAnio.appendChild(opcion);
            }

            // Toggle mostrar/ocultar contraseña
            document.getElementById('togglePassword').addEventListener('click', (e) => {
                e.preventDefault();
                const input = document.getElementById('inputPassword');
                input.type = input.type === 'password' ? 'text' : 'password';
            });

            // Manejar envío del formulario
            const formLogin = document.getElementById('formLogin');
            const mensajeError = document.getElementById('mensajeError');
            const mensajeBloqueo = document.getElementById('mensajeBloqueo');
            const btnLogin = document.getElementById('btnLogin');

            formLogin.addEventListener('submit', async (e) => {
                e.preventDefault();

                mensajeError.classList.add('d-none');
                mensajeBloqueo.classList.add('d-none');
                btnLogin.disabled = true;
                btnLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';

                try {
                    const respuesta = await fetchAPI('api/auth.php?action=login', {
                        method: 'POST',
                        body: JSON.stringify({
                            username: document.getElementById('inputUsuario').value.trim(),
                            password: document.getElementById('inputPassword').value,
                            year: parseInt(document.getElementById('selectAnio').value)
                        })
                    });

                    if (respuesta.success) {
                        // Guardar CSRF token
                        localStorage.setItem('csrf_token', respuesta.data.csrf_token);
                        // Redirigir al dashboard
                        window.location.href = 'index.php';
                    } else {
                        if (respuesta.code === 429) {
                            mensajeBloqueo.textContent = respuesta.error;
                            mensajeBloqueo.classList.remove('d-none');
                        } else {
                            mensajeError.textContent = respuesta.error;
                            mensajeError.classList.remove('d-none');
                        }
                        btnLogin.disabled = false;
                        btnLogin.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M9 12h12l-3 -3"/><path d="M18 15l3 -3"/></svg> Ingresar';
                    }
                } catch (error) {
                    mensajeError.textContent = 'Error de conexión. Intente nuevamente.';
                    mensajeError.classList.remove('d-none');
                    btnLogin.disabled = false;
                    btnLogin.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M9 12h12l-3 -3"/><path d="M18 15l3 -3"/></svg> Ingresar';
                }
            });
        });
    </script>
</body>
</html>
