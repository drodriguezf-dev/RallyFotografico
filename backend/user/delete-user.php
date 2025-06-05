<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

// Validar sesión y rol de administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../../frontend/index.php");
    exit;
}

// Validar parámetro GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$id = (int) $_GET['id'];
$conexion = conectarPDO($host, $user, $password, $bbdd);

try {
    // Comprobamos si el usuario existe y es un usuario normal (rol_id = 3)
    $stmt = $conexion->prepare("SELECT rol_id FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario || $usuario['rol_id'] != 3) {
        header("Location: ../../frontend/admin/gestion-usuarios.php?error=Usuario+no+válido");
        exit;
    }

    // Eliminar usuario
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: ../../frontend/admin/gestion-usuarios.php?exito=Usuario+eliminado+correctamente");
    exit;

} catch (PDOException $e) {
    header("Location: ../../frontend/admin/gestion-usuarios.php?error=Error+al+eliminar+el+usuario");
    exit;
}
