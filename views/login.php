<?php
$initialTheme = $_COOKIE['rem_theme'] ?? $_COOKIE['rem.theme'] ?? 'light';
$initialTheme = in_array($initialTheme, ['light', 'dark'], true) ? $initialTheme : 'light';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo htmlspecialchars($initialTheme, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Sistema REM'; ?> - Iniciar sesión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.21.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
</head>
<body>
    <a class="skip-link" href="#loginCard">Saltar al formulario de inicio de sesión</a>

    <div class="login-shell">
        <section class="login-hero" aria-hidden="false">
            <div>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <?php if (file_exists(__DIR__ . '/assets/images/logo.png')): ?>
                        <img src="assets/images/logo.png" alt="DEIS Osorno" style="height: 48px;">
                    <?php else: ?>
                        <span class="avatar avatar-lg" style="background: rgba(255,255,255,0.18); color:#ffffff;">
                            <i class="ti ti-heart-rate-monitor"></i>
                        </span>
                    <?php endif; ?>
                    <div class="d-flex flex-column lh-1">
                        <span class="fs-4 fw-bold">Sistema REM</span>
                        <span class="login-hero-eyebrow">Servicio de Salud Osorno</span>
                    </div>
                </div>
                <h1>Gestión integral de Observaciones REM</h1>
                <p class="mb-4" style="max-width: 28rem; opacity: 0.9;">
                    Plataforma para registrar, supervisar y reportar las observaciones del
                    Resumen Estadístico Mensual de establecimientos de salud.
                </p>
                <div class="login-hero-meta">
                    <div>
                        <strong><i class="ti ti-shield-check me-1"></i>Seguro</strong>
                        <span>CSRF + sesiones</span>
                    </div>
                    <div>
                        <strong><i class="ti ti-users me-1"></i>2 roles</strong>
                        <span>Registrador / Supervisor</span>
                    </div>
                    <div>
                        <strong><i class="ti ti-calendar-stats me-1"></i>Anual</strong>
                        <span>Trabajo por año</span>
                    </div>
                </div>
            </div>
            <div class="login-hero-illustration" aria-hidden="true">
                <i class="ti ti-heart-rate-monitor"></i>
            </div>
            <div style="font-size: 0.8rem; opacity: 0.75;">
                <i class="ti ti-info-circle me-1"></i>
                Aplicación PHP + MySQL con autenticación por sesión y CSRF.
            </div>
        </section>

        <main class="login-panel">
            <div class="login-card" id="loginCard">
                <div class="mb-4 text-center d-md-none">
                    <?php if (file_exists(__DIR__ . '/assets/images/logo.png')): ?>
                        <img src="assets/images/logo.png" alt="DEIS Osorno" style="height: 56px;">
                    <?php endif; ?>
                </div>
                <h2 class="h3 fw-bold mb-1">Iniciar sesión</h2>
                <p class="text-secondary mb-4">Ingresa tus credenciales para continuar.</p>

                <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off" novalidate>
                    <div class="form-floating mb-3">
                        <input type="text" name="username" id="username" class="form-control"
                            placeholder="usuario" required autocomplete="username" autofocus>
                        <label for="username"><i class="ti ti-user me-1"></i>Usuario</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="contraseña" required minlength="6" autocomplete="current-password">
                        <label for="password"><i class="ti ti-lock me-1"></i>Contraseña</label>
                    </div>

                    <div class="form-floating mb-4">
                        <select name="year" id="year" class="form-select" required>
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear + 1; $y >= 2020; $y--) {
                                $selected = ($y == $currentYear) ? 'selected' : '';
                                echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
                            }
                            ?>
                        </select>
                        <label for="year"><i class="ti ti-calendar-event me-1"></i>Año de trabajo</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                        id="loginBtn">
                        <span id="loginBtnLabel">Iniciar sesión</span>
                        <i class="ti ti-arrow-right" id="loginBtnIcon"></i>
                    </button>

                    <div id="loginError" class="alert alert-danger mt-3 d-none" role="alert">
                        <i class="ti ti-alert-triangle me-1"></i>
                        <span id="loginErrorText">Credenciales inválidas.</span>
                    </div>
                    <div id="loginSuccess" class="alert alert-success mt-3 d-none" role="alert">
                        <i class="ti ti-circle-check me-1"></i>
                        <span id="loginSuccessText">¡Login exitoso! Redirigiendo...</span>
                    </div>
                </form>

                <details class="login-credentials">
                    <summary>
                        <i class="ti ti-key"></i>
                        Credenciales de prueba
                    </summary>
                    <div class="login-credentials-body">
                        <div class="d-flex justify-content-between">
                            <span><i class="ti ti-shield me-1"></i>Supervisor</span>
                            <code>supervisor1 / admin123</code>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span><i class="ti ti-user me-1"></i>Registrador</span>
                            <code>registrador2 / admin123</code>
                        </div>
                    </div>
                </details>

                <div class="text-center mt-4">
                    <a href="MANUAL_REGISTRO_OBSERVACIONES.html" target="_blank" class="text-secondary text-decoration-none small">
                        <i class="ti ti-book me-1"></i>Manual de usuario
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const loginBtn = document.getElementById('loginBtn');
            const loginBtnLabel = document.getElementById('loginBtnLabel');
            const loginBtnIcon = document.getElementById('loginBtnIcon');
            const loginError = document.getElementById('loginError');
            const loginErrorText = document.getElementById('loginErrorText');
            const loginSuccess = document.getElementById('loginSuccess');
            const loginSuccessText = document.getElementById('loginSuccessText');

            loginError.classList.add('d-none');
            loginSuccess.classList.add('d-none');

            loginBtn.disabled = true;
            loginBtnLabel.textContent = 'Iniciando sesión...';
            loginBtnIcon.className = 'ti ti-loader-2 spin';

            const formData = {
                username: document.getElementById('username').value.trim(),
                password: document.getElementById('password').value,
                year: document.getElementById('year').value
            };

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const text = await response.text();
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    data = { success: false, message: text || 'Respuesta inválida del servidor' };
                }

                if (response.ok && data.success) {
                    loginSuccessText.textContent = '¡Login exitoso! Redirigiendo...';
                    loginSuccess.classList.remove('d-none');
                    setTimeout(function () {
                        window.location.href = 'index.php';
                    }, 800);
                } else {
                    loginErrorText.textContent = data.message || 'Error al iniciar sesión';
                    loginError.classList.remove('d-none');
                    loginBtn.disabled = false;
                    loginBtnLabel.textContent = 'Iniciar sesión';
                    loginBtnIcon.className = 'ti ti-arrow-right';
                }
            } catch (error) {
                console.error('Error:', error);
                loginErrorText.textContent = 'Error al conectar con el servidor';
                loginError.classList.remove('d-none');
                loginBtn.disabled = false;
                loginBtnLabel.textContent = 'Iniciar sesión';
                loginBtnIcon.className = 'ti ti-arrow-right';
            }
        }

        // Tiny inline style for spinning icon (avoids depending on tabler.min.js)
        const styleEl = document.createElement('style');
        styleEl.textContent = '.spin { animation: rem-spin 0.8s linear infinite; }';
        document.head.appendChild(styleEl);
    </script>
</body>
</html>
