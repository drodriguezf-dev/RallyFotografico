<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$concurso_id = $_GET['id'];
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener concurso actual
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

$mensaje = $_GET['mensaje'] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Modificar Concurso</h1>

        <?php if ($mensaje): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-300 rounded">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($concurso): ?>
            <form method="post" action="../../backend/concurso/procesar-modificar-concurso.php?id=<?= $concurso_id ?>" class="space-y-4">
                <div>
                    <label class="block font-medium">Título:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($concurso['titulo']) ?>" required
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-medium">Descripción:</label>
                    <textarea name="descripcion" required rows="4"
                        class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($concurso['descripcion']) ?></textarea>
                </div>

                <div>
                    <label class="block font-medium">Fecha de inicio:</label>
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($concurso['fecha_inicio']) ?>" required
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-medium">Fecha de fin:</label>
                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($concurso['fecha_fin']) ?>" required
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div class="flex justify-between items-center mt-6">
                    <a href="../index.php" class="text-gray-600 hover:underline">← Volver</a>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">Guardar cambios</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>