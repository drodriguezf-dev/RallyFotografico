<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

// Verificación del rol
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../../frontend/index.php");
    exit;
}

// Verifica si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../frontend/concurso/crear-concurso.php?error=Acceso inválido");
    exit;
}

try {
    $pdo = conectarPDO($host, $user, $password, $bbdd);

    // Validación básica
    $campos_obligatorios = ['titulo', 'fecha_inicio', 'fecha_fin', 'fecha_inicio_votacion', 'fecha_fin_votacion'];
    foreach ($campos_obligatorios as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("Faltan campos obligatorios.");
        }
    }

    // Recoger datos
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'] ?? '';
    $reglas = $_POST['reglas'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $fecha_inicio_votacion = $_POST['fecha_inicio_votacion'];
    $fecha_fin_votacion = $_POST['fecha_fin_votacion'];
    $max_fotos = intval($_POST['max_fotos_por_usuario'] ?? 3);
    $max_votos_ip = intval($_POST['max_votos_por_ip'] ?? 2);
    $max_participantes = intval($_POST['max_participantes'] ?? 100);
    $tamano_max_bytes = intval($_POST['tamano_maximo_bytes'] ?? 2097152);
    $formatos = $_POST['formatos_aceptados'] ?? [];

    // Validar existencia del título
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM concursos WHERE titulo = :titulo");
    $stmt->execute([':titulo' => $titulo]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        header("Location: ../../frontend/concurso/crear-concurso.php?error=" . urlencode("Ya existe un concurso con ese título."));
        exit;
    }

    // Procesar imagen portada si existe
    $imagen_base64 = null;
    $mime_type = null;

    if (isset($_FILES['foto_concurso']) && $_FILES['foto_concurso']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto_concurso'];

        $contenido_binario = file_get_contents($foto['tmp_name']);
        $imagen_base64 = base64_encode($contenido_binario);
        $mime_type = $foto['type'];
    }

    // Insertar concurso con imagen
    $stmt = $pdo->prepare("
        INSERT INTO concursos (
            titulo, descripcion, reglas, fecha_inicio, fecha_fin,
            fecha_inicio_votacion, fecha_fin_votacion,
            max_fotos_por_usuario, max_votos_por_ip, max_participantes,
            tamano_maximo_bytes, formatos_aceptados,
            imagen_portada_base64, imagen_portada_mime
        ) VALUES (
            :titulo, :descripcion, :reglas, :fecha_inicio, :fecha_fin,
            :fecha_inicio_votacion, :fecha_fin_votacion,
            :max_fotos, :max_votos_ip, :max_participantes,
            :tamano_maximo_bytes, :formatos_aceptados,
            :imagen_base64, :mime_type
        )
    ");

    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':reglas' => $reglas,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
        ':fecha_inicio_votacion' => $fecha_inicio_votacion,
        ':fecha_fin_votacion' => $fecha_fin_votacion,
        ':max_fotos' => $max_fotos,
        ':max_votos_ip' => $max_votos_ip,
        ':max_participantes' => $max_participantes,
        ':tamano_maximo_bytes' => $tamano_max_bytes,
        ':formatos_aceptados' => implode(',', $formatos),
        ':imagen_base64' => $imagen_base64,
        ':mime_type' => $mime_type
    ]);

    // Redirigir con éxito
    header("Location: ../../frontend/concurso/crear-concurso.php?exito=Concurso creado correctamente");
    exit;
} catch (Exception $e) {
    header("Location: ../../frontend/concurso/crear-concurso.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}
