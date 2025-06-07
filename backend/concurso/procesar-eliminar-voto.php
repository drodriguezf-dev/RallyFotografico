<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../frontend/index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

$foto_id = $_POST['foto_id'] ?? null;
$concurso_id = $_POST['concurso_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'];

if (!$foto_id || !$concurso_id || !is_numeric($foto_id) || !is_numeric($concurso_id)) {
    header("Location: ../../frontend/index.php");
    exit;
}

$stmt = $conexion->prepare("DELETE FROM votos WHERE concurso_id = :cid AND fotografia_id = :fid AND ip_votante = :ip");
$stmt->execute([
    'cid' => $concurso_id,
    'fid' => $foto_id,
    'ip'  => $ip
]);

header("Location: ../../frontend/concurso/votar-concurso.php?id=$concurso_id");
exit;