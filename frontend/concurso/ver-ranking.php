<?php
require_once('../../utils/variables.php');
require_once('../../utils/funciones.php');
$conexion = conectarPDO($host, $user, $password, $bbdd);

$concurso_id = $_GET['id'] ?? null;
if (!is_numeric($concurso_id)) {
    header("Location: ../index.php");
    exit;
}

// Obtener el concurso
$stmt = $conexion->prepare("SELECT titulo FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener las 3 fotos más votadas
$stmt = $conexion->prepare("
    SELECT f.titulo, f.descripcion, f.imagen_base64, f.mime_type, COUNT(v.id) AS votos
    FROM fotografias f
    LEFT JOIN votos v ON f.id = v.fotografia_id
    WHERE f.concurso_id = :cid
    GROUP BY f.id
    ORDER BY votos DESC
    LIMIT 3
");
$stmt->execute(['cid' => $concurso_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking - <?= htmlspecialchars($concurso['titulo']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes ascend {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-rise {
            animation: ascend 1s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-5xl mx-auto text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-12">🏆 Top 3 - <?= htmlspecialchars($concurso['titulo']) ?></h1>

        <div class="flex justify-center items-end gap-8">
            <!-- Segundo lugar -->
            <?php if (isset($fotos[1])): ?>
                <div class="w-1/4 animate-rise" style="animation-delay: 0.2s">
                    <img src="data:<?= $fotos[1]['mime_type'] ?>;base64,<?= $fotos[1]['imagen_base64'] ?>" class="w-full h-64 object-cover rounded-xl shadow-md border-4 border-silver mb-2">
                    <h2 class="text-xl font-semibold text-gray-700">🥈 <?= htmlspecialchars($fotos[1]['titulo']) ?></h2>
                    <p class="text-gray-600 text-sm"><?= $fotos[1]['votos'] ?> votos</p>
                </div>
            <?php endif; ?>

            <!-- Primer lugar -->
            <?php if (isset($fotos[0])): ?>
                <div class="w-1/3 animate-rise" style="animation-delay: 0.4s">
                    <img src="data:<?= $fotos[0]['mime_type'] ?>;base64,<?= $fotos[0]['imagen_base64'] ?>" class="w-full h-80 object-cover rounded-xl shadow-lg border-4 border-yellow-400 mb-2">
                    <h2 class="text-2xl font-bold text-yellow-600">🥇 <?= htmlspecialchars($fotos[0]['titulo']) ?></h2>
                    <p class="text-gray-700 text-base"><?= $fotos[0]['votos'] ?> votos</p>
                </div>
            <?php endif; ?>

            <!-- Tercer lugar -->
            <?php if (isset($fotos[2])): ?>
                <div class="w-1/4 animate-rise" style="animation-delay: 0.6s">
                    <img src="data:<?= $fotos[2]['mime_type'] ?>;base64,<?= $fotos[2]['imagen_base64'] ?>" class="w-full h-56 object-cover rounded-xl shadow-md border-4 border-orange-300 mb-2">
                    <h2 class="text-xl font-semibold text-gray-700">🥉 <?= htmlspecialchars($fotos[2]['titulo']) ?></h2>
                    <p class="text-gray-600 text-sm"><?= $fotos[2]['votos'] ?> votos</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-10">
            <a href="../index.php" class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">Volver al inicio</a>
        </div>
    </div>
</body>
</html>