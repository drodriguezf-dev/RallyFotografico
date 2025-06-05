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
    header("Location: ../../frontend/admin/gestion-usuarios.php");
    exit;
}

$id = (int) $_GET['id'];
$conexion = conectarPDO($host, $user, $password, $bbdd);

try {
    // Comprobamos si el usuario existe y es un gestor
    $stmt = $conexion->prepare("SELECT rol_id FROM admins WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario || $usuario['rol_id'] != 2) {
        header("Location: ../../frontend/admin/gestion-usuarios.php?error=Gestor+no+válido");
        exit;
    }

    // Eliminar gestor
    $stmt = $conexion->prepare("DELETE FROM admins WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: ../../frontend/admin/gestion-usuarios.php?exito=Gestor+eliminado+correctamente");
    exit;

} catch (PDOException $e) {
    header("Location: ../../frontend/admin/gestion-usuarios.php?error=Error+al+eliminar+el+gestor");
    exit;
}
