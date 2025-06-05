<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['foto_id'], $_POST['concurso_id'])) {
    header("Location: ../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

$concurso_id = $_POST['concurso_id'];
$foto_id = $_POST['foto_id'];
$ip = $_SERVER['REMOTE_ADDR'];

// Verificar concurso válido
$stmt = $conexion->prepare("SELECT max_votos_por_ip FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concurso) {
    header("Location: ../../frontend/concurso/votar-concurso.php?id=$concurso_id&error=Concurso+no+encontrado");
    exit;
}

$limite = $concurso['max_votos_por_ip'];

$stmt = $conexion->prepare("SELECT COUNT(*) FROM votos WHERE concurso_id = :cid AND ip_votante = :ip");
$stmt->execute(['cid' => $concurso_id, 'ip' => $ip]);
$realizados = $stmt->fetchColumn();

if ($realizados >= $limite) {
    header("Location: ../../frontend/concurso/votar-concurso.php?id=$concurso_id&error=Has+alcanzado+el+límite+de+votos");
    exit;
}

// Validar foto
$stmt = $conexion->prepare("SELECT id FROM fotografias WHERE id = :fid AND concurso_id = :cid AND estado = 'admitida'");
$stmt->execute(['fid' => $foto_id, 'cid' => $concurso_id]);

if (!$stmt->fetch()) {
    header("Location: ../../frontend/concurso/votar-concurso.php?id=$concurso_id&error=Foto+no+válida");
    exit;
}

// Registrar voto
$stmt = $conexion->prepare("INSERT INTO votos (concurso_id, fotografia_id, ip_votante) VALUES (:cid, :fid, :ip)");
$stmt->execute([
    'cid' => $concurso_id,
    'fid' => $foto_id,
    'ip' => $ip
]);

header("Location: ../../frontend/concurso/votar-concurso.php?id=$concurso_id&voto=ok");
exit;
?>