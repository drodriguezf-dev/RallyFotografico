<?php
require_once("../../utils/variables.php");
require_once("../../utils/funciones.php");

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: ../index.php");
    exit;
}

$conexion = conectarPDO($host, $user, $password, $bbdd);
$gestor_id = $_SESSION['admin_id'] ?? null;

if (!$gestor_id) {
    header("Location: ../index.php");
    exit;
}

// Obtener datos actuales del gestor
$stmt = $conexion->prepare("SELECT nombre, apellidos, email FROM admins WHERE id = :id");
$stmt->execute(['id' => $gestor_id]);
$gestor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gestor) {
    header("Location: ../index.php?mensaje=" . urlencode("Gestor no encontrado.") . "&tipo=error");
    exit;
}

// Mensajes de la URL (si hay)
$mensaje = $_GET['mensaje'] ?? "";
$tipo_mensaje = $_GET['tipo'] ?? "";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Gestor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-white via-gray-100 to-white min-h-screen text-gray-800 p-6 flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-xl animate-fade-in-down border border-gray-200">
        <h1 class="text-3xl font-bold mb-6 text-center text-orange-500">Editar Gestor</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="mb-4 px-4 py-3 rounded text-center text-white <?= $tipo_mensaje === 'exito' ? 'bg-orange-500' : 'bg-red-600' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form action="../../backend/admin/procesar-modificar-gestor.php" method="post" class="space-y-5">
            <!-- Enviamos el id oculto para backend -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($gestor_id) ?>">

            <div>
                <label class="block mb-1 text-sm font-medium">Nombre</label>
                <input type="text" name="nombre" required value="<?= htmlspecialchars($gestor['nombre']) ?>"
                       class="w-full p-2 rounded bg-white border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none transition">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Apellidos</label>
                <input type="text" name="apellidos" required value="<?= htmlspecialchars($gestor['apellidos']) ?>"
                       class="w-full p-2 rounded bg-white border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none transition">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Email <span class="text-gray-500 text-sm">(no editable)</span></label>
                <input type="email" name="email" readonly value="<?= htmlspecialchars($gestor['email']) ?>"
                       class="w-full p-2 rounded bg-gray-200 border border-gray-300 cursor-not-allowed">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Nueva contraseña <span class="text-gray-500 text-sm">(opcional)</span></label>
                <input type="password" name="password"
                       class="w-full p-2 rounded bg-white border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none transition">
            </div>

            <div class="text-center pt-4">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 px-6 py-2 rounded-lg font-semibold text-white transition transform hover:scale-105">
                    Guardar cambios
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <a href="../index.php" class="text-sm text-orange-500 hover:underline transition">Volver al inicio</a>
        </div>
    </div>

    <style>
        @keyframes fade-in-down {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
            animation: fade-in-down 0.6s ease-out both;
        }
    </style>
</body>
</html>