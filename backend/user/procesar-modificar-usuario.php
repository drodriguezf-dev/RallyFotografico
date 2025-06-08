<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$rol_id = $_SESSION['rol_id'] ?? null; // Obtén el rol del usuario desde sesión
$usuario_id_sesion = $_SESSION['usuario_id'] ?? null; // Usuario ID de la sesión

// Por defecto, el usuario_id es el de la sesión
$usuario_id = $usuario_id_sesion;

// Si es admin, usa el id que viene por GET, si es válido
if ($rol_id == 1) {
    if (isset($_POST['id']) && ctype_digit($_POST['id'])) {
        $usuario_id = $_POST['id'];
    }
}
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
        if ($rol_id == 1) {
            // Si es admin, redirigir a la gestión de usuarios
            header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("No se realizaron cambios en los datos.") . "&tipo=error");
            exit;
        } else {
            // Si es usuario normal, redirigir a su perfil
            header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("No se realizaron cambios en los datos.") . "&tipo=info");
            exit;        }
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

    if ($rol_id == 1) {
        // Si es admin, redirigir a la gestión de usuarios
        header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("Datos actualizados correctamente.") . "&tipo=exito");
    } else {
        // Si es usuario normal, redirigir a su perfil
        header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("Datos actualizados correctamente.") . "&tipo=exito");
    }
} catch (Exception $e) {
    if ($rol_id == 1) {
        // Si es admin, redirigir a la gestión de usuarios
        header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("Error al actualizar: " . $e->getMessage()) . "&tipo=error");
    } else {
        // Si es usuario normal, redirigir a su perfil
        header("Location: ../../frontend/user/modificar-usuario.php?mensaje=" . urlencode("Error al actualizar: " . $e->getMessage()) . "&tipo=error");
    }
}

