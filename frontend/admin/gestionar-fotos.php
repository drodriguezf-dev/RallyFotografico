<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] == 3) {
    header("Location: ../login.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

// Obtener concursos
$stmt = $conexion->prepare("SELECT id, titulo, fecha_fin FROM concursos");
$stmt->execute();
$concursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener fotos con datos de concurso
$stmt = $conexion->prepare("
    SELECT f.id, f.titulo AS titulo_foto, f.descripcion, f.estado, f.imagen_base64, f.mime_type,
           c.id AS concurso_id, c.titulo AS titulo_concurso
    FROM fotografias f 
    JOIN concursos c ON f.concurso_id = c.id
    ORDER BY f.id DESC
");
$stmt->execute();
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por concurso
$fotos_por_concurso = [];
foreach ($fotos as $foto) {
    $fotos_por_concurso[$foto['concurso_id']][] = $foto;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administrar Fotos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="mt-6 mb-12 text-center">
        <a href="../index.php" class="inline-block bg-gray-800 text-white px-5 py-2 rounded hover:bg-gray-900 transition">
            Volver al índice
        </a>
    </div>
    <div class="space-y-10">
        <?php foreach ($concursos as $concurso): ?>
            <?php
            $id_concurso = $concurso['id'];
            $titulo_concurso = $concurso['titulo'];
            $fecha_fin = $concurso['fecha_fin'];
            $es_pasado = strtotime($fecha_fin) < time();
            $fotos_concurso = $fotos_por_concurso[$id_concurso] ?? [];
            ?>
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold border-b pb-1 <?= $es_pasado ? 'text-red-600' : '' ?>">
                        <?= htmlspecialchars($titulo_concurso) ?>
                        <?php if ($es_pasado): ?>
                            <span class="text-sm font-normal">(Finalizado)</span>
                        <?php endif; ?>
                    </h2>
                    <div class="flex items-center gap-3">
                        <a href="gestionar-concurso.php?id=<?= $id_concurso ?>" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">
                            Gestionar concurso
                        </a>
                        <form method="post" action="../../backend/admin/procesar-gestionar-fotos.php" class="eliminar-concurso-form" data-titulo="<?= htmlspecialchars($titulo_concurso) ?>">
                            <input type="hidden" name="concurso_id" value="<?= $id_concurso ?>">
                            <button type="button" class="btn-eliminar-concurso bg-red-600 text-white px-4 py-1 rounded hover:bg-red-800">
                                Eliminar concurso
                            </button>
                        </form>
                    </div>
                </div>

                <?php if (empty($fotos_concurso)): ?>
                    <p class="text-gray-500 italic">No hay fotos actualmente.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($fotos_concurso as $foto): ?>
                            <div class="bg-white rounded-lg shadow p-4">
                                <img src="data:<?= htmlspecialchars($foto['mime_type']) ?>;base64,<?= $foto['imagen_base64'] ?>"
                                    alt="Foto #<?= $foto['id'] ?>"
                                    class="w-full max-h-48 object-contain rounded mb-2 bg-gray-100">
                                <p><strong>Título:</strong> <?= htmlspecialchars($foto['titulo_foto']) ?></p>
                                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($foto['descripcion'])) ?></p>
                                <p><strong>Estado:</strong> <?= htmlspecialchars($foto['estado']) ?></p>
                                <form method="post" action="../../backend/admin/procesar-gestionar-fotos.php" class="eliminar-foto-form" data-titulo="<?= htmlspecialchars($foto['titulo_foto']) ?>">
                                    <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                                    <?php if (!$es_pasado): ?>
                                        <?php if ($foto['estado'] !== 'admitida'): ?>
                                            <button name="aceptar" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Aceptar</button>
                                        <?php endif; ?>
                                        <?php if ($foto['estado'] !== 'rechazada'): ?>
                                            <button name="rechazar" class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">Rechazar</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button type="button" class="btn-eliminar-foto bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Modal para eliminar foto -->
    <div id="modalEliminarFoto" class="modal fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h3 class="text-xl font-semibold mb-4">Eliminar foto</h3>
            <p class="mb-6" id="mensajeEliminarFoto">¿Seguro que deseas eliminar esta foto?</p>
            <div class="flex justify-end gap-4">
                <button id="cancelarEliminarFoto" class="px-4 py-2 rounded border border-gray-400 hover:bg-gray-100">Cancelar</button>
                <button id="confirmarEliminarFoto" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar concurso -->
    <div id="modalEliminarConcurso" class="modal fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h3 class="text-xl font-semibold mb-4">Eliminar concurso</h3>
            <p class="mb-6" id="mensajeEliminarConcurso">¿Seguro que deseas eliminar este concurso?</p>
            <div class="flex justify-end gap-4">
                <button id="cancelarEliminarConcurso" class="px-4 py-2 rounded border border-gray-400 hover:bg-gray-100">Cancelar</button>
                <button id="confirmarEliminarConcurso" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        const modalFoto = document.getElementById('modalEliminarFoto');
        const mensajeFoto = document.getElementById('mensajeEliminarFoto');
        const btnCancelarFoto = document.getElementById('cancelarEliminarFoto');
        const btnConfirmarFoto = document.getElementById('confirmarEliminarFoto');

        let formEliminarFoto = null;

        document.querySelectorAll('.btn-eliminar-foto').forEach(btn => {
            btn.addEventListener('click', e => {
                const form = e.target.closest('form');
                const titulo = form.dataset.titulo;
                mensajeFoto.textContent = `¿Seguro que deseas eliminar la foto "${titulo}"? Esta acción no se puede deshacer.`;
                formEliminarFoto = form;
                modalFoto.classList.remove('hidden');
                modalFoto.classList.add('flex');
            });
        });

        btnCancelarFoto.addEventListener('click', () => {
            modalFoto.classList.add('hidden');
            modalFoto.classList.remove('flex');
            formEliminarFoto = null;
        });

        btnConfirmarFoto.addEventListener('click', () => {
            if (formEliminarFoto) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar';
                input.value = '1';
                formEliminarFoto.appendChild(input);
                formEliminarFoto.submit();
                formEliminarFoto = null;
            }
            modalFoto.classList.add('hidden');
            modalFoto.classList.remove('flex');
        });

        // CONCURSO
        const modalConcurso = document.getElementById('modalEliminarConcurso');
        const mensajeConcurso = document.getElementById('mensajeEliminarConcurso');
        const btnCancelarConcurso = document.getElementById('cancelarEliminarConcurso');
        const btnConfirmarConcurso = document.getElementById('confirmarEliminarConcurso');

        let formEliminarConcurso = null;

        document.querySelectorAll('.btn-eliminar-concurso').forEach(btn => {
            btn.addEventListener('click', e => {
                const form = e.target.closest('form');
                const titulo = form.dataset.titulo;
                mensajeConcurso.textContent = `¿Seguro que deseas eliminar el concurso "${titulo}"? Esta acción no se puede deshacer.`;
                formEliminarConcurso = form;
                modalConcurso.classList.remove('hidden');
                modalConcurso.classList.add('flex');
            });
        });

        btnCancelarConcurso.addEventListener('click', () => {
            modalConcurso.classList.add('hidden');
            modalConcurso.classList.remove('flex');
            formEliminarConcurso = null;
        });

        btnConfirmarConcurso.addEventListener('click', () => {
            if (formEliminarConcurso) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar_concurso';
                input.value = '1';
                formEliminarConcurso.appendChild(input);
                formEliminarConcurso.submit();
                formEliminarConcurso = null;
            }
            modalConcurso.classList.add('hidden');
            modalConcurso.classList.remove('flex');
        });
    </script>

</body>

</html>