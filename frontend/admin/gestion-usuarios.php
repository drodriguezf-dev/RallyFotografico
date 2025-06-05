<?php
session_start();
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

// Validar rol administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);

try {
    $stmtUsuarios = $conexion->query("SELECT id, nombre, apellidos, email FROM usuarios");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    $stmtGestores = $conexion->query("SELECT id, nombre, apellidos, email FROM admins WHERE rol_id = 2");
    $gestores = $stmtGestores->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = $gestores = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Gestionar Usuarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <h1 class="text-3xl font-bold mb-6 text-center">Gestión de Usuarios</h1>

    <div class="grid md:grid-cols-2 gap-8 mb-8">
        <!-- Tabla de Usuarios -->
        <div>
            <h2 class="text-xl font-semibold mb-2">Usuarios</h2>
            <table id="usuarios-table" class="w-full table-auto bg-white shadow rounded">
                <thead class="bg-gray-200 text-left">
                    <tr>
                        <th class="p-2">ID</th>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Email</th>
                        <th class="p-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="border-t">
                            <td class="p-2"><?= htmlspecialchars($usuario['id']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($usuario['email']) ?></td>
                            <td class="p-2 flex justify-center space-x-2">
                                <a href="modificar-usuario.php?id=<?= $usuario['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-3 rounded text-sm">
                                    Modificar
                                </a>
                                <a href="#" data-url="../../backend/user/delete-user.php?id=<?= $usuario['id'] ?>" class="delete-button bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded text-sm">
                                    Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Gestores con botón para crear nuevo -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl font-semibold">Gestores</h2>
                <a href="register-admin.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-3 rounded text-sm">
                    + Crear Gestor
                </a>
            </div>
            <table id="gestores-table" class="w-full table-auto bg-white shadow rounded">
                <thead class="bg-gray-200 text-left">
                    <tr>
                        <th class="p-2">ID</th>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Email</th>
                        <th class="p-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gestores as $gestor): ?>
                        <tr class="border-t">
                            <td class="p-2"><?= htmlspecialchars($gestor['id']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($gestor['nombre'] . ' ' . $gestor['apellidos']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($gestor['email']) ?></td>
                            <td class="p-2 flex justify-center space-x-2">
                                <a href="modificar-gestor.php?id=<?= $gestor['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-3 rounded text-sm">
                                    Modificar
                                </a>
                                <a href="#" data-url="../../backend/admin/delete-gestor.php?id=<?= $gestor['id'] ?>" class="delete-button bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded text-sm">
                                    Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold mb-4 text-gray-800">¿Estás seguro que deseas eliminarlo?</h2>
            <p class="text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-4">
                <button id="cancel-button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                    Cancelar
                </button>
                <a id="confirm-delete" href="#" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
                    Borrar
                </a>
            </div>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="../index.php" class="bg-amber-300 hover:bg-amber-400 text-white font-semibold py-2 px-4 rounded transition">
            Volver al inicio
        </a>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('delete-modal');
        const confirmDelete = document.getElementById('confirm-delete');
        const cancelButton = document.getElementById('cancel-button');

        // Abrir modal al hacer clic en botón de borrar
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                confirmDelete.setAttribute('href', url);
                modal.classList.remove('hidden');
            });
        });

        // Cancelar borrado
        cancelButton.addEventListener('click', function () {
            modal.classList.add('hidden');
        });

        // Cerrar modal si se hace clic fuera del contenido
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>

</body>

</html>