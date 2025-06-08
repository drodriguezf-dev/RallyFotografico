<?php
session_start();

require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

// Solo administradores pueden acceder
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Crear Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <h1 class="text-3xl font-bold mb-6 text-center">Crear Nuevo Concurso</h1>

    <!-- Contenedor de errores dinámicos -->
    <div id="errorBox" class="max-w-3xl mx-auto bg-red-100 text-red-700 border border-red-300 rounded p-4 mb-4 text-center font-semibold hidden"></div>

    <?php if (isset($_GET['error'])): ?>
        <div class="max-w-3xl mx-auto bg-red-100 text-red-700 border border-red-300 rounded p-4 mb-4 text-center font-semibold">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php elseif (isset($_GET['exito'])): ?>
        <div class="max-w-3xl mx-auto bg-green-100 text-green-700 border border-green-300 rounded p-4 mb-4 text-center font-semibold">
            <?php echo htmlspecialchars($_GET['exito']); ?>
        </div>
    <?php endif; ?>
    <form id="form-concurso" method="POST" action="../../backend/concurso/procesar-crear-concurso.php" class="max-w-3xl mx-auto bg-white shadow-md rounded p-6 space-y-4" enctype="multipart/form-data">
        <div>
            <label class="block font-medium">Título:</label>
            <input type="text" name="titulo" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
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
                <input type="datetime-local" name="fecha_inicio" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Fecha de fin:</label>
                <input type="datetime-local" name="fecha_fin" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Inicio votación:</label>
                <input type="datetime-local" name="fecha_inicio_votacion" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            </div>
            <div>
                <label class="block font-medium">Fin votación:</label>
                <input type="datetime-local" name="fecha_fin_votacion" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
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
            <label class="block font-medium mb-2" for="foto_concurso">Foto del Concurso (opcional):</label>
            <input type="file" name="foto_concurso" id="foto_concurso" accept="image/jpeg, image/png, image/webp" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
        </div>

        <div>
            <label class="block font-medium mb-2">Formatos aceptados:</label>
            <div class="grid gap-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="formatos_aceptados[]" value="image/jpeg" class="text-green-500 focus:ring-green-300" checked>
                    <span>JPEG (.jpg)</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="formatos_aceptados[]" value="image/png" class="text-green-500 focus:ring-green-300" checked>
                    <span>PNG (.png)</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="formatos_aceptados[]" value="image/webp" class="text-green-500 focus:ring-green-300">
                    <span>WebP (.webp)</span>
                </label>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Crear Concurso</button>
        </div>
    </form>

    <div class="text-center mt-6">
        <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">Volver al inicio</a>
    </div>
    <script>
        const form = document.getElementById('form-concurso');
        const errorBox = document.getElementById('errorBox');

        form.addEventListener('submit', function(e) {
            let errors = [];

            // Validar título
            const titulo = form.titulo.value.trim();
            if (!titulo) errors.push("El título es obligatorio.");

            // Validar fechas
            const fecha_inicio = form.fecha_inicio.value;
            const fecha_fin = form.fecha_fin.value;
            const fecha_inicio_votacion = form.fecha_inicio_votacion.value;
            const fecha_fin_votacion = form.fecha_fin_votacion.value;

            if (!fecha_inicio) errors.push("La fecha de inicio es obligatoria.");
            if (!fecha_fin) errors.push("La fecha de fin es obligatoria.");
            if (!fecha_inicio_votacion) errors.push("La fecha de inicio de votación es obligatoria.");
            if (!fecha_fin_votacion) errors.push("La fecha de fin de votación es obligatoria.");

            if (fecha_inicio && fecha_fin) {
                const inicio = new Date(fecha_inicio);
                const fin = new Date(fecha_fin);
                const ahora = new Date();

                if (fin < inicio) {
                    errors.push("La fecha de fin no puede ser anterior a la fecha de inicio.");
                }
                if (fin <= ahora) {
                    errors.push("No se puede crear un concurso que ya haya terminado.");
                }
            }

            if (fecha_inicio_votacion && fecha_fin_votacion) {
                const inicioVot = new Date(fecha_inicio_votacion);
                const finVot = new Date(fecha_fin_votacion);

                if (finVot < inicioVot) {
                    errors.push("La fecha de fin de votación no puede ser anterior a la fecha de inicio de votación.");
                }
            }

            // Validar que al menos un formato esté seleccionado
            const formatos = form.querySelectorAll('input[name="formatos_aceptados[]"]:checked');
            if (formatos.length === 0) errors.push("Debes seleccionar al menos un formato aceptado.");

            if (errors.length > 0) {
                e.preventDefault();
                errorBox.innerHTML = errors.join("<br>");
                errorBox.classList.remove("hidden");
                errorBox.scrollIntoView({
                    behavior: "smooth"
                });
            } else {
                errorBox.classList.add("hidden");
            }
        });
        if (fecha_inicio && fecha_inicio_votacion) {
            const inicio = new Date(fecha_inicio);
            const inicioVot = new Date(fecha_inicio_votacion);

            if (inicioVot < inicio) {
                errors.push("La fecha de inicio de votación no puede ser anterior a la fecha de inicio del concurso.");
            }
        }
    </script>
</body>

</html>