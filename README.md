# RallyFotografico
Web donde podrás participar en concursos fotográficos o votar por la mejor foto.

-Acceso online: [https://dferretedev.com/final/frontend/]

## Manual de instalación
### 1. Obtener el proyecto:

Opción 1: Clonando el repositorio desde GitHub
https://github.com/drodriguezf-dev/RallyFotografico.git

Opción 2: Usando el archivo ZIP del proyecto entregado
En ambos casos, asegúrate de que el contenido quede en una carpeta llamada RallyFotografico.

### 2. Usar XAMPP y phpMyAdmin
Para ejecutar el proyecto en local, utilizaremos XAMPP y su servicio de phpMyAdmin para gestionar la base de datos.

### 3. Configuración de la base de datos
Inicia Apache y MySQL desde el panel de control de XAMPP.

Accede a phpMyAdmin desde tu navegador:
http://localhost/phpmyadmin

Crea una nueva base de datos, por ejemplo:
rally_fotografico

Importa el archivo SQL incluido en el proyecto:

Ve a la pestaña Importar.

Selecciona el archivo rally.sql (incluido en la carpeta del proyecto).

Pulsa Continuar.

Esto creará las tablas necesarias y un usuario administrador por defecto.

### 4. Configuración del archivo de conexión
Dentro de la carpeta utils del proyecto hay un archivo llamado variables.php.
Este contiene los datos de conexión a la base de datos. Por defecto está configurado así:
$host = “localhost”;
$user = “root”;
$password = “”';
$bbdd = “rallys”;

Si cambias el nombre de la base de datos o tu contraseña de MySQL, asegúrate de actualizarlo aquí.

### 5. Visualizar la web
Aunque puedes usar extensiones como PHP Server en VS Code, en esta guía utilizaremos XAMPP.
Copia la carpeta del proyecto (RallyFotografico) dentro de:
C:\xampp\htdocs

Abre tu navegador (en este ejemplo, Google Chrome) y entra a:
http://localhost/RallyFotografico/frontend

Si colocaste el proyecto en una subcarpeta, asegúrate de incluir la ruta completa en la URL.

## 5. Manual de usuario

### Usuarios sin registrar (visitantes):
Como visitante (sin iniciar sesión), puedes:
Visualizar todos los concursos disponibles.

Votar en los concursos según las reglas establecidas (voto limitado por IP).

Ver el ranking con las 3 fotos más votadas de cada concurso.

Registrarte o iniciar sesión desde la página de login.

### Usuarios con sesión iniciada:
Una vez hayas iniciado sesión, tendrás acceso completo como usuario registrado:
Participar en cualquiera de los concursos activos, subiendo tus fotos desde la sección correspondiente.

Modificar tus datos de usuario (por ejemplo, nombre, correo, contraseña).

Ver el ranking con las 3 fotos más votadas de cada concurso.

Eliminar fotos que hayas subido anteriormente.

## 6. Manual de administrador

Para iniciar sesión como administrador necesitaremos ir al login de usuario corriente y después clicar en acceso para trabajadores.

### Un gestor puede realizar las siguientes acciones:
- Crear concursos
Desde crear-concurso.php, completando los campos requeridos.

- Modificar concursos
Desde la página principal, pulsando en el botón "Editar".

- Eliminar concursos
A través del botón "Eliminar" en el listado de concursos.

- Validar fotografías enviadas por los usuarios
Desde la sección "Gestionar fotografías", puedes aceptar, rechazar o eliminar cada imagen.

- Modificar sus propios datos
Excepto el correo electrónico, que no puede ser editado por motivos de seguridad.

### Administrador
(el administrador generado por el .sql tiene de correo admin@email.com y de contraseña 12345)

Un administrador tiene todas las funcionalidades de un gestor, además de:
- Crear nuevos gestores
Desde la sección de gestión de usuarios.

- Modificar o eliminar gestores existentes.

- Modificar o eliminar usuarios registrados

## Autor
**David Rodríguez Ferrete**

## Licencia
Este proyecto está bajo la licencia MIT.
Puedes usar, copiar, modificar y distribuir el código libremente, siempre mencionando al autor original.

No se ofrece ninguna garantía.

