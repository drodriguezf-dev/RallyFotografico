<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");
session_start();

$conexion = conectarPDO($host, $user, $password, $bbdd);

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../login.php");
    exit;
}

$concurso_id = $_GET['id'];
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    $mensaje_error = "Concurso no encontrado.";
}

$stmt = $conexion->prepare("SELECT * FROM fotografias WHERE concurso_id = :cid");
$stmt->execute(['cid' => $concurso_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen px-4 py-8">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Gestionar: <?= htmlspecialchars($concurso['titulo'] ?? 'Concurso desconocido') ?></h1>
        <a href="../index.php" class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded transition">Volver al índice</a>

        <?php if (isset($mensaje_error)): ?>
            <p class="text-red-600 mb-4"><?= htmlspecialchars($mensaje_error) ?></p>
        <?php else: ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2 text-gray-700">Descripción</h2>
                <p class="text-gray-700 whitespace-pre-line mb-4"><?= nl2br(htmlspecialchars($concurso['descripcion'])) ?></p>

                <h2 class="text-xl font-semibold mb-2 text-gray-700">Reglas</h2>
                <p class="text-gray-700 whitespace-pre-line mb-4"><?= nl2br(htmlspecialchars($concurso['reglas'])) ?></p>

                <h2 class="text-xl font-semibold mb-2 text-gray-700">Configuración de Fotos</h2>
                <ul class="text-gray-700 list-disc list-inside mb-4">
                    <li><strong>Formatos permitidos:</strong> <?= htmlspecialchars($concurso['formatos_aceptados']) ?></li>
                    <li><strong>Tamaño máximo por foto:</strong> <?= number_format($concurso['tamano_maximo_bytes'] / (1024 * 1024), 2) ?> MB</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($fotos as $foto): ?>
                <div class="border rounded p-3 bg-gray-50 shadow-sm flex flex-col h-[24rem]">
                    <div class="flex-shrink-0 h-48 w-full mb-2 overflow-hidden flex items-center justify-center bg-white rounded">
                        <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>"
                            alt="<?= htmlspecialchars($foto['titulo']) ?>"
                            class="max-h-full max-w-full object-contain">
                    </div>

                    <div class="flex-grow overflow-auto">
                        <h3 class="font-semibold text-lg"><?= htmlspecialchars($foto['titulo']) ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>

                        <ul class="text-xs text-gray-500 mb-2 space-y-1">
                            <li><strong>Estado:</strong> <?= htmlspecialchars($foto['estado']) ?></li>
                            <?php
                            $base64_length = strlen($foto['imagen_base64']);
                            $peso_bytes = ($base64_length * 3) / 4;
                            ?>
                            <li><strong>Peso:</strong> <?= number_format($peso_bytes / 1024, 2) ?> KB</li>
                            <li><strong>Formato:</strong> <?= htmlspecialchars($foto['mime_type']) ?></li>
                        </ul>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <form method="post" action="../../backend/admin/procesar-gestionar-fotos.php" class="eliminar-foto-form" data-titulo="<?= htmlspecialchars($foto['titulo']) ?>">
                                <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                                <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"> <!-- URL actual -->

                                <?php if ($foto['estado'] !== 'admitida'): ?>
                                    <button type="submit" name="aceptar" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Aceptar
                                    </button>
                                <?php endif; ?>
                                <?php if ($foto['estado'] !== 'rechazada'): ?>
                                    <button type="submit" name="rechazar" class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">
                                        Rechazar
                                    </button>
                                <?php endif; ?>

                                <button type="submit" name="eliminar" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" onclick="return confirm('¿Seguro que deseas eliminar esta foto?');">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>