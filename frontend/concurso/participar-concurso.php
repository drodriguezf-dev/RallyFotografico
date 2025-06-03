<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Participar en Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
    <?php if ($foto_subida_correctamente): ?>
        <div id="toast-exito" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50 transition-opacity duration-500">
            Imagen subida con éxito.
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast-exito');
                if (toast) {
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.remove(), 500);
                }
            }, 3000);
        </script>
    <?php endif; ?>
    <div class="bg-white p-8 rounded shadow-md w-full max-w-2xl">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Participar en: <?= htmlspecialchars($concurso['titulo']) ?></h1>

        <p class="mb-4 text-gray-700"><?= nl2br(htmlspecialchars($concurso['descripcion'])) ?></p>

        <div class="mb-6">
            <h2 class="font-semibold text-gray-800 mb-2">Reglas del concurso:</h2>
            <div class="bg-gray-50 p-3 border rounded text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($concurso['reglas']) ?></div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <?php if (!empty($mensaje_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= htmlspecialchars($mensaje_error) ?>
                </div>
            <?php endif; ?>

            <label class="block">
                <span class="text-gray-700">Selecciona una imagen:</span>
                <input type="file" name="foto" accept="<?= htmlspecialchars($concurso['formatos_aceptados']) ?>" required class="block w-full mt-1 border border-gray-300 p-2 rounded">
            </label>

            <label class="block">
                <span class="text-gray-700">Título de la foto:</span>
                <input type="text" name="titulo" required class="block w-full mt-1 border border-gray-300 p-2 rounded" placeholder="Escribe un título para tu foto">
            </label>

            <label class="block">
                <span class="text-gray-700">Descripción de la foto:</span>
                <textarea name="descripcion" rows="3" class="block w-full mt-1 border border-gray-300 p-2 rounded resize-none" placeholder="Escribe una breve descripción de tu foto"></textarea>
            </label>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Subir fotografía</button>
        </form>

        <p class="text-sm text-gray-600 mt-4">Máximo permitido por usuario: <?= $concurso['max_fotos_por_usuario'] ?> fotos</p>
        <p class="text-sm text-gray-600">Formatos aceptados: <?= $concurso['formatos_aceptados'] ?></p>
        <p class="text-sm text-gray-600">Tamaño máximo: <?= round($concurso['tamano_maximo_bytes'] / 1048576, 2) ?> MB</p>
        <?php
        // Obtener las fotos subidas por el usuario en este concurso
        $stmt = $conexion->prepare("SELECT id, titulo, descripcion, imagen_base64, mime_type, fecha_subida FROM fotografias WHERE usuario_id = :uid AND concurso_id = :cid ORDER BY fecha_subida DESC");
        $stmt->execute([
            'uid' => $_SESSION['usuario_id'],
            'cid' => $concurso_id
        ]);
        $mis_fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="mt-10">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Mis fotos</h2>

            <?php if (count($mis_fotos) === 0): ?>
                <p class="text-gray-600">Aún no has subido ninguna fotografía a este concurso.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <?php foreach ($mis_fotos as $foto): ?>
                        <div class="relative border rounded overflow-hidden shadow hover:shadow-md transition bg-white">
                            <!-- Botón de eliminar -->
                            <button type="button"
                                data-id="<?= htmlspecialchars($foto['id']) ?>"
                                class="delete-button bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 absolute top-2 right-2 z-10"
                                title="Eliminar">
                                &minus;
                            </button>

                            <!-- Imagen -->
                            <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= htmlspecialchars($foto['imagen_base64']) ?>"
                                alt="<?= htmlspecialchars($foto['titulo']) ?>"
                                class="w-full h-48 object-cover">

                            <div class="p-3">
                                <h3 class="text-gray-800 font-semibold text-base"><?= htmlspecialchars($foto['titulo']) ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>
                                <p class="text-gray-500 text-xs">Subida el <?= date('d/m/Y H:i', strtotime($foto['fecha_subida'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-6">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition">
                Volver al inicio
            </a>
        </div>
    </div>
    <!-- Modal de confirmación personalizado -->
    <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">¿Estás seguro?</h2>
            <p class="text-gray-600 mb-6">Esta acción eliminará la fotografía de forma permanente.</p>
            <div class="flex justify-end gap-4">
                <button id="cancel-button" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800 transition">Cancelar</button>
                <form id="delete-form" method="POST">
                    <input type="hidden" name="eliminar_id" id="foto-a-eliminar">
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white transition">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('delete-modal');
    const confirmForm = document.getElementById('delete-form');
    const inputFotoId = document.getElementById('foto-a-eliminar');
    const cancelButton = document.getElementById('cancel-button');

    // Abrir el modal y cargar el ID de la foto
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            const fotoId = this.getAttribute('data-id');
            inputFotoId.value = fotoId;
            modal.classList.remove('hidden');
        });
    });

    // Cancelar
    cancelButton.addEventListener('click', function () {
        modal.classList.add('hidden');
    });

    // Cerrar si hacen clic fuera del modal
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>


</html>