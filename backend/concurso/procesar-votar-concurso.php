<?php
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

$conexion = conectarPDO($host, $user, $password, $bbdd);

$voto_registrado = false;
$mensaje_error = '';
$limite_votos = 0;
$votos_realizados = 0;
$fotos = [];
$concurso = [];

// Validar parámetro id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$concurso_id = $_GET['id'];

// Obtener datos del concurso
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    $mensaje_error = "Concurso no encontrado.";
    return;
}

$limite_votos = $concurso['max_votos_por_ip'];
$ip_usuario = $_SERVER['REMOTE_ADDR'];

// Contar votos por IP
$stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
$stmt->execute(['cid' => $concurso_id, 'ip' => $ip_usuario]);
$votos_realizados = $stmt->fetchColumn();

// Procesar voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foto_id'])) {
    if ($votos_realizados < $limite_votos) {
        $foto_id = $_POST['foto_id'];

        // Verificar que la foto pertenece al concurso y está admitida
        $stmt = $conexion->prepare("SELECT id FROM fotografias WHERE id = :fid AND concurso_id = :cid AND estado = 'admitida'");
        $stmt->execute(['fid' => $foto_id, 'cid' => $concurso_id]);

        if ($stmt->fetch()) {
            $stmt = $conexion->prepare("INSERT INTO votos (concurso_id, fotografia_id, ip_votante) VALUES (:cid, :fid, :ip)");
            $stmt->execute([
                'cid' => $concurso_id,
                'fid' => $foto_id,
                'ip' => $ip_usuario
            ]);
            $voto_registrado = true;

            // Actualizar recuento
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
            $stmt->execute(['cid' => $concurso_id, 'ip' => $ip_usuario]);
            $votos_realizados = $stmt->fetchColumn();
        } else {
            $mensaje_error = "Foto no válida.";
        }
    } else {
        $mensaje_error = "Has alcanzado el límite de votos permitidos para este concurso.";
    }
}

// Obtener fotos admitidas
$stmt = $conexion->prepare("SELECT * FROM fotografias WHERE concurso_id = :cid AND estado = 'admitida'");
$stmt->execute(['cid' => $concurso_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
