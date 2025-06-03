<?php
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrar Gestor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#1a1a1a] to-[#3a3a3a]">

    <!-- Cuadro de notificación -->
    <div id="success-notification" class="fixed top-0 left-1/2 transform -translate-x-1/2 -translate-y-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg flex items-center space-x-2 transition-transform duration-300 ease-in-out z-50" style="display:none;">
        <svg class="w-6 h-6 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span>Gestor registrado correctamente.</span>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Registrar gestor</h1>

        <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>

        <!-- Aquí se inyectan mensajes de error o éxito desde PHP si usas plantilla PHP -->
        <?php if (!empty($error)): ?>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const notification = document.getElementById('success-notification');
                    notification.style.display = 'flex';
                    notification.classList.remove('-translate-y-full');
                    setTimeout(() => {
                        notification.classList.add('-translate-y-full');
                    }, 2000);
                });
            </script>
        <?php endif; ?>

        <form id="gestor-form" method="POST" action="register-admin.php" class="space-y-4" novalidate>
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" required />
            </div>

            <div>
                <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" required />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" required />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" required />
            </div>

            <div>
                <button type="submit" class="w-full bg-amber-300 hover:bg-amber-400 text-white font-semibold py-2 px-4 rounded transition">
                    Registrar gestor
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="../public/gestion-usuarios.php" class="text-sm text-gray-600 hover:text-amber-500 font-semibold">Volver al panel de administración</a>
        </div>
    </div>

    <script>
        document.getElementById('gestor-form').addEventListener('submit', function(event) {
            const campos = [
                { id: 'nombre', mensaje: 'El nombre es obligatorio.' },
                { id: 'apellidos', mensaje: 'Los apellidos son obligatorios.' },
                { id: 'email', mensaje: 'El correo electrónico es obligatorio.' },
                { id: 'password', mensaje: 'La contraseña es obligatoria.' }
            ];

            const errorMessage = document.getElementById('error-message');
            let errores = [];

            // Validar cada campo
            for (const campo of campos) {
                const valor = document.getElementById(campo.id).value.trim();
                if (!valor) {
                    errores.push(campo.mensaje);
                }
            }

            // Mostrar errores si los hay
            if (errores.length > 0) {
                event.preventDefault();
                errorMessage.innerHTML = errores.map(e => `<li>${e}</li>`).join('');
                errorMessage.classList.remove('hidden');
            } else {
                errorMessage.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
