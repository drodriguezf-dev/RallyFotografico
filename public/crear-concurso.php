<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");

// Solo administradores pueden acceder
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validación de fechas
    $ahora = date('Y-m-d H:i:s');

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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <h1 class="text-3xl font-bold mb-6 text-center">Crear Nuevo Concurso</h1>

    <?php if ($mensaje): ?>
        <div class="mb-4 text-center text-white font-semibold p-3 rounded
                    <?= str_contains($mensaje, 'Error') ? 'bg-red-500' : 'bg-green-500' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="max-w-3xl mx-auto bg-white shadow-md rounded p-6 space-y-4">
        <div>
            <label class="block font-medium">Título:</label>
            <input type="text" name="titulo" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
        </div>

        <div>
            <label class="block font-medium">Descripción:</label>
            <textarea name="descripcion" rows="3" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300 resize-none"></textarea>
        </div>

        <div>
            <label class="block font-medium">Reglas:</label>
            <textarea name="reglas" rows="4" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300 resize-none"></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Fecha de inicio:</label>
                <input type="datetime-local" name="fecha_inicio" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
            <div>
                <label class="block font-medium">Fecha de fin:</label>
                <input type="datetime-local" name="fecha_fin" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Inicio votación:</label>
                <input type="datetime-local" name="fecha_inicio_votacion" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
            <div>
                <label class="block font-medium">Fin votación:</label>
                <input type="datetime-local" name="fecha_fin_votacion" required class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Máx. fotos por usuario:</label>
                <input type="number" name="max_fotos_por_usuario" value="3" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
            <div>
                <label class="block font-medium">Máx. votos por IP:</label>
                <input type="number" name="max_votos_por_ip" value="2" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Máx. participantes:</label>
                <input type="number" name="max_participantes" value="100" min="1" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
            <div>
                <label class="block font-medium">Tamaño máx. foto (MB):</label>
                <select name="tamano_maximo_bytes" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <option value="1048576">1 MB</option>
                    <option value="2097152" selected>2 MB</option>
                    <option value="5242880">5 MB</option>
                    <option value="10485760">10 MB</option>
                    <option value="20971520">20 MB</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-medium">Formatos aceptados (separados por coma):</label>
            <input type="text" name="formatos_aceptados" value="image/jpeg,image/png" class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-green-300">
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                Crear Concurso
            </button>
        </div>
    </form>

    <div class="text-center mt-6">
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
            Volver al inicio
        </a>
    </div>

</body>

</html>