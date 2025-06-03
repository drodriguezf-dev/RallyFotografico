<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT id, rol_id, password FROM usuarios WHERE email = :email";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            header("Location: ../../frontend/index.php");
            exit;
        } else {
            header("Location: ../../frontend/user/login-user.php?error=" . urlencode("Credenciales inválidas."));
            exit;        }
    }
}

?>
