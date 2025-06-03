<?php
require_once("../utils/variables.php");
require_once("../utils/funciones.php");
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
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    if ($titulo && $descripcion && $fecha_inicio && $fecha_fin) {
        $conexion = conectarPDO($host, $user, $password, $bbdd);

        try {
            $stmt = $conexion->prepare("
                UPDATE concursos 
                SET titulo = :titulo, descripcion = :descripcion, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin 
                WHERE id = :id
            ");
            $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'id' => $concurso_id
            ]);
            $mensaje = "Concurso actualizado correctamente.";
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }

    header("Location: ../modificar-concurso.php?id=$concurso_id&mensaje=" . urlencode($mensaje));
    exit;
}
