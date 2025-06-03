<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

// Solo administradores pueden acceder
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarPDO($host, $user, $password, $bbdd);

    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $reglas = trim($_POST['reglas']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $fecha_inicio_votacion = $_POST['fecha_inicio_votacion'];
    $fecha_fin_votacion = $_POST['fecha_fin_votacion'];
    $max_fotos = (int)$_POST['max_fotos_por_usuario'];
    $max_votos = (int)$_POST['max_votos_por_ip'];
    $max_participantes = (int)$_POST['max_participantes'];
    $tamano_maximo = (int)$_POST['tamano_maximo_bytes'];
    $formatos = trim($_POST['formatos_aceptados']);

    $ahora = date('Y-m-d H:i:s');

    // Validación de fechas
    if (
        $fecha_inicio < $ahora ||
        $fecha_fin < $fecha_inicio ||
        $fecha_inicio_votacion < $fecha_inicio ||
        $fecha_fin_votacion < $fecha_inicio_votacion ||
        $fecha_fin_votacion > $fecha_fin
    ) {
        $mensaje = "Error: Las fechas introducidas no son válidas.";
    } else {
        try {
            // Verificar si ya existe un concurso con el mismo título
            $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM concursos WHERE titulo = :titulo");
            $stmt_check->execute(['titulo' => $titulo]);
            $existe = $stmt_check->fetchColumn();

            if ($existe > 0) {
                $mensaje = "Error: Ya existe un concurso con ese título.";
            } else {
                $sql = "INSERT INTO concursos (
                            titulo, descripcion, reglas, fecha_inicio, fecha_fin,
                            max_fotos_por_usuario, max_votos_por_ip, max_participantes,
                            tamano_maximo_bytes, formatos_aceptados,
                            fecha_inicio_votacion, fecha_fin_votacion
                        )
                        VALUES (
                            :titulo, :descripcion, :reglas, :fecha_inicio, :fecha_fin,
                            :max_fotos, :max_votos, :max_participantes,
                            :tamano_maximo, :formatos,
                            :inicio_votacion, :fin_votacion
                        )";

                $stmt = $conexion->prepare($sql);
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'reglas' => $reglas,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'max_fotos' => $max_fotos,
                    'max_votos' => $max_votos,
                    'max_participantes' => $max_participantes,
                    'tamano_maximo' => $tamano_maximo,
                    'formatos' => $formatos,
                    'inicio_votacion' => $fecha_inicio_votacion,
                    'fin_votacion' => $fecha_fin_votacion
                ]);

                $mensaje = "Concurso creado correctamente.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear el concurso: " . $e->getMessage();
        }
    }
}

// Devuelve JSON con el mensaje para el frontend
header('Content-Type: application/json');
echo json_encode(['mensaje' => $mensaje]);
exit;
