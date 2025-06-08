<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../../frontend/index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../frontend/index.php");
    exit;
}

$concurso_id = $_GET['id'];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $reglas = trim($_POST['reglas'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $fecha_inicio_votacion = $_POST['fecha_inicio_votacion'] ?? '';
    $fecha_fin_votacion = $_POST['fecha_fin_votacion'] ?? '';
    $max_fotos_por_usuario = $_POST['max_fotos_por_usuario'] ?? 0;
    $max_votos_por_ip = $_POST['max_votos_por_ip'] ?? 0;
    $max_participantes = $_POST['max_participantes'] ?? 0;
    $tamano_maximo_bytes = $_POST['tamano_maximo_bytes'] ?? 0;
    $formatos_aceptados = $_POST['formatos_aceptados'] ?? [];

    // Validar campos obligatorios
    if (!$titulo) $errors[] = "El título es obligatorio.";
    if (!$descripcion) $errors[] = "La descripción es obligatoria.";
    if (!$reglas) $errors[] = "Las reglas son obligatorias.";
    if (!$fecha_inicio) $errors[] = "La fecha de inicio es obligatoria.";
    if (!$fecha_fin) $errors[] = "La fecha de fin es obligatoria.";
    if (!$fecha_inicio_votacion) $errors[] = "La fecha de inicio de votación es obligatoria.";
    if (!$fecha_fin_votacion) $errors[] = "La fecha de fin de votación es obligatoria.";
    if (empty($formatos_aceptados)) $errors[] = "Debes seleccionar al menos un formato aceptado.";

    // Convertir fechas a objetos DateTime para comparación si no hay errores previos
    if (empty($errors)) {
        try {
            $fecha_inicio_dt = new DateTime($fecha_inicio);
            $fecha_fin_dt = new DateTime($fecha_fin);
            $fecha_inicio_votacion_dt = new DateTime($fecha_inicio_votacion);
            $fecha_fin_votacion_dt = new DateTime($fecha_fin_votacion);
            $ahora = new DateTime();

            // La fecha de fin no puede ser anterior a la de inicio
            if ($fecha_fin_dt < $fecha_inicio_dt) {
                $errors[] = "La fecha de fin no puede ser anterior a la fecha de inicio.";
            }

            // El concurso no puede estar ya terminado
            if ($fecha_fin_dt <= $ahora) {
                $errors[] = "No se puede crear un concurso que ya haya terminado.";
            }

            // La fecha de fin de votación no puede ser anterior a la fecha de inicio de votación
            if ($fecha_fin_votacion_dt < $fecha_inicio_votacion_dt) {
                $errors[] = "La fecha de fin de votación no puede ser anterior a la fecha de inicio de votación.";
            }

            // La fecha de inicio de votación no puede ser anterior a la fecha de inicio del concurso
            if ($fecha_inicio_votacion_dt < $fecha_inicio_dt) {
                $errors[] = "La fecha de inicio de votación no puede ser anterior a la fecha de inicio del concurso.";
            }
        } catch (Exception $e) {
            $errors[] = "Error en las fechas: " . $e->getMessage();
        }
    }

    // Si hay errores, redirigir con mensaje
    if (!empty($errors)) {
        $mensaje = implode(' ', $errors);
        header("Location: ../../frontend/concurso/modificar-concurso.php?id=$concurso_id&mensaje=" . urlencode($mensaje));
        exit;
    }

    // Convertir array de formatos en cadena CSV
    $formatos_aceptados_str = implode(',', $formatos_aceptados);

    if ($titulo && $descripcion && $reglas && $fecha_inicio && $fecha_fin && $fecha_inicio_votacion && $fecha_fin_votacion) {
        $conexion = conectarPDO($host, $user, $password, $bbdd);

        // Variables para imagen
        $imagenPortadaBase64 = null;
        $imagenPortadaMime = null;

        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['imagen_portada'];

            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($archivo['type'], $tiposPermitidos)) {
                if ($archivo['size'] <= 20971520) {
                    $contenido = file_get_contents($archivo['tmp_name']);
                    $imagenPortadaBase64 = base64_encode($contenido);
                    $imagenPortadaMime = $archivo['type'];
                } else {
                    $mensaje = "La imagen es demasiado grande. Máximo 20MB.";
                }
            } else {
                $mensaje = "Formato de imagen no permitido.";
            }
        }

        try {
            if ($imagenPortadaBase64 && $imagenPortadaMime) {
                $stmt = $conexion->prepare("
                    UPDATE concursos 
                    SET titulo = :titulo, descripcion = :descripcion, reglas = :reglas,
                        fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin,
                        fecha_inicio_votacion = :fecha_inicio_votacion, fecha_fin_votacion = :fecha_fin_votacion,
                        max_fotos_por_usuario = :max_fotos_por_usuario,
                        max_votos_por_ip = :max_votos_por_ip,
                        max_participantes = :max_participantes,
                        tamano_maximo_bytes = :tamano_maximo_bytes,
                        formatos_aceptados = :formatos_aceptados,
                        imagen_portada_base64 = :imagen_base64, imagen_portada_mime = :imagen_mime
                    WHERE id = :id
                ");
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'reglas' => $reglas,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'fecha_inicio_votacion' => $fecha_inicio_votacion,
                    'fecha_fin_votacion' => $fecha_fin_votacion,
                    'max_fotos_por_usuario' => $max_fotos_por_usuario,
                    'max_votos_por_ip' => $max_votos_por_ip,
                    'max_participantes' => $max_participantes,
                    'tamano_maximo_bytes' => $tamano_maximo_bytes,
                    'formatos_aceptados' => $formatos_aceptados_str,
                    'imagen_base64' => $imagenPortadaBase64,
                    'imagen_mime' => $imagenPortadaMime,
                    'id' => $concurso_id
                ]);
            } else {
                $stmt = $conexion->prepare("
                    UPDATE concursos 
                    SET titulo = :titulo, descripcion = :descripcion, reglas = :reglas,
                        fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin,
                        fecha_inicio_votacion = :fecha_inicio_votacion, fecha_fin_votacion = :fecha_fin_votacion,
                        max_fotos_por_usuario = :max_fotos_por_usuario,
                        max_votos_por_ip = :max_votos_por_ip,
                        max_participantes = :max_participantes,
                        tamano_maximo_bytes = :tamano_maximo_bytes,
                        formatos_aceptados = :formatos_aceptados
                    WHERE id = :id
                ");
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'reglas' => $reglas,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'fecha_inicio_votacion' => $fecha_inicio_votacion,
                    'fecha_fin_votacion' => $fecha_fin_votacion,
                    'max_fotos_por_usuario' => $max_fotos_por_usuario,
                    'max_votos_por_ip' => $max_votos_por_ip,
                    'max_participantes' => $max_participantes,
                    'tamano_maximo_bytes' => $tamano_maximo_bytes,
                    'formatos_aceptados' => $formatos_aceptados_str,
                    'id' => $concurso_id
                ]);
            }
            if (!$mensaje) {
                $mensaje = "Concurso actualizado correctamente.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }

    header("Location: ../../frontend/concurso/modificar-concurso.php?id=$concurso_id&mensaje=" . urlencode($mensaje));
    exit;
}
