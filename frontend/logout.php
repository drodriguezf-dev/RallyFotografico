<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cerrando sesión...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#1a1a1a] to-[#3a3a3a] text-white">

    <div class="flex flex-col items-center space-y-4">
        <div class="w-12 h-12 border-4 border-amber-300 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-lg font-semibold">Cerrando sesión...</p>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "../frontend/index.php";
        }, 2000);
    </script>
</body>
</html>
