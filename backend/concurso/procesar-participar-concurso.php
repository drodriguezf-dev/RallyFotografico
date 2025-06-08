<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../frontend/index.php");
    exit;
}

if (!isset($_POST['concurso_id']) || !is_numeric($_POST['concurso_id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$concurso_id = $_POST['concurso_id'];

// Obtener datos del concurso
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    header("Location: ../participar-concurso.php?id=$concurso_id&error=concurso_no_encontrado");
    exit;
}

// Validar fechas del concurso
$fecha_actual = date('Y-m-d H:i:s');
if ($fecha_actual < $concurso['fecha_inicio'] || $fecha_actual > $concurso['fecha_fin']) {
    header("Location: ../participar-concurso.php?id=$concurso_id&error=fuera_de_fecha");
    exit;
}

// Eliminar fotografía
if (isset($_POST['eliminar_id'])) {
    $foto_id = (int) $_POST['eliminar_id'];

    $stmt = $conexion->prepare("SELECT id FROM fotografias WHERE id = :fid AND usuario_id = :uid AND concurso_id = :cid");
    $stmt->execute([
        'fid' => $foto_id,
        'uid' => $_SESSION['usuario_id'],
        'cid' => $concurso_id
    ]);

    if ($stmt->fetch()) {
        $stmt = $conexion->prepare("DELETE FROM fotografias WHERE id = :fid");
        $stmt->execute(['fid' => $foto_id]);
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&success=eliminada");
    } else {
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=no_autorizado");
    }
    exit;
}

// Subir fotografía
if (isset($_FILES['foto'])) {

    // Verificar que no se haya superado el número máximo de participantes
    $stmt = $conexion->prepare("SELECT COUNT(DISTINCT usuario_id) 
FROM fotografias 
WHERE concurso_id = :cid
");
    $stmt->execute(['cid' => $concurso_id]);
    $participantes_actuales = $stmt->fetchColumn();

    if ($participantes_actuales >= $concurso['max_participantes']) {
        // Si el usuario ya ha participado, se le permite subir más fotos hasta su límite
        $stmt = $conexion->prepare("SELECT COUNT(*) 
    FROM fotografias 
    WHERE usuario_id = :uid AND concurso_id = :cid
");
        $stmt->execute([
            'uid' => $_SESSION['usuario_id'],
            'cid' => $concurso_id
        ]);
        $ya_participo = $stmt->fetchColumn();

        if ($ya_participo == 0) {
            header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=participantes_completos");
            exit;
        }
    }
    // Contar fotos ya subidas por el usuario
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM fotografias WHERE usuario_id = :uid AND concurso_id = :cid");
    $stmt->execute([
        'uid' => $_SESSION['usuario_id'],
        'cid' => $concurso_id
    ]);
    $fotos_subidas = $stmt->fetchColumn();

    if ($fotos_subidas >= $concurso['max_fotos_por_usuario']) {
        header("Location:../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=limite_excedido");
        exit;
    }

    $foto = $_FILES['foto'];
    $formatos_aceptados = explode(",", $concurso['formatos_aceptados']);

    if (!in_array($foto['type'], $formatos_aceptados)) {
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=formato_no_valido");
        exit;
    }

    if ($foto['size'] > $concurso['tamano_maximo_bytes']) {
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=imagen_grande");
        exit;
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($titulo)) {
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=titulo_vacio");
        exit;
    }

    // Guardar imagen
    $contenido_binario = file_get_contents($foto['tmp_name']);
    $imagen_base64 = base64_encode($contenido_binario);
    $mime_type = $foto['type'];

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

        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&success=subida");
    } catch (PDOException $e) {
        header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id&error=bd");
    }

    exit;
}

header("Location: ../../frontend/concurso/participar-concurso.php?id=$concurso_id");
exit;
