<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

header('Content-Type: application/json');

$response = ['error' => '', 'success' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol_id = 3; // usuario normal

    if ($nombre && $apellidos && $email && $password) {
        // Comprobar si el email ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            header("Location: ../../frontend/user/register-user.php?error=" . urlencode("Este correo ya está registrado."));
            exit;
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, email, password, rol_id) VALUES (:nombre, :apellidos, :email, :password, :rol_id)");
            $stmt->execute([
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'password' => $passwordHash,
                'rol_id' => $rol_id
            ]);
            header("Location: ../../frontend/user/login-user.php?registro=" . urlencode("Registro completado. ¡Ya puedes iniciar sesión!"));
            exit;
        }
    }
}
