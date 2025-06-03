<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT id, rol_id, password FROM admins WHERE email = :email";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['rol_id'] = $admin['rol_id'];
            header("Location: ../public/index.php");
            exit;
        } else {
            header("Location: login-admin.php?error=Correo+o+contraseña+incorrectos.");
            exit;
        }
    } else {
        header("Location: login-admin.php?error=Rellena+todos+los+campos.");
        exit;
    }
} else {
    header("Location: login-admin.php");
    exit;
}
