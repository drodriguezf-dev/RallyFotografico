<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

// Validar que el usuario es administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol_id = 2; // Rol gestor

    if ($nombre && $apellidos && $email && $password) {
        try {
            $stmt = $conexion->prepare("SELECT id FROM admins WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                // Email ya registrado
                header("Location: ../../frontend/admin/register-admin.php?mensaje=" . urlencode("El email ya está registrado.") . "&tipo=error");
                exit;
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("INSERT INTO admins (nombre, apellidos, email, password, rol_id) VALUES (:nombre, :apellidos, :email, :password, :rol_id)");
                $stmt->execute([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password' => $passwordHash,
                    'rol_id' => $rol_id
                ]);
                // Registro exitoso
                header("Location: ../../frontend/admin/register-admin.php?mensaje=" . urlencode("Gestor registrado correctamente.") . "&tipo=success");
                exit;
            }
        } catch (PDOException $e) {
            header("Location: ../../frontend/admin/register-admin.php?mensaje=" . urlencode("Error al registrar gestor.") . "&tipo=error");
            exit;
        }
    } else {
        header("Location: ../../frontend/admin/register-admin.php?mensaje=" . urlencode("Rellena todos los campos.") . "&tipo=error");
        exit;
    }
}
?>