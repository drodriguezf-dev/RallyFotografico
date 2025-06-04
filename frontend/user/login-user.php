<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#1a1a1a] to-[#3a3a3a]">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Iniciar sesión</h1>

        <!-- Mostrar mensaje de error si existe -->
        <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 <?= empty($error) ? 'hidden' : '' ?>">
        </div>
        <?php if (isset($_GET['error'])): ?>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($_GET['error']) ?>
        <?php elseif (isset($_GET['registro'])): ?>
            <div id="register-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($_GET['registro']) ?>
    </div>
<?php endif; ?>

        <form id="login-form" method="POST" action="../../backend/user/procesar-login-user.php" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>

            <div>
                <button type="submit" class="w-full bg-amber-300 hover:bg-amber-400 text-white font-semibold py-2 px-4 rounded transition">
                    Entrar
                </button>
            </div>
        </form>

        <!-- Enlaces adicionales -->
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">¿No tienes una cuenta? 
                <a href="register-user.php" class="text-amber-500 hover:text-amber-600 font-semibold">Crea una aquí</a>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <a href="../index.php" class="text-amber-500 hover:text-amber-600 font-semibold">Volver al inicio</a>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <a href="../admin/login-admin.php" class="text-amber-500 hover:text-amber-600 font-semibold">Acceso para trabajadores</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorMessage = document.getElementById('error-message');

            if (!email && !password) {
                event.preventDefault();
                errorMessage.textContent = 'Por favor rellene los campos.';
                errorMessage.classList.remove('hidden');
            } else if (!email) {
                event.preventDefault();
                errorMessage.textContent = 'Indique el usuario.';
                errorMessage.classList.remove('hidden');
            } else if (!password) {
                event.preventDefault();
                errorMessage.textContent = 'Indique la contraseña.';
                errorMessage.classList.remove('hidden');
            } else {
                errorMessage.classList.add('hidden');
            }
        });
    </script>

</body>
</html>
