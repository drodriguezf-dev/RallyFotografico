<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol_id = 3; // Asignamos rol de usuario normal

    if ($nombre && $apellidos && $email && $password) {
        try {
            // Comprobar si ya existe ese email
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $error = 'Ese correo ya está registrado.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, email, password, rol_id) VALUES (:nombre, :apellidos, :email, :password, :rol_id)");
                $stmt->execute([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password' => $passwordHash,
                    'rol_id' => $rol_id
                ]);
                $exito = 'Usuario registrado correctamente. Puedes iniciar sesión.';
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar usuario.';
        }
    } else {
        $error = 'Rellena todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#1a1a1a] to-[#3a3a3a]">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Crear cuenta</h1>

        <?php if (!empty($error)): ?>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($exito) ?>
            </div>
        <?php endif; ?>

        <form id="register-form" method="POST" action="register.php" class="space-y-4">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>

            <div>
                <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>

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
                    Registrarse
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">¿Ya tienes una cuenta? 
                <a href="login.php" class="text-amber-500 hover:text-amber-600 font-semibold">Inicia sesión aquí</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('register-form').addEventListener('submit', function(event) {
            const campos = [
                { id: 'nombre', mensaje: 'Te falta el nombre.' },
                { id: 'apellidos', mensaje: 'Te faltan los apellidos.' },
                { id: 'email', mensaje: 'Te falta el correo electrónico.' },
                { id: 'password', mensaje: 'Te falta la contraseña.' }
            ];

            const errorMessage = document.getElementById('error-message');
            let errores = [];

            // Validar cada campo
            for (const campo of campos) {
                const valor = document.getElementById(campo.id).value.trim();
                if (!valor) {
                    errores.push(campo.mensaje); // Agregar el mensaje de error a la lista
                }
            }

            // Mostrar los errores si existen
            if (errores.length > 0) {
                event.preventDefault(); // Detener el envío del formulario
                errorMessage.innerHTML = errores.map(error => `<li>${error}</li>`).join(''); // Mostrar los errores como lista
                errorMessage.classList.remove('hidden'); // Mostrar el contenedor
            } else if (errorMessage) {
                errorMessage.classList.add('hidden'); // Ocultar el contenedor si no hay errores
            }
        });
    </script>

</body>
</html>
