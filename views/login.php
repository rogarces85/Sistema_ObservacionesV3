<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema REM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body style="background: linear-gradient(135deg, #003366 0%, #0B71B9 100%);">
    <div class="flex items-center justify-center h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 slide-up">
            <div class="text-center mb-6">
                <img src="assets/images/logo.png" alt="DEIS Osorno" style="height: 80px; margin: 0 auto 12px auto; display: block;">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Sistema REM</h1>
                <p class="text-slate-600" style="color: #0B71B9;">Servicio de Salud Osorno</p>
                <div class="w-16 h-1 mx-auto mt-3 rounded-full" style="background: linear-gradient(90deg, #003366, #00AEEF);"></div>
            </div>

            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario</label>
                        <input type="text" name="username" id="username" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 transition-all"
                            style="outline: none;"
                            onfocus="this.style.borderColor='#0B71B9'; this.style.boxShadow='0 0 0 3px rgba(11, 113, 185, 0.2)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                            placeholder="Ingrese su usuario" autocomplete="username">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 transition-all"
                            style="outline: none;"
                            onfocus="this.style.borderColor='#0B71B9'; this.style.boxShadow='0 0 0 3px rgba(11, 113, 185, 0.2)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                            placeholder="Ingrese su contraseña" autocomplete="current-password">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Año de Trabajo</label>
                        <select name="year" id="year"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 transition-all"
                            style="outline: none;"
                            onfocus="this.style.borderColor='#0B71B9'; this.style.boxShadow='0 0 0 3px rgba(11, 113, 185, 0.2)';"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear + 1; $y >= 2020; $y--) {
                                $selected = ($y == $currentYear) ? 'selected' : '';
                                echo "<option value='{$y}' {$selected}>{$y}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <button type="submit"
                    class="w-full text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-xl"
                    style="background: linear-gradient(135deg, #0B71B9 0%, #003366 100%);"
                    id="loginBtn"
                    onmouseover="this.style.background='linear-gradient(135deg, #003366 0%, #0B71B9 100%)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #0B71B9 0%, #003366 100%)'">
                    Iniciar Sesión
                </button>

                <div id="loginError"
                    class="hidden mt-4 p-3 bg-rose-100 text-rose-700 rounded-xl text-sm font-medium text-center">
                </div>

                <div id="loginSuccess"
                    class="hidden mt-4 p-3 bg-emerald-100 text-emerald-700 rounded-xl text-sm font-medium text-center">
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="MANUAL_REGISTRO_OBSERVACIONES.html" target="_blank"
                   class="inline-flex items-center gap-2 text-sm font-medium transition-colors hover:underline"
                   style="color: #0B71B9;">
                    <span>📖</span> Manual de Usuario
                </a>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-500 text-center mb-2">Credenciales de prueba:</p>
                <div class="text-xs text-slate-600 space-y-1">
                    <div class="flex justify-between px-4">
                        <span>Supervisor:</span>
                        <span class="font-mono">supervisor1 / admin123</span>
                    </div>
                    <div class="flex justify-between px-4">
                        <span>Registrador:</span>
                        <span class="font-mono">registrador1 / admin123</span>
                    </div>
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

            // Ocultar mensajes previos
            loginError.classList.add('hidden');
            loginSuccess.classList.add('hidden');

            // Deshabilitar botón
            loginBtn.disabled = true;
            loginBtn.textContent = 'Iniciando sesión...';

            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                year: document.getElementById('year').value
            };

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    loginSuccess.textContent = '¡Login exitoso! Redirigiendo...';
                    loginSuccess.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    loginError.textContent = data.message || 'Credenciales inválidas';
                    loginError.classList.remove('hidden');

                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Iniciar Sesión';
                }
            } catch (error) {
                console.error('Error:', error);
                loginError.textContent = 'Error al conectar con el servidor';
                loginError.classList.remove('hidden');

                loginBtn.disabled = false;
                loginBtn.textContent = 'Iniciar Sesión';
            }
        }
    </script>
</body>

</html>