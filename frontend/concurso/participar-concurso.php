<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$concurso_id = $_GET['id'];

// Obtener datos del concurso
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    echo "<p>Concurso no encontrado.</p>";
    exit;
}

$fecha_actual = date('Y-m-d H:i:s');
$foto_subida_correctamente = false;


// Verificar fechas y cantidad de fotos
if ($fecha_actual < $concurso['fecha_inicio'] || $fecha_actual > $concurso['fecha_fin']) {
    echo "<p>Este concurso no está disponible para participar en este momento.</p>";
    exit;
}

$mensaje_error = ""; // Variable para almacenar mensajes de error

// Contar fotos ya subidas por este usuario
$stmt = $conexion->prepare("SELECT COUNT(*) FROM fotografias WHERE usuario_id = :uid AND concurso_id = :cid");
$stmt->execute([
    'uid' => $_SESSION['usuario_id'],
    'cid' => $concurso_id
]);
$fotos_subidas = $stmt->fetchColumn();

// Obtener las fotos subidas por el usuario en este concurso
$stmt = $conexion->prepare("SELECT id, titulo, descripcion, imagen_base64, mime_type, fecha_subida, estado FROM fotografias WHERE usuario_id = :uid AND concurso_id = :cid ORDER BY fecha_subida DESC");
$stmt->execute([
    'uid' => $_SESSION['usuario_id'],
    'cid' => $concurso_id
]);
$mis_fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Participar en Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen">
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
    <div class="flex flex-col md:flex-row h-screen">

        <!-- COLUMNA IZQUIERDA: Descripción y reglas -->
        <div class="md:basis-2/5 p-8 pb-20 overflow-y-auto border-r border-gray-300 bg-white">
            <h1 class="text-3xl font-bold mb-6 text-gray-800">Participar en: <?= htmlspecialchars($concurso['titulo']) ?></h1>

            <p class="mb-8 text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($concurso['descripcion'])) ?></p>

            <h2 class="font-semibold text-gray-800 mb-3 mt-8 text-xl">Reglas del concurso:</h2>
            <div class="bg-gray-50 p-4 border rounded text-gray-700 whitespace-pre-wrap mb-8"><?= htmlspecialchars($concurso['reglas']) ?></div>

            <div class="text-sm text-gray-600 space-y-3 mb-12">
                <p><strong>Máximo permitido por usuario:</strong> <?= $concurso['max_fotos_por_usuario'] ?> fotos</p>
                <p><strong>Formatos aceptados:</strong> <?= $concurso['formatos_aceptados'] ?></p>
                <p><strong>Tamaño máximo:</strong> <?= round($concurso['tamano_maximo_bytes'] / 1048576, 2) ?> MB</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <?php
                $mensajes_error = [
                    'concurso_no_encontrado' => 'El concurso no existe.',
                    'fuera_de_fecha' => 'El concurso no está disponible actualmente.',
                    'no_autorizado' => 'No estás autorizado para eliminar esa foto.',
                    'limite_excedido' => 'Has alcanzado el límite de fotos permitidas.',
                    'formato_no_valido' => 'El formato de la imagen no es válido.',
                    'imagen_grande' => 'La imagen supera el tamaño máximo permitido.',
                    'titulo_vacio' => 'El título no puede estar vacío.',
                    'bd' => 'Error inesperado al guardar la imagen.',
                ];
                $codigo = $_GET['error'];
                $mensaje = $mensajes_error[$codigo] ?? 'Error desconocido.';
                ?>
                <div class="mb-6 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <!-- Botón de volver -->
            <div class="text-left mt-10">
                <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition">
                    Volver al inicio
                </a>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Formulario y fotos -->
        <div class="md:basis-3/5 p-8 overflow-y-auto">
            <!-- Formulario -->
            <form method="POST" action="../../backend/concurso/procesar-participar-concurso.php" enctype="multipart/form-data" class="space-y-4 mb-10">
                <?php if (!empty($mensaje_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?= htmlspecialchars($mensaje_error) ?>
                    </div>
                <?php endif; ?>

                <label class="block">
                    <span class="text-gray-700 font-medium">Selecciona una imagen:</span>
                    <input type="file" name="foto" accept="<?= htmlspecialchars($concurso['formatos_aceptados']) ?>" required class="block w-full mt-1 border border-gray-300 p-2 rounded">
                </label>

                <label class="block">
                    <span class="text-gray-700 font-medium">Título de la foto:</span>
                    <input type="text" name="titulo" required class="block w-full mt-1 border border-gray-300 p-2 rounded">
                </label>

                <label class="block">
                    <span class="text-gray-700 font-medium">Descripción de la foto:</span>
                    <textarea name="descripcion" rows="3" class="block w-full mt-1 border border-gray-300 p-2 rounded resize-none"></textarea>
                </label>

                <input type="hidden" name="concurso_id" value="<?= htmlspecialchars($concurso['id']) ?>">

                <?php if ($fotos_subidas >= $concurso['max_fotos_por_usuario']): ?>
                    <p class="text-red-600 font-semibold">Límite de fotos alcanzado. No puedes subir más imágenes.</p>
                <?php else: ?>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Subir fotografía</button>
                <?php endif; ?>
            </form>

            <!-- Galería de fotos -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Mis fotos</h2>
            <?php if (count($mis_fotos) === 0): ?>
                <p class="text-gray-600">Aún no has subido ninguna fotografía a este concurso.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($mis_fotos as $foto): ?>
                        <div class="relative border rounded overflow-hidden shadow hover:shadow-md transition bg-white">
                            <!-- Botón de eliminar -->
                            <button type="button"
                                data-id="<?= htmlspecialchars($foto['id']) ?>"
                                class="delete-button bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 absolute top-2 right-2 z-10"
                                title="Eliminar">
                                &minus;
                            </button>

                            <!-- Contenedor de imagen con estado -->
                            <div class="relative">
                                <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= htmlspecialchars($foto['imagen_base64']) ?>"
                                    alt="<?= htmlspecialchars($foto['titulo']) ?>"
                                    class="w-full h-48 object-cover">

                                <?php if (!empty($foto['estado'])): ?>
                                    <span class="<?php
                                                    switch ($foto['estado']) {
                                                        case 'admitida':
                                                            echo 'text-green-700 bg-green-100';
                                                            break;
                                                        case 'rechazada':
                                                            echo 'text-red-700 bg-red-100';
                                                            break;
                                                        case 'pendiente':
                                                        default:
                                                            echo 'text-yellow-700 bg-yellow-100';
                                                            break;
                                                    }
                                                    ?> text-xs font-medium px-2 py-1 rounded absolute bottom-2 left-2">
                                        <?= ucfirst(htmlspecialchars($foto['estado'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="p-3">
                                <h3 class="text-gray-800 font-semibold text-base mb-1"><?= htmlspecialchars($foto['titulo']) ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>
                                <p class="text-gray-500 text-xs">Subida el <?= date('d/m/Y H:i', strtotime($foto['fecha_subida'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Modal de confirmación personalizado -->
    <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">¿Estás seguro?</h2>
            <p class="text-gray-600 mb-6">Esta acción eliminará la fotografía de forma permanente.</p>
            <div class="flex justify-end gap-4">
                <button id="cancel-button" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800 transition">Cancelar</button>
                <form id="delete-form" method="POST" action="../../backend/concurso/procesar-participar-concurso.php">
                    <input type="hidden" name="eliminar_id" id="foto-a-eliminar">
                    <input type="hidden" name="concurso_id" value="<?= htmlspecialchars($concurso_id) ?>">
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white transition">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('delete-modal');
        const confirmForm = document.getElementById('delete-form');
        const inputFotoId = document.getElementById('foto-a-eliminar');
        const cancelButton = document.getElementById('cancel-button');

        // Abrir el modal y cargar el ID de la foto
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                const fotoId = this.getAttribute('data-id');
                inputFotoId.value = fotoId;
                modal.classList.remove('hidden');
            });
        });

        // Cancelar
        cancelButton.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        // Cerrar si hacen clic fuera del modal
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>


</html>