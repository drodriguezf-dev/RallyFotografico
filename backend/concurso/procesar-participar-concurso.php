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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    if ($fotos_subidas >= $concurso['max_fotos_por_usuario']) {
        $mensaje_error = "Ya has subido el número máximo de fotos permitido para este concurso.";
    } else {
        $foto = $_FILES['foto'];

        // Validaciones
        $formatos_aceptados = explode(",", $concurso['formatos_aceptados']);
        if (!in_array($foto['type'], $formatos_aceptados)) {
            $mensaje_error = "Formato de imagen no aceptado.";
        } elseif ($foto['size'] > $concurso['tamano_maximo_bytes']) {
            $mensaje_error = "Tamaño de imagen excedido. Máximo permitido: " . ($concurso['tamano_maximo_bytes'] / 1048576) . " MB";
        } else {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($titulo)) {
                $mensaje_error = "El título es obligatorio.";
            } else {
                // Leer contenido del archivo e ir preparando datos
                $contenido_binario = file_get_contents($foto['tmp_name']);
                $imagen_base64 = base64_encode($contenido_binario);
                $mime_type = $foto['type'];

                // Insertar en la base de datos
                try {
                    $stmt = $conexion->prepare("
                        INSERT INTO fotografias (usuario_id, concurso_id, titulo, descripcion, imagen_base64, mime_type)
                        VALUES (:uid, :cid, :titulo, :descripcion, :imagen_base64, :mime_type)
                    ");
                    $stmt->execute([
                        'uid' => $_SESSION['usuario_id'],
                        'cid' => $concurso_id,
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'imagen_base64' => $imagen_base64,
                        'mime_type' => $mime_type
                    ]);

                    $foto_subida_correctamente = true;
                } catch (PDOException $e) {
                    $mensaje_error = "Error al guardar en la base de datos: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}
// Eliminar foto si se envía una solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $foto_id = (int) $_POST['eliminar_id'];

    // Verifica que la foto pertenezca al usuario actual
    $stmt = $conexion->prepare("SELECT id FROM fotografias WHERE id = :fid AND usuario_id = :uid AND concurso_id = :cid");
    $stmt->execute([
        'fid' => $foto_id,
        'uid' => $_SESSION['usuario_id'],
        'cid' => $concurso_id
    ]);

    if ($stmt->fetch()) {
        $stmt = $conexion->prepare("DELETE FROM fotografias WHERE id = :fid");
        $stmt->execute(['fid' => $foto_id]);
        // Recarga la página para reflejar el cambio
        header("Location: participar-concurso.php?id=" . $concurso_id);
        exit;
    } else {
        $mensaje_error = "No tienes permiso para eliminar esta fotografía.";
    }
}
?>