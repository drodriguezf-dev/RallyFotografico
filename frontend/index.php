<?php
session_start();
require_once("../utils/variables.php");
require_once("../utils/funciones.php");
// Conectar a la BBDD

$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener fecha actual
$fecha_actual = date('Y-m-d H:i:s');

// Consulta concursos activos
$sql = "SELECT 
    id, 
    titulo, 
    descripcion, 
    fecha_inicio, 
    fecha_fin, 
    fecha_inicio_votacion, 
    fecha_fin_votacion,
    imagen_portada_base64,
    imagen_portada_mime
FROM 
    concursos
ORDER BY 
    fecha_inicio DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$concursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Concursos - Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen flex flex-col items-center">

    <!-- Contenedor central -->
    <div class="bg-gray-100 min-h-screen w-full flex flex-col">

        <!-- Barra de navegación -->
        <nav class="bg-orange-400 shadow p-4 flex items-center justify-between w-full">
            <div class="w-1/3"></div>

            <div class="w-1/3 text-center">
                <span class="text-3xl font-bold text-white">Plataforma Fotográfica</span>
            </div>

            <div class="w-1/3 flex justify-end relative">
                <?php if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['admin_id'])): ?>
                    <!-- Mostrar botón de iniciar sesión si no hay usuario ni administrador logueado -->
                    <a href="user/login-user.php" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded transition">
                        Iniciar sesión
                    </a>
                <?php else: ?>
                    <!-- Mostrar menú de usuario o administrador -->
                    <button id="user-menu-btn" class="flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded transition focus:outline-none mr-6">
                        <span class="mr-2">Panel de usuario</span>
                        <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </button>

                    <div id="user-menu" class="absolute right-0 mt-2 w-56 bg-gray-50 border border-gray-200 rounded-lg shadow-lg hidden z-50">
                        <div class="px-4 py-2 text-sm text-gray-700 font-semibold border-b bg-gray-100">Opciones de cuenta</div>
                        <!-- Opciones para usuarios -->
                        <?php if ($_SESSION['rol_id'] == 3): ?>
                            <a href="user/ver-fotos.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Ver mis fotos
                            </a>
                            <a href="user/modificar-usuario.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Cambiar mis datos
                            </a>
                        <?php elseif ($_SESSION['rol_id'] == 2): ?>
                            <a href="admin/gestionar-fotos.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Gestionar fotografías
                            </a>
                            <a href="concurso/crear-concurso.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Crear concurso
                            </a>
                            <a href="admin/modificar-gestor.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Cambiar mis datos
                            </a>
                        <?php elseif ($_SESSION['rol_id'] == 1): ?>
                            <!-- Opciones para administradores -->
                            <a href="admin/gestionar-fotos.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Gestionar fotografías
                            </a>
                            <a href="concurso/crear-concurso.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Crear concurso
                            </a>
                            <a href="admin/gestion-usuarios.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded transition">
                                Gestionar usuarios
                            </a>
                        <?php endif; ?>
                        <a href="../backend/procesar-logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-100 rounded transition">
                            Cerrar sesión
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="flex-grow px-4 py-8">
            <h1 class="text-3xl font-semibold mb-6 text-center text-gray-800">Concursos Activos</h1>

            <?php if (count($concursos) === 0): ?>
                <p class="text-center text-gray-600">No hay concursos activos en este momento.</p>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($concursos as $concurso): ?>
                        <?php
                        $fecha_actual_dt = new DateTime($fecha_actual);
                        $fecha_inicio_participacion = new DateTime($concurso['fecha_inicio']);
                        $fecha_fin_participacion = new DateTime($concurso['fecha_fin']);
                        $fecha_inicio_votacion = new DateTime($concurso['fecha_inicio_votacion']);
                        $fecha_fin_votacion = new DateTime($concurso['fecha_fin_votacion']);
                        $fecha_fin_votacion_mas_3 = clone $fecha_fin_votacion;
                        $fecha_fin_votacion_mas_3->modify('+3 days');

                        $mostrar_concurso = false;

                        if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 3) {
                            // Participantes: solo mientras está abierta la participación
                            $mostrar_concurso = $fecha_actual_dt <= $fecha_fin_participacion;
                        } elseif (!isset($_SESSION['rol_id']) && !isset($_SESSION['usuario_id'])) {
                            // Anónimos: hasta 3 días después del fin de votación
                            $mostrar_concurso = $fecha_actual_dt <= $fecha_fin_votacion_mas_3;
                        } else {
                            // Admins o gestores: siempre
                            $mostrar_concurso = true;
                        }

                        if (!$mostrar_concurso) continue;
                        ?>

                        <div class="bg-white p-6 rounded shadow hover:shadow-lg transition">
                            <h2 class="text-xl font-bold mb-2 text-gray-900 text-center"><?= htmlspecialchars($concurso['titulo']) ?></h2>
                            <?php if (!empty($concurso['imagen_portada_base64']) && !empty($concurso['imagen_portada_mime'])): ?>
                                <div class="flex-shrink-0 h-48 w-full mb-4 overflow-hidden flex items-center justify-center bg-white rounded">
                                    <img src="data:<?= htmlspecialchars($concurso['imagen_portada_mime']) ?>;base64,<?= $concurso['imagen_portada_base64'] ?>"
                                        alt="Imagen de portada de <?= htmlspecialchars($concurso['titulo']) ?>"
                                        class="max-h-full max-w-full object-contain">
                                </div>

                            <?php endif; ?>
                            <p class="text-gray-700 text-center"><?= nl2br(htmlspecialchars($concurso['descripcion'])) ?></p>

                            <div class="mt-4 text-center flex justify-center space-x-4">
                                <?php if (!isset($_SESSION['rol_id']) && !isset($_SESSION['usuario_id'])): ?>
                                    <?php if ($fecha_actual_dt >= $fecha_inicio_votacion && $fecha_actual_dt <= $fecha_fin_votacion): ?>
                                        <a href="concurso/votar-concurso.php?id=<?= $concurso['id'] ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded transition">
                                            Votar
                                        </a>
                                    <?php endif; ?>
                                <?php elseif (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 3): ?>
                                    <?php if ($fecha_actual_dt >= $fecha_inicio_participacion && $fecha_actual_dt <= $fecha_fin_participacion): ?>
                                        <a href="concurso/participar-concurso.php?id=<?= $concurso['id'] ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded transition">
                                            Participar
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 2)): ?>
                                    <a href="concurso/modificar-concurso.php?id=<?= $concurso['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition">
                                        Modificar
                                    </a>
                                    <a href="admin/gestionar-concurso.php?id=<?= $concurso['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition">
                                        Ver fotos
                                    </a>
                                <?php endif; ?>

                                <!-- Todos ven el botón de ranking si están dentro del rango de visibilidad -->
                                <?php if ($fecha_actual_dt >= $fecha_inicio_votacion): ?>
                                <a href="concurso/ver-ranking.php?id=<?= $concurso['id'] ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded transition">
                                    Ranking
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer class="bg-orange-400 text-center p-4 text-orange-900 text-sm">
            &copy; <?= date('Y') ?> Plataforma Fotográfica - David Rodríguez Ferrete
        </footer>
    </div>

</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('user-menu-btn');
        const menu = document.getElementById('user-menu');

        btn?.addEventListener('click', function() {
            menu.classList.toggle('hidden');
        });

        // Cierra el menú si haces clic fuera
        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    });
</script>

</html>