<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

// Verificar sesión y rol
if (!isset($_SESSION['admin_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../public/index.php");
    exit;
}

// Conectar con la BBDD
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener usuarios normales (rol_id = 3)
$sqlUsuarios = "SELECT id, nombre, email FROM usuarios WHERE rol_id = 3 ORDER BY nombre";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Obtener gestores (rol_id = 2)
$sqlGestores = "SELECT id, nombre, email FROM admins WHERE rol_id = 2 ORDER BY nombre";
$stmtGestores = $conexion->prepare($sqlGestores);
$stmtGestores->execute();
$gestores = $stmtGestores->fetchAll(PDO::FETCH_ASSOC);

// Devolver datos en formato JSON para el frontend
header('Content-Type: application/json');
echo json_encode([
    'usuarios' => $usuarios,
    'gestores' => $gestores,
]);
