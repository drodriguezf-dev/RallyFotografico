<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

$conexion = conectarPDO($host, $user, $password, $bbdd);

$mensaje_error = $_GET['error'] ?? '';
$voto_exito = isset($_GET['voto']) && $_GET['voto'] === 'ok';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$concurso_id = $_GET['id'];
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    $mensaje_error = "Concurso no encontrado.";
}

$limite_votos = $concurso['max_votos_por_ip'] ?? 0;
$ip_usuario = $_SERVER['REMOTE_ADDR'];

$stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
$stmt->execute(['cid' => $concurso_id, 'ip' => $ip_usuario]);
$votos_realizados = $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT * FROM fotografias WHERE concurso_id = :cid AND estado = 'admitida'");
$stmt->execute(['cid' => $concurso_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener IDs de fotos votadas por esta IP en este concurso
$stmt = $conexion->prepare("SELECT fotografia_id FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
$stmt->execute(['cid' => $concurso_id, 'ip' => $ip_usuario]);
$fotos_votadas_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Votar en Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen px-4 py-8">
    <div id="notificacion-exito" class="fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 bg-green-100 text-green-800 px-6 py-3 rounded shadow-lg flex items-center gap-2 opacity-0 transition-all duration-500 z-50">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span>¡Voto registrado!</span>
    </div>

    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <?php if ($mensaje_error): ?>
            <p class="text-red-600 mb-4"><?= htmlspecialchars($mensaje_error) ?></p>
        <?php endif; ?>

        <h1 class="text-2xl font-bold mb-4 text-gray-800">Votar en: <?= htmlspecialchars($concurso['titulo'] ?? 'Concurso desconocido') ?></h1>
        <a href="../index.php" class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded transition">Volver al índice</a>

        <p class="mb-2 text-gray-700">Puedes votar hasta <strong><?= $limite_votos ?></strong> fotos desde tu IP.</p>
        <p class="mb-6 text-gray-700">Has usado <strong><?= $votos_realizados ?></strong> de tus votos disponibles.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($fotos as $foto): ?>
                <div class="border rounded p-3 bg-gray-50 shadow-sm">
                    <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>" class="w-full h-48 object-cover rounded mb-2">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($foto['titulo']) ?></h3>
                    <p class="text-sm text-gray-600 mb-2"><?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>

                    <?php
                    $ya_votada = in_array($foto['id'], $fotos_votadas_ids);
                    ?>

                    <?php if ($ya_votada): ?>
                        <form method="POST" action="../../backend/concurso/procesar-eliminar-voto.php" class="flex justify-end">
                            <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                            <input type="hidden" name="concurso_id" value="<?= $concurso_id ?>">
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition text-sm">Eliminar voto</button>
                        </form>
                    <?php elseif ($votos_realizados < $limite_votos): ?>
                        <form method="POST" action="../../backend/concurso/procesar-votar-concurso.php" class="flex justify-end">
                            <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                            <input type="hidden" name="concurso_id" value="<?= $concurso_id ?>">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition">Votar</button>
                        </form>
                    <?php else: ?>
                        <p class="text-red-500 text-sm">Límite de votos alcanzado.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        <?php if ($voto_exito): ?>
            window.addEventListener('DOMContentLoaded', () => {
                const noti = document.getElementById('notificacion-exito');
                noti.classList.remove('opacity-0');
                noti.classList.add('opacity-100');
                setTimeout(() => {
                    noti.classList.remove('opacity-100');
                    noti.classList.add('opacity-0');
                }, 3000);
            });
        <?php endif; ?>
    </script>
</body>

</html>