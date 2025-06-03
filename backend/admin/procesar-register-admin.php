<?php
session_start();
require_once(".../utils/variables.php");
require_once(".../utils/funciones.php");

// Validar que el usuario es administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: .../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

$error = '';
$exito = '';

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
                $error = 'Ese correo ya está registrado.';
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
                $exito = 'Gestor registrado correctamente.';
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar gestor.';
        }
    } else {
        $error = 'Rellena todos los campos.';
    }
}
?>
