<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema REM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);">
    <div class="flex items-center justify-center h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 slide-up">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Sistema REM</h1>
                <p class="text-slate-600">Servicio de Salud Osorno</p>
                <div class="w-16 h-1 bg-sky-600 mx-auto mt-4 rounded-full"></div>
            </div>

            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario</label>
                        <input type="text" name="username" id="username" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all"
                            placeholder="Ingrese su usuario" autocomplete="username">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all"
                            placeholder="Ingrese su contraseña" autocomplete="current-password">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Año de Trabajo</label>
                        <select name="year" id="year"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all">
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
                    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-xl"
                    id="loginBtn">
                    Iniciar Sesión
                </button>

                <div id="loginError"
                    class="hidden mt-4 p-3 bg-rose-100 text-rose-700 rounded-xl text-sm font-medium text-center">
                </div>

                <div id="loginSuccess"
                    class="hidden mt-4 p-3 bg-emerald-100 text-emerald-700 rounded-xl text-sm font-medium text-center">
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100">
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