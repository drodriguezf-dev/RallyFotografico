<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

// Procesar acciones del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foto_id = $_POST['foto_id'] ?? null;
    $concurso_id = $_POST['concurso_id'] ?? null;

    if ($foto_id && is_numeric($foto_id)) {
        if (isset($_POST['aceptar'])) {
            $stmt = $conexion->prepare("UPDATE fotografias SET estado = 'admitida' WHERE id = :id");
            $stmt->execute(['id' => $foto_id]);
        } elseif (isset($_POST['eliminar'])) {
            $stmt = $conexion->prepare("DELETE FROM fotografias WHERE id = :id");
            $stmt->execute(['id' => $foto_id]);
        } elseif (isset($_POST['rechazar'])) {
            $stmt = $conexion->prepare("UPDATE fotografias SET estado = 'rechazada' WHERE id = :id");
            $stmt->execute(['id' => $foto_id]);
        }
    }

    if ($concurso_id && is_numeric($concurso_id) && isset($_POST['eliminar_concurso'])) {
        $stmt = $conexion->prepare("SELECT fecha_fin FROM concursos WHERE id = :id");
        $stmt->execute(['id' => $concurso_id]);

        $stmt = $conexion->prepare("DELETE FROM fotografias WHERE concurso_id = :id");
        $stmt->execute(['id' => $concurso_id]);

        $stmt = $conexion->prepare("DELETE FROM concursos WHERE id = :id");
        $stmt->execute(['id' => $concurso_id]);
    }

    // Volver al panel tras la acción
    $return_url = $_POST['return_url'] ?? '../../frontend/admin/gestionar-fotos.php';
    header("Location: $return_url");

    exit;
}
