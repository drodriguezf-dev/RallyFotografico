<?php
session_start();
require_once 'utils/db.php'; // conexión PDO
require_once 'utils/auth.php'; // para verificar login y roles

// Verifica si el usuario está autenticado y tiene permisos
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el rol del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT rol_id FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$rol = $stmt->fetchColumn();

if ($rol != 1 && $rol != 2) { // Solo admin o gestor
    header("Location: listado.php");
    exit;
}

// Validar que se ha pasado un ID de concurso
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listado.php");
    exit;
}

$concurso_id = $_GET['id'];

// Obtener datos del concurso
$stmt = $pdo->prepare("SELECT * FROM concursos WHERE id = ?");
$stmt->execute([$concurso_id]);
$concurso = $stmt->fetch();

if (!$concurso) {
    echo "Concurso no encontrado.";
    exit;
}

// Obtener fotos del concurso
$stmt = $pdo->prepare("SELECT f.*, u.nombre AS autor FROM fotos f JOIN usuarios u ON f.usuario_id = u.id WHERE f.concurso_id = ?");
$stmt->execute([$concurso_id]);
$fotos = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión del Concurso</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .foto-card {
            display: inline-block;
            width: 200px;
            margin: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            vertical-align: top;
            text-align: center;
        }
        img { max-width: 100%; height: auto; border-radius: 4px; }
        .estado { font-weight: bold; }
        .acciones form { display: inline; }
    </style>
</head>
<body>

<h1>Concurso: <?= htmlspecialchars($concurso['titulo']) ?></h1>
<p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($concurso['descripcion'])) ?></p>
<p><strong>Fecha de inicio:</strong> <?= htmlspecialchars($concurso['fecha_inicio']) ?></p>

<h2>Fotos presentadas (<?= count($fotos) ?>)</h2>

<?php if (count($fotos) == 0): ?>
    <p>No hay fotos aún en este concurso.</p>
<?php else: ?>
    <?php foreach ($fotos as $foto): ?>
        <div class="foto-card">
            <img src="<?= htmlspecialchars($foto['ruta']) ?>" alt="Foto">
            <p><strong>Autor:</strong> <?= htmlspecialchars($foto['autor']) ?></p>
            <p class="estado">Estado: <?= htmlspecialchars($foto['estado']) ?></p>

            <div class="acciones">
                <!-- Botones de acción -->
                <form method="post" action="gestionar-foto.php">
                    <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                    <input type="hidden" name="concurso_id" value="<?= $concurso_id ?>">
                    <button name="accion" value="aprobar">Aprobar</button>
                    <button name="accion" value="rechazar">Rechazar</button>
                    <button name="accion" value="eliminar" onclick="return confirm('¿Estás seguro de eliminar esta foto?')">Eliminar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<p><a href="panel-gestion.php">← Volver al panel</a></p>

</body>
</html>
