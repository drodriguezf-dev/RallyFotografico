<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$usuario_id = $_SESSION['usuario_id'];

// Obtener concursos
$stmt = $conexion->prepare("SELECT id, titulo, fecha_fin FROM concursos");
$stmt->execute();
$concursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener fotos del usuario
$stmt = $conexion->prepare("
    SELECT f.id, f.titulo AS titulo_foto, f.descripcion, f.estado, f.imagen_base64, f.mime_type,
           c.id AS concurso_id, c.titulo AS titulo_concurso, c.fecha_fin
    FROM fotografias f 
    JOIN concursos c ON f.concurso_id = c.id
    WHERE f.usuario_id = :usuario_id
    ORDER BY f.id DESC
");
$stmt->execute(['usuario_id' => $usuario_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar fotos por concurso
$fotos_por_concurso = [];
foreach ($fotos as $foto) {
    $fotos_por_concurso[$foto['concurso_id']][] = $foto;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Fotos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="space-y-10">
        <?php foreach ($concursos as $concurso): ?>
            <?php
            $id_concurso = $concurso['id'];
            $titulo_concurso = $concurso['titulo'];
            $fecha_fin = $concurso['fecha_fin'];
            $es_pasado = strtotime($fecha_fin) < time();
            $fotos_concurso = $fotos_por_concurso[$id_concurso] ?? [];
            ?>
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold border-b pb-1 <?= $es_pasado ? 'text-red-600' : '' ?>">
                        <?= htmlspecialchars($titulo_concurso) ?>
                        <?php if ($es_pasado): ?>
                            <span class="text-sm font-normal">(Finalizado)</span>
                        <?php endif; ?>
                    </h2>

                    <?php if (!$es_pasado): ?>
                        <a href="../concurso/participar-concurso.php?id=<?= $id_concurso ?>" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition">
                            Participar
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($fotos_concurso)): ?>
                    <p class="text-gray-500 italic">No has enviado fotos a este concurso.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($fotos_concurso as $foto): ?>
                            <div class="bg-white rounded-lg shadow p-4">
                                <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>"
                                     alt="Foto #<?= $foto['id'] ?>"
                                     class="w-full max-h-48 object-contain rounded mb-2 bg-gray-100">
                                <p><strong>Título:</strong> <?= htmlspecialchars($foto['titulo_foto']) ?></p>
                                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>
                                <p><strong>Estado:</strong> 
                                    <span class="<?= $foto['estado'] === 'admitida' ? 'text-green-600' : ($foto['estado'] === 'rechazada' ? 'text-red-600' : 'text-gray-600') ?>">
                                        <?= htmlspecialchars($foto['estado']) ?>
                                    </span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <div class="mt-12 text-center">
            <a href="../index.php" class="inline-block bg-gray-800 text-white px-5 py-2 rounded hover:bg-gray-900 transition">
                Volver al índice
            </a>
        </div>
    </div>
</body>
</html>
