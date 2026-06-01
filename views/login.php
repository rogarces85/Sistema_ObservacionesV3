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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
    <style>
        body { background: linear-gradient(135deg, #003366 0%, #0B71B9 100%); min-height: 100vh; }
    </style>
</head>
<body class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <img src="assets/images/logo.png" alt="DEIS Osorno" style="height: 80px;">
            <h1 class="h2 mt-3 mb-1 text-white">Sistema REM</h1>
            <p class="text-white opacity-75">Servicio de Salud Osorno</p>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>

                <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-user"></i>
                            </span>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Ingrese su usuario" required autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Año de Trabajo</label>
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

                    <div id="loginError" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
                    <div id="loginSuccess" class="alert alert-success d-none mt-3 mb-0" role="alert"></div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="MANUAL_REGISTRO_OBSERVACIONES.html" target="_blank" class="text-white text-decoration-none opacity-75 hover-opacity-100">
                <i class="ti ti-book me-1"></i> Manual de Usuario
            </a>
        </div>

        <div class="card mt-3">
            <div class="card-body text-center py-2">
                <p class="text-secondary small mb-1">Credenciales de prueba:</p>
                <div class="row g-2 small">
                    <div class="col-6 text-end text-secondary">Supervisor:</div>
                    <div class="col-6 text-start font-monospace">supervisor1 / admin123</div>
                    <div class="col-6 text-end text-secondary">Registrador:</div>
                    <div class="col-6 text-start font-monospace">registrador1 / admin123</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const loginBtn = document.getElementById('loginBtn');
            const loginError = document.getElementById('loginError');
            const loginSuccess = document.getElementById('loginSuccess');

            loginError.classList.add('d-none');
            loginSuccess.classList.add('d-none');

            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Iniciando sesión...';

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
                    setTimeout(() => { window.location.href = 'index.php'; }, 1000);
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
