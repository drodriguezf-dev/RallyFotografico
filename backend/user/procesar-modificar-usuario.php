<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$usuario_id = $_SESSION['usuario_id'];

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($nombre) || empty($apellidos) || empty($email)) {
    header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("Por favor, rellena todos los campos obligatorios.") . "&tipo=error");
    exit;
}

try {
    // Obtener datos actuales del usuario
    $stmt = $conexion->prepare("SELECT nombre, apellidos, email FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioActual) {
        throw new Exception("Usuario no encontrado.");
    }

    // Verificar si ha cambiado algún campo (excepto password)
    $sinCambios = 
        $usuarioActual['nombre'] === $nombre &&
        $usuarioActual['apellidos'] === $apellidos &&
        $usuarioActual['email'] === $email &&
        empty($password);

    if ($sinCambios) {
        header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("No se realizaron cambios en los datos.") . "&tipo=info");
        exit;
    }

    // Verificar que el email no esté en uso por otro usuario o admin
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $email, 'id' => $usuario_id]);
    $existeEnUsuarios = $stmt->fetch();

    $stmt = $conexion->prepare("SELECT id FROM admins WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existeEnAdmins = $stmt->fetch();

    if ($existeEnUsuarios || $existeEnAdmins) {
        header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("El email ya está en uso por otro usuario o administrador.") . "&tipo=error");
        exit;
    }

    // Ejecutar la actualización
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email, password = :password WHERE id = :id");
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => $hash,
            'id' => $usuario_id
        ]);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email WHERE id = :id");
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'id' => $usuario_id
        ]);
    }

    $_SESSION['email'] = $email;

    header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("Datos actualizados correctamente.") . "&tipo=exito");
    exit;
} catch (Exception $e) {
    header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("Error al actualizar: " . $e->getMessage()) . "&tipo=error");
    exit;
}
