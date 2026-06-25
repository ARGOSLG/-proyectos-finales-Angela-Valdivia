<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Argos — Iniciar Sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-argos-darker min-h-screen flex items-center justify-center relative overflow-hidden transition-colors">

    <!-- Fondo con gradiente sutil -->
    <div class="absolute inset-0 bg-gradient-to-br from-argos-orange/10 via-transparent to-argos-green/10"></div>

    <!-- Botón modo oscuro/claro -->
    <button
        id="theme-toggle"
        class="absolute top-6 right-6 bg-white dark:bg-argos-dark hover:bg-gray-100 dark:hover:bg-argos-dark/80 text-gray-900 dark:text-argos-white p-3 rounded-xl shadow-md transition-colors z-10"
    >
        <svg id="icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg id="icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>

    <!-- Card de login -->
    <div class="relative z-10 w-full max-w-md mx-4">
        <div class="bg-white dark:bg-argos-dark rounded-2xl shadow-2xl p-8 transition-colors">

            <!-- Logo y título -->
            <div class="flex flex-col items-center mb-8">
                <div class="bg-argos-orange/20 rounded-2xl p-4 mb-4">
                    <svg class="w-10 h-10 text-argos-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-argos-white">ARGOS</h1>
                <p class="text-gray-400 text-sm mt-1">Sistema de monitoreo</p>
            </div>

            <!-- Mensaje de error -->
            <div id="error-message" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg p-3 mb-4"></div>

            <!-- Formulario -->
            <form id="login-form" class="space-y-5">
                <div>
                    <label class="block text-gray-900 dark:text-argos-white text-sm font-medium mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            placeholder="usuario@correo.com"
                            class="w-full bg-gray-50 dark:bg-argos-darker text-gray-900 dark:text-argos-white placeholder-gray-400 dark:placeholder-gray-500 rounded-xl py-3 pl-11 pr-4 border border-gray-300 dark:border-gray-700 focus:border-argos-orange focus:outline-none transition-colors"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-gray-900 dark:text-argos-white text-sm font-medium mb-2">Contraseña</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z" />
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="w-full bg-gray-50 dark:bg-argos-darker text-gray-900 dark:text-argos-white placeholder-gray-400 dark:placeholder-gray-500 rounded-xl py-3 pl-11 pr-4 border border-gray-300 dark:border-gray-700 focus:border-argos-orange focus:outline-none transition-colors"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    id="submit-btn"
                    class="w-full bg-argos-orange hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-colors flex items-center justify-center gap-2"
                >
                    <span id="submit-text">Acceder</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!--p class="text-center text-gray-400 text-sm mt-6">
                ¿Problemas con el acceso?
                <a href="#" class="text-argos-orange font-semibold hover:underline">Soporte Argos</a>
            </p-->

        </div>
    </div>

    <script>
        // Toggle modo oscuro/claro
        const html = document.documentElement;
        const iconSun = document.getElementById('icon-sun');
        const iconMoon = document.getElementById('icon-moon');
        const themeToggle = document.getElementById('theme-toggle');

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            iconSun.classList.toggle('hidden');
            iconMoon.classList.toggle('hidden');
        });

        // Login con fetch al API
        const form = document.getElementById('login-form');
        const errorMessage = document.getElementById('error-message');
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            errorMessage.classList.add('hidden');
            submitText.textContent = 'Ingresando...';
            submitBtn.disabled = true;

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Error al iniciar sesión');
                }

                // Guardar token y redirigir al panel
                localStorage.setItem('argos_token', data.token);
                localStorage.setItem('argos_user', JSON.stringify(data.user));

                window.location.href = '/dashboard';

            } catch (error) {
                errorMessage.textContent = error.message;
                errorMessage.classList.remove('hidden');
                submitText.textContent = 'Entrar a Argos';
                submitBtn.disabled = false;
            }
        });
    </script>

</body>
</html>