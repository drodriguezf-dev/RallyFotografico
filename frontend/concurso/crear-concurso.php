<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Crear Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <h1 class="text-3xl font-bold mb-6 text-center">Crear Nuevo Concurso</h1>

    <div id="mensaje" class="mb-4 text-center font-semibold p-3 rounded hidden"></div>

    <form id="form-concurso" class="max-w-3xl mx-auto bg-white shadow-md rounded p-6 space-y-4">
        <div>
            <label class="block font-medium">Título:</label>
            <input type="text" name="titulo" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
        </div>

        <div>
            <label class="block font-medium">Descripción:</label>
            <textarea name="descripcion" rows="3" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300 resize-none"></textarea>
        </div>

        <div>
            <label class="block font-medium">Reglas:</label>
            <textarea name="reglas" rows="4" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300 resize-none"></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Fecha de inicio:</label>
                <input type="datetime-local" name="fecha_inicio" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Fecha de fin:</label>
                <input type="datetime-local" name="fecha_fin" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Inicio votación:</label>
                <input type="datetime-local" name="fecha_inicio_votacion" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Fin votación:</label>
                <input type="datetime-local" name="fecha_fin_votacion" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Máx. fotos por usuario:</label>
                <input type="number" name="max_fotos_por_usuario" value="3" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Máx. votos por IP:</label>
                <input type="number" name="max_votos_por_ip" value="2" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Máx. participantes:</label>
                <input type="number" name="max_participantes" value="100" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Tamaño máx. foto (MB):</label>
                <select name="tamano_maximo_bytes" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <option value="1048576">1 MB</option>
                    <option value="2097152" selected>2 MB</option>
                    <option value="5242880">5 MB</option>
                    <option value="10485760">10 MB</option>
                    <option value="20971520">20 MB</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-medium">Formatos aceptados (separados por coma):</label>
            <input type="text" name="formatos_aceptados" value="image/jpeg,image/png" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Crear Concurso</button>
        </div>
    </form>

    <div class="text-center mt-6">
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">Volver al inicio</a>
    </div>

    <script>
        const form = document.getElementById('form-concurso');
        const mensajeDiv = document.getElementById('mensaje');

        form.addEventListener('submit', async e => {
            e.preventDefault();
            mensajeDiv.classList.add('hidden');
            mensajeDiv.textContent = '';

            const formData = new FormData(form);

            try {
                const response = await fetch('../backend/crear_concurso.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                mensajeDiv.textContent = data.mensaje;
                mensajeDiv.classList.remove('hidden');

                if (data.mensaje.includes('Error')) {
                    mensajeDiv.classList.remove('bg-green-500');
                    mensajeDiv.classList.add('bg-red-500');
                } else {
                    mensajeDiv.classList.remove('bg-red-500');
                    mensajeDiv.classList.add('bg-green-500');
                    form.reset();
                }
            } catch (error) {
                mensajeDiv.textContent = 'Error al enviar el formulario.';
                mensajeDiv.classList.remove('hidden');
                mensajeDiv.classList.remove('bg-green-500');
                mensajeDiv.classList.add('bg-red-500');
            }
        });
    </script>
</body>

</html>
