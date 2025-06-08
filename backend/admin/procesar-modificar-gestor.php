<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$rol_id = $_SESSION['rol_id'] ?? null;
$gestor_id = $_POST['id'];

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($nombre) || empty($apellidos)) {
    if ($rol_id == 1) {
        header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("Por favor, rellena todos los campos obligatorios.") . "&tipo=error");
        exit;
    } else {
        header("Location: ../../frontend/admin/modificar-gestor.php?mensaje=" . urlencode("Por favor, rellena todos los campos obligatorios.") . "&tipo=error");
        exit;
    }
}

try {
    // Obtener datos actuales del gestor
    $stmt = $conexion->prepare("SELECT nombre, apellidos FROM admins WHERE id = :id");
    $stmt->execute(['id' => $gestor_id]);
    $gestorActual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gestorActual) {
        throw new Exception("Gestor no encontrado.");
    }

    // Verificar si ha cambiado algún campo (excepto password)
    $sinCambios =
        $gestorActual['nombre'] === $nombre &&
        $gestorActual['apellidos'] === $apellidos &&
        empty($password);

    if ($sinCambios) {
        if ($rol_id == 1) {
            header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("No se realizaron cambios en los datos.") . "&tipo=info");
            exit;
        } else {
            header("Location: ../../frontend/admin/modificar-gestor.php?mensaje=" . urlencode("No se realizaron cambios en los datos.") . "&tipo=info");
            exit;
        }
    }

    // Ejecutar la actualización
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE admins SET nombre = :nombre, apellidos = :apellidos, password = :password WHERE id = :id");
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'password' => $hash,
            'id' => $gestor_id
        ]);
    } else {
        $stmt = $conexion->prepare("UPDATE admins SET nombre = :nombre, apellidos = :apellidos WHERE id = :id");
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'id' => $gestor_id
        ]);
    }

    if ($rol_id == 1) {
        header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("Datos actualizados correctamente.") . "&tipo=exito");
        exit;
    } else {
        header("Location: ../../frontend/admin/modificar-gestor.php?mensaje=" . urlencode("Datos actualizados correctamente.") . "&tipo=exito");
        exit;
    }
} catch (Exception $e) {
    if ($rol_id == 1) {
        header("Location: ../../frontend/admin/gestion-usuarios.php?mensaje=" . urlencode("Error al actualizar: " . $e->getMessage()) . "&tipo=error");
        exit;
    } else {
        header("Location: ../../frontend/admin/modificar-gestor.php?mensaje=" . urlencode("Error al actualizar: " . $e->getMessage()) . "&tipo=error");
        exit;
    }
}
