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

        <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
        <div id="success-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 hidden"></div>

        <form id="register-form" class="space-y-4" novalidate>
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
                <a href="login-user.php" class="text-amber-500 hover:text-amber-600 font-semibold">Inicia sesión aquí</a>
            </p>
        </div>
    </div>

<script>
    const form = document.getElementById('register-form');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
        errorMessage.innerHTML = '';
        successMessage.innerHTML = '';

        const campos = [
            { id: 'nombre', mensaje: 'Te falta el nombre.' },
            { id: 'apellidos', mensaje: 'Te faltan los apellidos.' },
            { id: 'email', mensaje: 'Te falta el correo electrónico.' },
            { id: 'password', mensaje: 'Te falta la contraseña.' }
        ];

        let errores = [];

        // Validar campos vacíos
        for (const campo of campos) {
            const valor = document.getElementById(campo.id).value.trim();
            if (!valor) {
                errores.push(campo.mensaje);
            }
        }

        if (errores.length > 0) {
            errorMessage.innerHTML = '<ul><li>' + errores.join('</li><li>') + '</li></ul>';
            errorMessage.classList.remove('hidden');
            return;
        }

        // Preparar datos para enviar al backend
        const formData = new FormData(form);

        try {
            const response = await fetch('../../backend/user/procesar-register-user.php', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.error) {
                errorMessage.textContent = data.error;
                errorMessage.classList.remove('hidden');
            } else if (data.success) {
                successMessage.textContent = data.success;
                successMessage.classList.remove('hidden');
                form.reset();
            }
        } catch (err) {
            errorMessage.textContent = 'Error al comunicarse con el servidor.';
            errorMessage.classList.remove('hidden');
        }
    });
</script>

</body>
</html>
