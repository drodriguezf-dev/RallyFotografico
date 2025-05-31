<?php
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

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

// Obtener IP del visitante
$ip_usuario = $_SERVER['REMOTE_ADDR'];

// Contar votos por IP
$stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
$stmt->execute([
    'cid' => $concurso_id,
    'ip' => $ip_usuario
]);
$votos_realizados = $stmt->fetchColumn();
$limite_votos = $concurso['max_votos_por_ip'];

// Procesar voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foto_id'])) {
    if ($votos_realizados < $limite_votos) {
        $foto_id = $_POST['foto_id'];

        // Verifica que la foto pertenezca al concurso
        $stmt = $conexion->prepare("SELECT id FROM fotografias WHERE id = :fid AND concurso_id = :cid AND estado = 'admitida'");
        $stmt->execute(['fid' => $foto_id, 'cid' => $concurso_id]);

        if ($stmt->fetch()) {
            $stmt = $conexion->prepare("INSERT INTO votos (concurso_id, fotografia_id, ip_votante) VALUES (:cid, :fid, :ip)");
            $stmt->execute([
                'cid' => $concurso_id,
                'fid' => $foto_id,
                'ip' => $ip_usuario
            ]);
            $voto_registrado = true;

            // Volver a contar los votos después de registrar uno nuevo
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
            $stmt->execute([
                'cid' => $concurso_id,
                'ip' => $ip_usuario
            ]);
            $votos_realizados = $stmt->fetchColumn();
        } else {
            echo "<p class='text-red-600'>Foto no válida.</p>";
        }
    } else {
        echo "<p class='text-red-600'>Has alcanzado el límite de votos permitidos para este concurso.</p>";
    }
}

// Obtener fotos admitidas
$stmt = $conexion->prepare("SELECT * FROM fotografias WHERE concurso_id = :cid AND estado = 'admitida'");
$stmt->execute(['cid' => $concurso_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Votar en: <?= htmlspecialchars($concurso['titulo']) ?></h1>
        <a href="index.php" class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded transition">
            Volver al índice
        </a>
        <p class="mb-2 text-gray-700">Puedes votar hasta <strong><?= $limite_votos ?></strong> fotos desde tu IP.</p>
        <p class="mb-6 text-gray-700">Has usado <strong><?= $votos_realizados ?></strong> de tus votos disponibles.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($fotos as $foto): ?>
                <div class="border rounded p-3 bg-gray-50 shadow-sm">
                    <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>" class="w-full h-48 object-cover rounded mb-2">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($foto['titulo']) ?></h3>
                    <p class="text-sm text-gray-600 mb-2"><?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>

                    <?php if ($votos_realizados < $limite_votos): ?>
                        <form method="POST">
                            <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition">Votar</button>
                        </form>
                    <?php else: ?>
                        <p class="text-red-500 text-sm">Límite de votos alcanzado.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
<script>
    <?php if (!empty($voto_registrado)): ?>
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

</html>