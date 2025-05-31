<?php
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: index.php");
    exit;
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foto_id = $_POST['foto_id'] ?? null;
    $concurso_id = $_POST['concurso_id'] ?? null;

    if ($foto_id && is_numeric($foto_id)) {
        if (isset($_POST['aceptar'])) {
            $stmt = $conexion->prepare("UPDATE fotografias SET estado = 'admitida' WHERE id = :id");
            $stmt->execute(['id' => $foto_id]);
            $mensaje = "Foto aceptada correctamente.";
        } elseif (isset($_POST['eliminar'])) {
            $stmt = $conexion->prepare("DELETE FROM fotografias WHERE id = :id");
            $stmt->execute(['id' => $foto_id]);
            $mensaje = "Foto eliminada correctamente.";
        }
    }

    // Eliminar concurso
    if ($concurso_id && is_numeric($concurso_id) && isset($_POST['eliminar_concurso'])) {
        // Solo permitir eliminar concursos pasados
        $stmt = $conexion->prepare("SELECT fecha_fin FROM concursos WHERE id = :id");
        $stmt->execute(['id' => $concurso_id]);
        $fecha_fin = $stmt->fetchColumn();

        if ($fecha_fin && strtotime($fecha_fin) < time()) {
            // Eliminar primero sus fotos
            $stmt = $conexion->prepare("DELETE FROM fotografias WHERE concurso_id = :id");
            $stmt->execute(['id' => $concurso_id]);
            // Luego eliminar el concurso
            $stmt = $conexion->prepare("DELETE FROM concursos WHERE id = :id");
            $stmt->execute(['id' => $concurso_id]);
            $mensaje = "Concurso eliminado correctamente.";
        }
    }
}

// Obtener todas las fotos con el concurso
$stmt = $conexion->prepare("
    SELECT f.id, f.titulo AS titulo_foto, f.descripcion, f.estado, f.imagen_base64, f.mime_type,
           c.id AS concurso_id, c.titulo AS titulo_concurso, c.fecha_fin
    FROM fotografias f 
    JOIN concursos c ON f.concurso_id = c.id
    ORDER BY f.id DESC
");
$stmt->execute();
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar fotos por concurso
$fotos_por_concurso = [];
$concurso_info = [];
foreach ($fotos as $foto) {
    $fotos_por_concurso[$foto['titulo_concurso']][] = $foto;
    $concurso_info[$foto['titulo_concurso']] = [
        'id' => $foto['concurso_id'],
        'fecha_fin' => $foto['fecha_fin']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Fotos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="space-y-10">
        <?php foreach ($fotos_por_concurso as $titulo_concurso => $fotos_concurso): ?>
            <?php
                $id_concurso = $concurso_info[$titulo_concurso]['id'];
                $fecha_fin = $concurso_info[$titulo_concurso]['fecha_fin'];
                $es_pasado = strtotime($fecha_fin) < time();
            ?>
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold border-b pb-1 <?= $es_pasado ? 'text-red-600' : '' ?>">
                        <?= htmlspecialchars($titulo_concurso) ?>
                        <?php if ($es_pasado): ?>
                            <span class="text-sm font-normal">(Finalizado)</span>
                        <?php endif; ?>
                    </h2>
                        <for method="post" onsubmit="return confirm('¿Eliminar este concurso y todas sus fotos?')">
                            <input type="hidden" name="concurso_id" value="<?= $id_concurso ?>">
                            <button name="eliminar_concurso" class="bg-red-700 text-white px-4 py-1 rounded hover:bg-red-800">
                                Eliminar concurso
                            </button>
                        </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($fotos_concurso as $foto): ?>
                        <div class="bg-white rounded-lg shadow p-4">
                            <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>"
                                 alt="Foto #<?= $foto['id'] ?>"
                                 class="w-full max-h-48 object-contain rounded mb-2 bg-gray-100">
                            <p><strong>Título:</strong> <?= htmlspecialchars($foto['titulo_foto']) ?></p>
                            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>
                            <p><strong>Estado:</strong> <?= htmlspecialchars($foto['estado']) ?></p>
                            <form method="post" class="mt-2 flex gap-2">
                                <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                                <?php if ($foto['estado'] !== 'admitida'): ?>
                                    <button name="aceptar" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Aceptar</button>
                                <?php endif; ?>
                                <button name="eliminar" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" onclick="return confirm('¿Seguro que deseas eliminar esta foto?')">Eliminar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- Botón para volver al índice -->
        <div class="mt-12 text-center">
            <a href="index.php" class="inline-block bg-gray-800 text-white px-5 py-2 rounded hover:bg-gray-900 transition">
                Volver al índice
            </a>
        </div>
    </div>
</body>
</html>
