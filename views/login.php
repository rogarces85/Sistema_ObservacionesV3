<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login - Sistema REM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
</head>
<body class="d-flex flex-column bg-primary-lt" style="min-height: 100vh;">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img src="assets/images/logo.png" alt="DEIS Osorno" style="height: 70px; margin-bottom: 8px;">
                <h1 class="h2 fw-bold text-primary mb-1">Sistema REM</h1>
                <p class="text-secondary mb-0">Servicio de Salud Osorno</p>
            </div>

            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h3 text-center mb-4">Iniciar Sesión</h2>

                    <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label required" for="username">Usuario</label>
                            <input type="text" name="username" id="username" class="form-control" required
                                placeholder="Ingrese su usuario" autocomplete="username">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required" for="password">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required
                                placeholder="Ingrese su contraseña" autocomplete="current-password">
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="year">Año de Trabajo</label>
                            <select name="year" id="year" class="form-select">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear + 1; $y >= 2020; $y--) {
                                    $selected = ($y == $currentYear) ? 'selected' : '';
                                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                                Iniciar Sesión
                            </button>
                        </div>

                        <div id="loginError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        <div id="loginSuccess" class="alert alert-success mt-3 d-none" role="alert"></div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="MANUAL_REGISTRO_OBSERVACIONES.html" target="_blank" class="text-secondary">
                    📖 Manual de Usuario
                </a>
            </div>

            <div class="text-center mt-3">
                <div class="card card-sm">
                    <div class="card-body py-2">
                        <p class="text-secondary small mb-2">Credenciales de prueba:</p>
                        <div class="row g-2 text-secondary small">
                            <div class="col-6 text-end fw-semibold">Supervisor:</div>
                            <div class="col-6 text-start font-monospace">supervisor1 / admin123</div>
                            <div class="col-6 text-end fw-semibold">Registrador:</div>
                            <div class="col-6 text-start font-monospace">registrador1 / admin123</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const loginBtn = document.getElementById('loginBtn');
            const loginError = document.getElementById('loginError');
            const loginSuccess = document.getElementById('loginSuccess');

            loginError.classList.add('d-none');
            loginSuccess.classList.add('d-none');

            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';

            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                year: document.getElementById('year').value
            };

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    loginSuccess.textContent = '¡Login exitoso! Redirigiendo...';
                    loginSuccess.classList.remove('d-none');

                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    loginError.textContent = data.message || 'Credenciales inválidas';
                    loginError.classList.remove('d-none');

                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Iniciar Sesión';
                }
            } catch (error) {
                console.error('Error:', error);
                loginError.textContent = 'Error al conectar con el servidor';
                loginError.classList.remove('d-none');

                loginBtn.disabled = false;
                loginBtn.textContent = 'Iniciar Sesión';
            }
        }
    </script>
</body>
</html>
