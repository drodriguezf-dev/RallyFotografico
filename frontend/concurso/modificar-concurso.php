<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$concurso_id = $_GET['id'];
$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener concurso actual
$stmt = $conexion->prepare("SELECT * FROM concursos WHERE id = :id");
$stmt->execute(['id' => $concurso_id]);
$concurso = $stmt->fetch(PDO::FETCH_ASSOC);

$mensaje = $_GET['mensaje'] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar Concurso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-3xl bg-white shadow-2xl rounded-2xl p-10">
        <h1 class="text-3xl font-bold mb-8 text-orange-600">Modificar Concurso</h1>

        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-md text-sm font-semibold 
                        <?= str_contains($mensaje, 'error') ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($concurso): ?>
            <form method="post" action="../../backend/concurso/procesar-modificar-concurso.php?id=<?= $concurso_id ?>" class="grid grid-cols-1 md:grid-cols-2 gap-6" enctype="multipart/form-data">

                <!-- Campo genérico reutilizable -->
                <?php function campo($etiqueta, $nombre, $valor, $tipo = "text", $extra = '')
                { ?>
                    <div class="<?= $extra ?>">
                        <label class="block text-sm font-semibold text-gray-800 mb-1"><?= $etiqueta ?></label>
                        <input type="<?= $tipo ?>" name="<?= $nombre ?>" value="<?= htmlspecialchars($valor) ?>"
                            class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200" required>
                    </div>
                <?php } ?>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Título</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($concurso['titulo']) ?>"
                        class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1" for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="5"
                        class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200"><?= htmlspecialchars($concurso['descripcion']) ?></textarea>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1" for="reglas">Reglas</label>
                    <textarea name="reglas" id="reglas" rows="5"
                        class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200"><?= htmlspecialchars($concurso['reglas']) ?></textarea>
                </div>

                <?php
                campo("Fecha de inicio", "fecha_inicio", date('Y-m-d\TH:i', strtotime($concurso['fecha_inicio'])), "datetime-local");
                campo("Fecha de fin", "fecha_fin", date('Y-m-d\TH:i', strtotime($concurso['fecha_fin'])), "datetime-local");
                campo("Inicio votación", "fecha_inicio_votacion", date('Y-m-d\TH:i', strtotime($concurso['fecha_inicio_votacion'])), "datetime-local");
                campo("Fin votación", "fecha_fin_votacion", date('Y-m-d\TH:i', strtotime($concurso['fecha_fin_votacion'])), "datetime-local");

                campo("Máx. fotos por usuario", "max_fotos_por_usuario", $concurso['max_fotos_por_usuario'], "number");
                campo("Máx. votos por IP", "max_votos_por_ip", $concurso['max_votos_por_ip'], "number");
                campo("Máx. participantes", "max_participantes", $concurso['max_participantes'], "number");
                ?>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Tamaño máx. foto (MB)</label>
                    <select name="tamano_maximo_bytes"
                        class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200">
                        <option value="1048576" <?= $concurso['tamano_maximo_bytes'] == 1048576 ? 'selected' : '' ?>>1 MB</option>
                        <option value="2097152" <?= $concurso['tamano_maximo_bytes'] == 2097152 ? 'selected' : '' ?>>2 MB</option>
                        <option value="5242880" <?= $concurso['tamano_maximo_bytes'] == 5242880 ? 'selected' : '' ?>>5 MB</option>
                        <option value="10485760" <?= $concurso['tamano_maximo_bytes'] == 10485760 ? 'selected' : '' ?>>10 MB</option>
                        <option value="20971520" <?= $concurso['tamano_maximo_bytes'] == 20971520 ? 'selected' : '' ?>>20 MB</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1" for="imagen_portada">Imagen de portada (opcional)</label>
                    <input type="file" name="imagen_portada" id="imagen_portada" accept="image/*"
                        class="w-full px-4 py-2 bg-gray-50 shadow-inner rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all duration-200">
                    <?php if (!empty($concurso['imagen_portada_base64']) && !empty($concurso['imagen_portada_mime'])): ?>
                        <div class="mt-4 flex-shrink-0 h-48 w-full overflow-hidden flex items-center justify-center bg-white rounded">
                            <img src="data:<?= htmlspecialchars($concurso['imagen_portada_mime']) ?>;base64,<?= $concurso['imagen_portada_base64'] ?>"
                                alt="Imagen actual de portada"
                                class="max-h-full max-w-full object-contain">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Formatos aceptados</label>
                    <div class="grid grid-cols-2 gap-2 px-2 py-2 bg-gray-50 shadow-inner rounded-xl">
                        <?php
                        $formatos_actuales = explode(',', $concurso['formatos_aceptados']);
                        $todos_los_formatos = [
                            'image/jpeg' => 'JPEG (.jpg)',
                            'image/png'  => 'PNG (.png)',
                            'image/webp' => 'WebP (.webp)'                        ];
                        foreach ($todos_los_formatos as $valor => $texto):
                            $checked = in_array($valor, $formatos_actuales) ? 'checked' : '';
                        ?>
                            <label class="flex items-center space-x-2 text-sm text-gray-700">
                                <input type="checkbox" name="formatos_aceptados[]" value="<?= $valor ?>" <?= $checked ?>
                                    class="text-orange-500 focus:ring-orange-400 rounded">
                                <span><?= $texto ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Botones -->
                <div class="col-span-2 flex justify-between items-center mt-6">
                    <a href="../index.php" class="text-gray-500 hover:text-orange-600 transition-all">← Volver</a>
                    <button type="submit"
                        class="bg-orange-500 text-white font-semibold px-6 py-2 rounded-xl hover:bg-orange-600 transition-all shadow-md">
                        Guardar cambios
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
</body>

</html>